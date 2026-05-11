<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TwoFaStartFormRequest;
use App\Http\Requests\SetClientFormRequest;
use App\Http\Requests\UpdatePhoneFormRequest;
use App\Http\Requests\VerifyCodeFormRequest;
use App\Http\Resources\TwoFaRequestResource;
use App\Models\TwoFaRequest;
use App\Repositories\TwoFaRequestRepository;
use Neosurf\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Events\NeedTwoFaSmsCode;
use App\Events\TwoFaVerificationSucceeded;

class TwoFaRequestController extends Controller
{
    private TwoFaRequestRepository $twoFaRequestRepository;

    private ClientService $clientService;

    public function __construct(TwoFaRequestRepository $twoFaRequestRepository, ClientService $clientService)
    {
        $this->clientService = $clientService;
        $this->twoFaRequestRepository = $twoFaRequestRepository;
    }

    public function show(TwoFaRequest $twoFaRequest): TwoFaRequestResource
    {
        return new TwoFaRequestResource($twoFaRequest);
    }

    public function start(TwoFaStartFormRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['raw_request'] = json_encode($request->validated());
        $data['status'] = TwoFaRequest::STATUS['STARTED'];

        $twoFaRequest = TwoFaRequest::query()->create($data);

        $nextActionResponse = $this->twoFaRequestRepository->getNextActionResponse($twoFaRequest);

        return response()->json($nextActionResponse, Response::HTTP_CREATED);
    }

    public function setClient(SetClientFormRequest $request, TwoFaRequest $twoFaRequest): JsonResponse
    {
        $twoFaRequest->client_id = $request->input('client_id');
        $twoFaRequest->save();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function setMobile(UpdatePhoneFormRequest $request, TwoFaRequest $twoFaRequest): JsonResponse
    {
        $data = $request->validated();

        $this->clientService->update($twoFaRequest->client_id, $data);
        $twoFaRequest->setStatusAs(TwoFaRequest::STATUS['UPDATED_MOBILE']);

        $response = $this->twoFaRequestRepository->getNextActionResponse($twoFaRequest);

        return response()->json($response);
    }

    public function getNextAction(TwoFaRequest $twoFaRequest): JsonResponse
    {
        $nextActionResponse = $this->twoFaRequestRepository->getNextActionResponse($twoFaRequest);

        return response()->json($nextActionResponse);
    }

    public function resendCode(TwoFaRequest $twoFaRequest): JsonResponse
    {
        if ($twoFaRequest->codeRetriesLimitReached()) {
            return response()->json([
                'error' => true,
                'message' => __('errors.retries_limit_reached')
            ]);
        }

        try {
            NeedTwoFaSmsCode::dispatch($twoFaRequest);
        } catch (\Exception $e) {
            \Log::error($e);

            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'error' => false,
            'message' => __('errors.new_code_sent'),
        ]);
    }

    public function verifyCode(VerifyCodeFormRequest $request, TwoFaRequest $twoFaRequest): JsonResponse
    {
        if ($this->checkVerificationCode($twoFaRequest, $request->input('verification_code'))) {
            $twoFaRequest->generateTwoFaToken();

            try {
                // Send token
                TwoFaVerificationSucceeded::dispatch($twoFaRequest);
            } catch (\Exception $e) {
                \Log::error($e);
            }
        }

        $nextActionResponse = $this->twoFaRequestRepository->getNextActionResponse($twoFaRequest);

        return response()->json($nextActionResponse);
    }

    protected function checkVerificationCode(TwoFaRequest $twoFaRequest, string $code): bool
    {
        if ($twoFaRequest->checkVerificationCode($code)) {
            $twoFaRequest->setStatusAs(TwoFaRequest::STATUS['VALID_CODE']);

            return true;
        }

        $twoFaRequest->setStatusAs(TwoFaRequest::STATUS['INVALID_CODE']);

        return false;
    }
}
