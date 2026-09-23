# Architecture — Starlight Brain Tutorial

> Companion: `docs/TARGET_ARCHITECTURE.md` (target topology), `docs/IMPLEMENTATION_PLAN.md` (migration roadmap),
> `docs/CODEBASE_AUDIT.md` (baseline audit).

## Golden rules (review checklist — tick all before merging)

### R1 — Secrets never touch the browser, the client, or the repo
- [ ] No `.env`, `*_secret_key`, `*_password`, API tokens, or signing secrets in:
      git tree/history, livewire-bound props, JS, Google-bot-visible views, logs, or outbound HTTP query params.
- [ ] Payments keys are read server-side from `Modules\LaraPayease\Contracts\PaymentDriverInterface` settings — never from `request()`/client payloads.
- [ ] AI tokens use `Authorization: Bearer` headers only; FastAPI rejects missing/invalid tokens (401/403).

### R2 — Identity is a Principal, not a session read
- [ ] New/refactored services depend on `Principal` (via `principal()` / `PrincipalResolver`), not `Auth::user()->role`.
- [ ] API/queue/CLI can always produce a Principal; no hidden `Session::get('active_role_id...')` reads in service logic.
- [ ] IDOR rule: every resource mutation verifies the owner (`resource->user_id === principal->userId`); plain `show/destroy` of other users' rows returns 403.

### R3 — Modules are gated, not implicitly trusted
- [ ] Module API/web routes are wrapped in `enabled:<module>` middleware (disabled module = 404/401, never crash).
- [ ] No new `Module::has('x')` / `Str::is` capability checks — use `ModuleRegistry` capabilities (Phase 2.5).
- [ ] A guest/platform default (NoOp) exists for every capability; never `null`-deref a provider.

### R4 — No runtime environment surgery
- [ ] No `file_put_contents(.env)` on web requests. Settings live in `setting()`/optionbuilder + typed config.
- [ ] No `SET FOREIGN_KEY_CHECKS=0`/`SET FOREIGN_KEY_CHECKS=1` in application code.
- [ ] No blanket `CURLOPT_SSL_VERIFYPEER=false` anywhere.

### R5 — Mutations are idempotent + verified server-side
- [ ] Payment success/webhook paths guard against double-complete (`status === 'complete'` early return).
- [ ] Callbacks re-verify server-side with the gateway; never trust a frontend success flag.

### R6 — Delegators, not duplicates
- [ ] Helpers are thin wrappers that forward to `App\Domain\*`; identical behaviour preserved behind the wrapper.
- [ ] No new `new Service()` in controllers/hot paths — use `app(Service::class)` / constructor DI.

## ADRs
- **ADR-2.2 (Principal).** One `Principal` value object per web/API/queue principal, resolved by `PrincipalResolver`
  (web: session active_role → default_role; API: bearer + token claims; queue/CLI: system/default). Registered as a singleton
  so resolution happens at most once per request/queue batch.
- **ADR-2.4 (Shared).** `App\Domain\Shared` owns Currency/TimeZone/Storage/FileUpload/Guid; `app/Helpers/helpers.php`
  keeps BC delegators that forward to it award-by-magic delegation.

## Directory intent (2.1)
- `app/Domain/Principal` — Principal value object + resolver + `PrincipalAware` trait.
- `app/Domain/Shared` — Currency, TimeZone, Storage, FileUpload, Guid (+ thin `TimeZone`, `Storage`, `Currency` delegators in helpers).
- `app/Domain/<Context>` — future bounded contexts (Tutoring, Courses, Bundles, Commerce, ...) per TARGET_ARCHITECTURE.

## Migration status
- [x] 2.1 directories + PSR-4 (PSR-4 for `App\` already covers `app/Domain/**`).
- [x] 2.2 Principal value object + PrincipalResolver (singleton) + `principal()` helper.
- [x] 2.3 PrincipalAware trait; `User::role` accessor unchanged for BC (still session-coupled) — call sites audited in Phase 1.
- [x] 2.4 Shared classes + delegators.
- [ ] 2.5 ModuleRegistry capability map + NoOpProvider (next increment).
- [ ] 2.6 SettingReader typed DTOs (next increment).

## Running the verification targets
- `vendor/bin/pint --test` (Phase 2 goal: keep changed files clean; whole-repo cleanup is follow-up).
- `php artisan test` (Phase 2 goal: existing suite green).
- Lint guard: `php -l` on every committed PHP file via the CI workflow.
