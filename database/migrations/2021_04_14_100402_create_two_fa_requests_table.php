<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwoFaRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('two_fa_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('session_id')->nullable();
            $table->uuid('client_id')->nullable();
            $table->string('origin');
            $table->uuid('access_id');
            $table->string('signature');
            $table->text('url_back');
            $table->text('url_success'); 
            $table->text('url_failure');
            
            $table->string('locale');
            $table->json('raw_request');
            $table->ipAddress('ip_address');
            $table->string('status');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('two_fa_requests');
    }
}
