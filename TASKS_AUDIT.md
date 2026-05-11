# Quick codebase audit — proposed tasks

## 1) Typo fix task
**Finding**: the README contains unbalanced quotes and ambiguous wording: `"dev" for development"` (extra quote) in two places.

**Proposed task**:
- Fix both occurrences to `"dev" for development`.
- Review the *Installation* section to keep wording consistent (`local/dev/prod`).

**Acceptance criteria**:
- No remaining `development"` occurrences in `README.md`.

---

## 2) Bug fix task
**Finding**: the `resendCode` endpoint returns JSON errors without explicit HTTP status codes (so they default to 200), including retry-limit and exception cases.

**Proposed task**:
- Return explicit business-relevant statuses:
  - `429 Too Many Requests` when resend retries are exhausted.
  - `500 Internal Server Error` when an exception occurs during sending.
- Keep the current JSON body for backward compatibility, and optionally add an app-level `error_code` for frontend handling.

**Acceptance criteria**:
- `resendCode` error responses no longer return HTTP 200.

---

## 3) Code comment / documentation task
**Finding**: there is a non-actionable TODO comment in `TwoFaRequest::getIsStartedAttribute()` (`TODO add check here in future after adding other 2fa step`).

**Proposed task**:
- Replace the TODO with a precise note (or a referenced ticket) that describes:
  - the intended multi-step scenario,
  - the exact condition to implement,
  - the functional context.
- Alternatively, create a technical issue and remove the TODO if it is not planned.

**Acceptance criteria**:
- No vague TODO remains at this code location; debt is either clearly documented or tracked in a ticket.

---

## 4) Test improvement task
**Finding**: validation tests often assert exact full text messages (for example `The access id must be a valid UUID.`), which makes tests brittle when Laravel wording/translations change.

**Proposed task**:
- Replace exact-message assertions with:
  - `assertInvalid([...])` / `assertJsonValidationErrors([...])` on field keys,
  - and, when needed, partial assertions on rule identifiers rather than full human-readable text.
- Add a dedicated `resendCode` test that asserts error HTTP statuses (`429`, `500`) after the bug fix above.

**Acceptance criteria**:
- Validation tests no longer depend on full English message strings.
- `resendCode` HTTP error behavior is covered by tests.
