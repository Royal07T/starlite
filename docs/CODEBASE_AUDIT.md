# Starlite — Codebase Audit

> Full-stack audit of the repository at `/home/royal-t/starlite redo`
> Audit date: 2026-09-22 · Base commit: `6f10862` (branch `main`)

---

## 1. Executive Summary

Starlite (internally branded *"Lernen"*) is a commercial, feature-complete online
tutoring & education marketplace. It is a **Laravel 11 monolith** with a partial
`nwidart/laravel-modules` modularization layer, a bundled messenger package
(LaraGuppy), a CMS page builder, a 23-panel settings system, and a separate
**FastAPI AI course-assistant micro-service** added recently.

The codebase is **large and mature in feature breadth** (1,315 PHP files, 3,786
tracked files, 73 core migrations, 264+ tables across core/modules/packages) but
carries significant **structural and technical debt**:

- God-tier service classes (`BookingService` ≈ 1,200 lines, `PageBuilderService` ≈ 1,920 lines, `helpers.php` ≈ 2,470 lines).
- A global `helpers.php` with 80+ global functions (incl. a 1,276-row currency table duplicated into `config/currencies.php`).
- Services instantiated with `new Service()` inside other services, making testability and DI poor.
- Dynamic role-switching stored in the **session** while the same accessor is used in API/queue contexts.
- Hard-coded third-party hook points (`Module::has('quiz')`, `isActiveModule('kupondeal')`, etc.) interleaved into core booking/order logic.
- Committed secrets in tracked `.env` and `ai_assistant/.env`.
- The FastAPI assistant's `/api/v1/ask` endpoint is **unauthenticated** and trusts a client-supplied `student_id`.
- Minimal automated test coverage (13 test files, most boilerplate), despite 26 services and 27 controllers.
- Dead/branded remnants: blog system half-removed, `resources/views/pages` (Volt) path referenced but absent, deprecated typo methods (`getOrdeWrWithItem`), unused exports, stub services.

### Strategic conclusion

The platform's **domain model is sound** (polymorphic orders, polymorphic media,
two-stage wallet ledger, reservation lifecycle). The main redo effort is to
**preserve the domain, re-organize the monolith into bounded contexts, remove
dead weight, secure the exposed surface (API + AI), and replace the session-/
global-dependent glue with explicit, testable application services.**

---

## 2. Repository Overview

| Aspect | Value |
|---|---|
| Repository name | Starlite (formerly "Lernen") |
| Version | 3.0.1 (composer `laravel/laravel` skeleton) |
| Framework | Laravel 11.9 (PHP ^8.2) |
| UI framework | Livewire 3.5 + Volt + Alpine.js |
| Styling | Tailwind CSS 3 **and** Bootstrap 5 (hybrid) |
| Database | MySQL (`.env`: `***REMOVED***`) |
| Queue | sync (dev), jobs exist for async (prod) |
| Realtime | Laravel Reverb (config only, not wired in app code) + LaraGuppy chat events |
| Modularization | nwidart/laravel-modules v11 — 4 enabled modules |
| Auth | Session (web) + Sanctum tokens (API, 7-day expiry) + Socialite (Google) |
| RBAC | Spatie Laravel Permission v6 |
| AI | OpenAI (Laravel-side AI writer + FastAPI course tutor) |
| Pay | Stripe, Paystack, PayFast (code), Paytm/Razorpay/iyzico (packages present) |
| PDF | DomPDF + Browsershot (Puppeteer) |
| Excel | Maatwebsite Excel + PhpSpreadsheet |
| Local packages | 5 path-repos: optionbuilder, pagebuilder, laraguppy, laravel-installer, scssphp |

### Git history signals (most recent first)

| Commit | Meaning |
|---|---|
| `6f10862` | Vendor/cache churn ("updated") |
| `7d913f8` / `fef4c39` | **Removed** 'More' dropdown, **removed** blog & FAQ from nav/routes/seeders |
| `9b16588` | Fix `main.js` null ref on currency dropdown |
| `248cada` | Extracted `currencyList()` into `config/currencies.php` (1,276 rows) |
| `473403e` | **Added API versioning** (`/api/v1`), security fixes, tests |
| `9a652fd` | Security/code-quality fixes; added FastAPI AI assistant |

The repository is on an active **normalization/redesign trajectory**: recent
commits remove features, version the API, extract config, and add tests. The
"redo" task continues that trajectory in an organized, documented way.

---

## 3. High-Level Architecture (Current State)

```mermaid
flowchart TB
    subgraph Client["Clients"]
        WB["Web Browser (Livewire + Blade + Alpine)"]
        API["Mobile / 3rd-party (REST JSON)"]
        AIChat["Course-taking page AI chat widget"]
    end

    subgraph Laravel["Laravel 11 Monolith (entry: public/index.php)"]
        WEB["routes/web.php + auth + admin + pagebuilder + optionbuilder"]
        APIROUTES["routes/api.php (/api/v1)"]
        MOD["Modules:]
        Courses (routes/api, routes/web)
        CourseBundles (routes/web)
        LaraPayease (payment drivers)
        MeetFusion (meeting drivers)"
        APP["app/: Controller → Service → Eloquent Model"]
        PKG["packages/: optionbuilder, pagebuilder, laraguppy, installer, scssphp"]
        QUEUE["Jobs (11) / Listeners / Observers / Notifications"]
    end

    subgraph Data["Persistence"]
        MYSQL[("MySQL: core 73 migrations + courses_* + bundles + lg__* + optionbuilder__*")]
        CACHE[("File cache + redis config")]
        S3[("Local disk / S3 / DO Spaces")]
    end

    subgraph Ext["External Integrations"]
        STRIPE["Stripe / Paystack / PayFast / Paytm / Razorpay / iyzico"]
        ZM["Zoom + Google Meet (MeetFusion)"]
        GCAL["Google Calendar / Google OAuth"]
        OPENAI["OpenAI Chat Completions"]
    end

    subgraph AISERV["FastAPI AI Assistant (ai_assistant/, port 8001)"]
        AIAPI["/api/v1/ask (in-memory rate limit, session store)"]
        AIDB[("Reads MySQL courses_* via SQLAlchemy")]
    end

    WB --> WEB
    API --> APIROUTES
    AIChat --> AIAPI
    WEB --> MOD
    APIROUTES --> MOD
    MOD --> APP
    APP --> PKG
    APP --> QUEUE
    APP --> MYSQL
    APP --> S3
    PKG --> MYSQL
    WEB --> GCAL
    APP --> STRIPE
    APP --> ZM
    APP --> OPENAI
    AIAPI --> AIDB
    AIAPI --> OPENAI
```

### Key architectural traits (observed)

1. **Route discovery is bootstrap-level glob.** `bootstrap/app.php` globs
   `Modules/*/routes/web.php` and `Modules/*/routes/api.php` and loads **all**
   module route files, regardless of module enable/disable state. Module web
   routes guard themselves with `enabled:courses`, but module **API** routes are
   registered with no module-enabled gate. Disabled modules still expose API
   routes.
2. **Controller → Service → Model** is consistent: controllers are thin-ish,
   services hold business logic, no repository layer.
3. **Services are hand-rolled classes** with `new Service()` composition
   (no constructor DI / no interfaces), e.g. `(new OrderService())->storeOrderItems(...)`
   inside `BookingService`.
4. **Polymorphic `orderable`/`mediable`/`ratingable`/`likeable`** morphs are used
   extensively — a flexible but harder-to-index design.
5. **Settings accessed via global `setting('_group.key')`** helper backed by
   optionbuilder `optionbuilder__settings` table — settings are read from DB at
   runtime with no typed config objects.
6. **Role switching via session** (`User::role` accessor reads `Session::get('active_role_id'.$id)`),
   with default fallback to `default_role`. The same accessor is invoked inside
   service logic that also runs in queue workers / API contexts.
7. **Admin bypass Gate** — `Gate::before` returns `true` for `admin` role
   (deliberate, but makes permission auditing coarse).
8. **Global view composer** `View::composer('*', AdminComposer::class)` attaches
   admin context to every view render.

---

## 4. Directory Inventory

### 4.1 `app/` (core monolith)

| Path | Count | Notes |
|---|---|---|
| `app/Models/` | 49 PHP | incl. `Scopes/` (Active, Position) |
| `app/Services/` | 26 (1 empty stub `TaxonomyService`) | business logic |
| `app/Http/Controllers/` | 27 | Admin(1)+Api(19)+Auth(2)+Frontend(1)+Site+OpenAI+Impersonate+Base |
| `app/Livewire/` | 79 PHP | Components, Forms, Frontend, Pages (Admin/Common/Student/Tutor) |
| `app/Http/Requests/` | 33 | Form request DTOs |
| `app/Http/Resources/` | 41 | API transformers |
| `app/Http/Middleware/` | 7 | role, permit-of, locale, maintenance, onlineUser, module-enabled, redirect |
| `app/Jobs/` | 11 | booking/purchase/notification/calendar/reservation jobs |
| `app/Notifications/` | 2 | `EmailNotification`, `DbNotification` |
| `app/Listeners/` | 3 | message recv, modules lifecycle, settings updated |
| `app/Observers/` | 3 | Blog, BlogCategory, Profile |
| `app/Casts/` | 12 | Status/type casts |
| `app/Optionbuilder/` | 23 | Admin settings panels (`_general`, `_lernen`, `_api`, `_theme`, ...) |
| `app/Console/Commands/` | 5 | seed, upgrade-db, delete-pending-orders, enable-all-modules |
| `app/Helpers/helpers.php` | 2,470 lines | 80+ global functions |
| `app/Exports/`, `Facades/`, `Services/` extras | — | UsersExport, Cart + DbNotification facades |

### 4.2 `Modules/` (nwidart modules, all enabled)

| Module | Purpose | Models | Migrations | Services |
|---|---|---|---|---|
| `Courses` ("Learnty") | Course marketplace: categories, courses, sections, curriculum, pricing, enrollments, watchtime, forums | 13 | 18 (`courses_` prefix) | `CourseService` (783 ln), `CurriculumService` |
| `CourseBundles` ("LearntySuite") | Bundle multiple courses, purchases | 3 | 3 (`courses_` prefix) | `BundleService` (293 ln) |
| `LaraPayease` | Payment gateway drivers (Stripe, Paystack) + PayFast/Paytm/Razorpay views | — | — | Factory + drivers |
| `MeetFusion` | Video meeting drivers (GoogleMeet, Zoom) | — | — | Factory + drivers |

### 4.3 `packages/` (local Composer path repositories)

| Package | Role | Tables |
|---|---|---|
| `larabuild/optionbuilder` | Admin settings, `setting()` helper | `optionbuilder__settings` |
| `larabuild/pagebuilder` | Drag-and-drop CMS pages | `pages` |
| `amentotech/laraguppy` | Realtime messenger (events, services, resources) | `lg__*` (11 migrations) |
| `amentotech/laravel-installer` | Web installer (first-run) | — |
| `scssphp` | SCSS → CSS compiler (admin theme builder) | — |

### 4.4 `ai_assistant/` (Python FastAPI microservice)

- `main.py` — FastAPI app, CORS for `http://127.0.0.1:8000`, router mount.
- `routes/assistant.py` — `GET /api/v1/health`, `POST /api/v1/ask`; **in-memory**
  rate limit (20/min/IP) and SQLAlchemy session.
- `services/course_context.py` — verifies enrollment, builds system prompt from
  `courses_courses`/`sections`/`curriculums`/`faqs`.
- `services/ai_service.py` — OpenAI chat completions, in-memory conversation
  session store (`sessions: dict[str, dict]`), token budgeting, history trim.
- `models.py` — SQLAlchemy mirrors of `courses_*` tables.
- `venv/` + `requirements.txt` (fastapi, uvicorn, sqlalchemy, pymysql, openai, pydantic).

### 4.5 Miscellaneous

- `database/` — 73 migrations, 22 seeders + `V11`…`V301` upgrade seeders (37), 4 factories.
- `routes/` — web, api, admin, auth, breadcrumbs, optionbuilder, pagebuilder, channels, console.
- `config/` — 29 configs, incl. 37 KB `currencies.php`.
- `resources/views/` — 228 Blade templates (4 layouts, 30 components, 82 Livewire views, 40 pagebuilder sections, 19 skeletons).
- `public/` — prebuilt CSS (707 KB `main.css`), vendor JS, admin assets, installer, demo-content, summernote.
- `.kilo/worktrees/pitch-lingo` — detached git worktree at current HEAD (working area for the redo).
- `certs/` — bundled `cacert.pem` + SSL config for cURL fallbacks.
- Tracked secrets: `.env` and `ai_assistant/.env` **are committed**.

---

## 5. Feature Inventory & Disposition Summary

### 5.1 Tutoring (1-on-1 sessions)

- Tutor profiles, subjects, subject groups, hourly rates, education/experience/certificates.
- Recurring & single time-slot generation (`BookingService::addUserSubjectGroupSessions`, `addTimeSlots`).
- Slot booking with **reservation + auto-expiry** (`reservedBookingSlot` + `RemoveBookingReservationJob`).
- Booking lifecycle: reserved → active → rescheduled → refunded → completed; booking logs.
- Rescheduling with order-item remap, email/db notifications.
- Free-slot booking, free-course enrollment, free-bundle enrollment (all three live in `BookingService`).
- Meeting-link generation (Zoom/Google Meet), Google Calendar event creation.
- Reviews/ratings, favourite tutors, disputes with conversation threads.
- Tutor payouts, withdrawals, payout methods, wallet (two-stage ledger).

### 5.2 Courses (Modules/Courses)

- Course CRUD wizard (details, media, pricing, sections, curriculum, FAQs, promotions, publish).
- Course lifecycle: draft → under_review → need_revision → active → inactive.
- Curriculum types: video / audio / live / article.
- Media (thumbnail/promo/video), pricing (+ new `session_price`/`min_sessions`/`pricing_type`).
- Enrollments (+ new `total_sessions` / `booked_sessions`), watchtime progress, likes.
- Discussion forums, noticeboards, FAQ, categories (parent-child).
- Search, filtering, ratings, featured courses.

### 5.3 Course Bundles (Modules/CourseBundles)

- Bundle CRUD, discount, computed `final_price` (generated column), courses pivot, purchases.

### 5.4 Payments & Wallets

- Gateways via LaraPayease drivers: Stripe, Paystack; webhook/views for PayFast, Paytm, Razorpay; iyzico SDK installed.
- Shopping cart (`CartService` + `cart_items`), coupons via optional KuponDeal addon.
- Checkout, orders + polymorphic `order_items`, invoices (PDF via DomPDF/Browsershot).
- Platform commission per `platform_fee`; two-stage wallet (`pending_available` → `add`/available).
- Payouts/withdrawals with admin approval.

### 5.5 Admin Panel (Livewire pages)

- Insights/analytics, users, admin users, identity/KYC verification, bookings, invoices, reviews.
- Disputes, email templates, notification templates, menu manager, language translator.
- Taxonomies (languages, subjects, subject groups), commission settings, payment methods, withdraw requests.
- Upgrade, addons/packages manager, clear-cache, queue health, SMTP/broadcasting/Pusher/Reverb/social-login update endpoints.

### 5.6 Content & CMS

- PageBuilder (40+ sections), menus, breadcrumbs, 8 color variations, RTL, language translation.
- Blogs (being removed — routes/seeders deleted in `fef4c39`, model/view remnants remain).

### 5.7 Realtime & Notifications

- LaraGuppy chat (`lg__*`), in-app + email notification templates, online-user cache tracking.
- 25+ notification event types (`NotificationService`).

### 5.8 AI

- **AI Writer** (`OpenAiController`) — admin content assistant calling OpenAI Chat Completions with prompt templates from settings.
- **AI Course Tutor** (`ai_assistant/` FastAPI) — course-adaptive Q&A chat widget embedded in `CourseTaking` page.

### 5.9 API (see §7)

---

## 6. Data Model (Table Inventory)

### 6.1 Core tables (from `database/migrations`)

Authentication & platform:
`users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`,
`job_batches`, `failed_jobs`, `personal_access_tokens`, `password_resets`.

Profiles & identity:
`profiles`, `countries`, `country_states`, `languages`, `user_languages`,
`user_contacts`, `user_addresses`, `user_education`, `user_experience`,
`user_certificate`, `user_identity_verifications`.

Tutoring & subjects:
`subject_groups`, `subjects`, `user_subject_groups`, `user_subject_group_subjects`,
`user_subjects_slots`, `slot_bookings`, `booking_logs`, `ratings`,
`favourite_users`, `disputes`, `dispute_conversations`.

Commerce:
`orders`, `order_items`, `cart_items`, `billing_details`, `user_wallets`,
`user_wallet_details`, `user_withdrawals`, `user_payout_methods`.

Content & settings:
`menus`, `menu_items`, `blog_categories`, `blogs`, `blog_tags`,
`blog_category_links`, `blog_tag_links`, `addons`, `email_templates`,
`notification_templates`, `notifications`, `account_settings`, `translations`,
`translation_files`, `social_profiles`.

RBAC: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`,
`role_has_permissions`.

Dev: `telescope_entries` (+6 telescope tables via one migration).

### 6.2 Module tables

| Module prefix | Tables |
|---|---|
| `courses_` | `courses`, `categories`, `media`, `pricings`, `sections`, `curriculums`, `promotions`, `faqs`, `likes`, `noticeboards`, `watchtimes`, `enrollments`, `discussion_forums` |
| `courses_` (bundles) | `bundles`, `course_bundles`, `bundle_purchases` |
| `optionbuilder__` | `settings` |
| (pagebuilder) | `pages` |
| `lg__` (Laraguppy) | `messages`, `users`, `friends`, `chat_actions`, `guest_accounts`, `attachments`, `threads`, `participants`, `threads_detail`, `notifications`, `seen_messages` |

> Total ≈ **110–120 tables** depending on Laraguppy/dev-support tables.

### 6.3 Data-quality observations

- `slot_bookings.tutor_id` and `user_subject_slot_id` were just made **nullable**
  (unassigned bookings for free-course sessions) — FK semantics now loosened.
- `orders` uses `bigInteger student_id` (legacy) but was aliased to `user_id`
  via migration `change_orders_table_column_from_student_id_to_user_id`.
- JSON column usage is heavy (`options`, `meta_data`, `tags`, `learning_objectives`)
  — values are untyped; migrations don't add generated columns except
  `bundles.final_price` (generated).
- No soft-deletes on most transactional tables; `profiles` has them.

---

## 7. API Surface

### 7.1 Convention

- All endpoints under **`/api/v1`** (added in `473403e`).
- Root API in `routes/api.php`; module APIs in `Modules/*/routes/api.php` ALSO
  carry `v1` prefix, but are registered with `api` middleware only — **no
  `enabled:` gate** and no auth gate if the module is toggled off.
- Response envelope via `ApiResponser` trait: `{ success, message, data }`, HTTP codes.
- Auth: Sanctum bearer token, minted with `createToken('lernen', ['*'], now()->addDays(7))`.

### 7.2 Public endpoints (no auth)

`POST /api/v1/login`, `social-login`, `social-profile`, `register`, `forget-password`;
`GET /find-tutors`, `/tutor/{slug}`, `/recommended-tutors`, `/student-reviews/{id}`,
`/tutor-available-slots`, `/slot-detail/{id}`, `/countries`, `/languages`, `/states`,
`/subject-groups`, `/subjects`, `/settings`; resources for
`tutor-education`, `tutor-experience`, `tutor-certification` (show/store/update/destroy).
Courses module: `GET /api/v1/courses`, `/categories`, `/languages`, `/levels`,
`/prices`, `/ratings`, `/duration-counts`, `/course-detail/{slug}`.

### 7.3 Authenticated (Sanctum)

Bookings: `upcoming-bookings`, `complete-booking/{id}`, `book-free-slot`,
`dispute*`, `review/{id}`; profile: `profile-settings/{id}` (GET/PUT),
`favourite-tutors`, `identity-verification`, `billing-detail`, `invoices`;
payouts: `tutor-payouts`, `my-earning`, `earning-detail`, `user-withdrawal`,
`payout-status`, `payout-method`; cart/checkout: `booking-cart`, `checkout`;
notifications: `index`, `{id}/read`, `read-all`; account: `update-password`,
`timezone`, `send-message/{recipientId}`, `resend-email`, `logout`.
Courses module auth: `like-course`, `course-cart`, `enroll-course`,
`course-taking/{slug}`, `enrolled-courses`, `update-progress`.

### 7.4 API issues

1. **IDOR-prone** `profile-settings/{id}`, `timezone/{id}`, `update-password/{id}`
   — authorization relies on per-controller checks; only `profile-settings/{id}`
   has a test proving cross-user access is blocked.
2. **AI assistant has no auth** — `POST http://127.0.0.1:8001/api/v1/ask`
   trusts body `student_id`; anyone can query any enrolled course context.
3. **Mixed API/domain coupling** — API controllers depend on services that read
   the **web session** (`User::role`, `Auth::user()`, `setting()`), not on the
   authenticated API principal.
4. `/api/*` is CSRF-exempt globally (expected for token auth, but note it).

---

## 8. Business Logic Layer (Service Map)

| Service | Responsibility | Quality signal |
|---|---|---|
| `BookingService` (1,203 ln) | slots, reservations, bookings, reschedule, free purchase, calendar, meetings, orders | **God class** — 4 domains |
| `OrderService` (302 ln) | order CRUD, item morph querying, commissions | Fair |
| `CartService` (160 ln) | session cart, add/remove | Session-bound |
| `WalletService` (176 ln) | two-stage ledger, refunds, earnings | Good, tests exist |
| `PayoutService` (94 ln) | withdrawals, methods | Thin |
| `NotificationService` (586 ln) | 25+ template emails | Large, template-driven |
| `DbNotificationService` (361 ln) | in-app notifications | Large |
| `DisputeService` (213 ln) | disputes, threads | Fair |
| `SiteService` (380 ln) | tutor discovery/search | Query-heavy, fine |
| `CourseService` (783 ln, module) | course CRUD, enroll, progression | Large module service |
| `BundleService` (293 ln, module) | bundle CRUD, purchase | Fair |
| `CurriculumService` (115 ln) | curricula | Thin, good shape |
| `PageBuilderService` (1,922 ln) | CMS sections | **God class** — renderer/editor |
| `RegisterService` (205 ln) | registration, social login | OK |
| `GoogleCalender`, `ZoomService` | external calendar/meeting | OK |
| `TranslationService` (272 ln) | language files, translator | OK |
| `ProfileService`, `UserService`, `SubjectService`, `IdentityService`, `EducationService`, `ExperienceService`, `CertificateService`, `BillingService`, `AddressService` | CRUD-ish | Thin/OK |
| `TaxonomyService` | 0 lines | **Stub** |
| `InsightsService` (95 ln) | admin analytics | OK |

### Cross-service smell examples

- `BookingService` creates orders directly (`createOrder()`), stores order items,
  dispatches purchase jobs, and enrolls free courses/bundles — i.e., booking,
  ordering, enrolling, and wallet crediting are entangled.
- `Module::has('kupondeal')`, `isActiveModule('quiz')`, `isActiveModule('upcertify')`,
  `Module::has('subscriptions')` appear inside `BookingService`, `SiteService`,
  `OrderService`, `Course` model — optional add-on coupling is spread everywhere.
- `site.php` util `getGatewayObject()`, `getMeetingObject()` — runtime factories
  switch on settings strings.

---

## 9. Frontend Architecture

- **Livewire 3 + Volt** for interactive pages (auth via Volt; courses/tutor/admin via Livewire).
- **Alpine.js** for small interactions; **jQuery + Bootstrap 5** in `public/js/main.js`
  (552 lines) + vendor scripts loaded via Vite config array.
- Massive **pre-built assets committed**: `public/css/main.css` (707 KB + source map),
  `video.min.js` (663 KB), `chart.js`, `summernote`.
- `resources/js/app.js` is **empty** — the Vite entry file is a no-op; all real JS is in `public/`.
- Vite config feeds a long literal list of CSS/JS paths from `public/` plus module assets via `vite-module-loader.js`.
- Tailwind + Bootstrap hybrid with SCSS compiled at **runtime** via scssphp for the admin theme colorizer (`updateSaas`).
- 4 Blade layouts: `app`, `admin-app`, `frontend-app`, `guest`.
- RTL support (`rtl.css`), 8 homepage color variations, 19 loading skeletons, 40 pagebuilder section templates.

### Frontend issues

1. Dual styling systems (Tailwind utility classes + Bootstrap classes) increase design-system drift.
2. Committed compiled assets bloat the repo; build output regenerates from `public/` paths.
3. AI chat widget hard-codes `http://127.0.0.1:8001` — breaks in production unless reverse-proxied.
4. `resources/views/pages` (Volt) does not exist; Volt mounted on a non-existent path.

---

## 10. Integration Catalog

| Integration | Mechanism | Notes |
|---|---|---|
| Stripe | `stripe/stripe-php`, LaraPayease driver | prepareCharge + redirect |
| Paystack | LaraPayease driver | same pattern |
| PayFast | Webhook + views | not driver-based |
| Paytm / Razorpay / iyzico | SDK installed, views present | effectively dormant |
| Zoom | `ZoomService` + MeetFusion driver | meeting links |
| Google Meet | MeetFusion driver + Google Calendar OAuth | linked to booking `event_id` |
| Google Calendar | `GoogleCalender` service + `CreateGoogleCalendarEventJob` | two-way event creation |
| Google OAuth (social login) | Socialite `socialiteproviders/google` | web + API (`/api/v1/social-login`) |
| OpenAI (AI writer) | raw `Http::post` → `api.openai.com/v1/chat/completions` | does **not** use `openai-php/laravel` client |
| OpenAI (AI tutor) | `ai_assistant` FastAPI + `openai==1.51.0` | separate service |
| AWS S3 / DO Spaces | league/flysystem-aws-s3-v3 + spacekonnect addon | storage disk switcher `getStorageDisk()` |
| LaraguPeer chat | LaraGuppy package + Reverb/Pusher broadcasting config | events out |
| Email | Symfony Mailer SMTP | template-driven |
| Certificate PDF | DomPDF + Browsershot (Puppeteer) | `GenerateCertificateJob` |
| Browsershot/Chrome | `CHROME_PATH=***REMOVED***` | for screenshots/PDFs |

---

## 11. Security Assessment

### Critical

| # | Finding | Evidence | Recommendation |
|---|---|---|---|
| S1 | **`.env` committed with live secrets** | `MAIL_PASSWORD='***REMOVED***'`, DB config, in git; `ai_assistant/.env` also tracked | `git rm --cached .env ai_assistant/.env`, rotate creds, add `.env.example` |
| S2 | **AI assistant `/api/v1/ask` unauthenticated, trusts client `student_id`** | `routes/assistant.py` reads `req.student_id`, only DB-enrollment check | Require Sanctum token or signed session claim; never trust body user id |
| S3 | **Disabled SSL verification** for local env via cURL options + committed `cacert.pem` bootstrap | `AppServiceProvider` sets `CURLOPT_SSL_VERIFYPEER=false`; `fix-ssl.php`, `ssl-bootstrap.php` | Remove blanket disable; rely on system CAs |
| S4 | **IDOR patterns in API** | `profile-settings/{id}`, `update-password/{id}`, `timezone/{id}` | Replace with resolved auth principal + policies |

### High

| # | Finding | Evidence |
|---|---|---|
| S5 | `DB::statement('SET FOREIGN_KEY_CHECKS=0')` inside `rescheduleSession` transaction | FK integrity bypass during reschedule |
| S6 | Admin creator/updater controllers parse `.env` directly (`GeneralController::$envPath`) | Runtime env mutations are risky |
| S7 | Global `Gate::before` admin bypass | Cannot audit permission coverage per-resource |
| S8 | API — no throttling on auth endpoints | `login`/`social-login` hammerable |

### Medium / hygiene

- Blog remnants remain in models/views after feature removal.
- Demo-site guards (`isDemoSite()`) return 403 on registration/login in "demo" mode — check production path.
- `modules_statuses.json` committed and module routes load regardless of status.
- Telescope dev dependency disabled via `dont-discover` but migration + tables exist.
- Purchased-addon stubs (`subscriptions`, `quiz`, `upcertify`, `kupondeal`, `assignments`) referenced unconditionally inside core services.

---

## 12. Automated Testing Review

| Suite | Files | Coverage of |
|---|---|---|
| `tests/Feature/Api/AuthApiTest.php` | 9 tests | login (student/tutor), 401s, profile access, IDOR block, logout, 404 fallback |
| `tests/Unit/Services/WalletServiceTest.php` | 7 tests | wallet add/deduct/get, insufficient funds |
| `tests/Feature/Auth/*` (Breeze boilerplate) | 6 files | auth/registration/password/email flows |
| `tests/Feature/ProfileTest.php`, `ExampleTest.php` | 2 | trivial |
| `tests/Unit/ExampleTest.php` | 1 | trivial |

**Gaps:** no tests for BookingService, OrderService, CartService, DisputeService,
CourseService, PayoutService, the API booking/payout/resources, module routes,
payment drivers, webhooks, calendar/meeting, notifications, or the AI assistant.

---

## 13. Problems & Technical Debt (Consolidated)

### A. Structural
1. **God classes** — `BookingService` (4 domains), `PageBuilderService`, `helpers.php`, `NotificationService`.
2. **Global helper soup** — 80+ functions; location-based timezone helpers (`parseToUTC`, `parseToUserTz`) scattered.
3. **Service-layer non-DI** — `new Service()` patterns make unit-testing and swap-out hard.
4. **Session-coupled role logic** in API/queue paths.
5. **Cross-module hard-coded add-on references** (`quiz`, `upcertify`, `subscriptions`, `kupondeal`, `assignments`, `starup`, `spacekonnect`) embedded in core domains.
6. **Module API routes registered even when module disabled.**
7. **Global view composer** on every view.

### B. Code-quality
8. Deprecated/typo methods: `getOrdeWrWithItem()`, commented-out blocks in `BookingService`.
9. `resources/js/app.js` empty; Vite input lists hard-coded `public/` paths.
10. Duplicate currency data in `helpers.php` and `config/currencies.php` (dedup started).
11. `.env.example` missing; installer package compensates.
12. `TaxonomyService` empty stub; `Exports/UsersExport` unused.
13. Non-anonymous legacy migration classes vs anonymous migrations.

### C. Data
14. Two migration files named `*_create_languages_table.php` (different tables, but confusing).
15. JSON `options`/`meta_data` shapes are stringly-typed (e.g. `options->subject`).
16. Slot booking FK looseness after nullable migration.

### D. Ops
17. Queue config `sync` in dev; `QueueHeartbeatJob` exists but needs wiring.
18. Reverb config present but no app usage confirmed.
19. No CI workflow in repo (no `.github/workflows`).

---

## 14. What Is Worth Preserving vs Rebuilding (Summary)

**Preserve as-is (strong):**
- Domain model: polymorphic orders, media, ratings; two-stage wallet ledger; reservation lifecycle.
- Wallet service logic (tested).
- `Modules/Courses` module structure and migrations (prefix-based).
- LaraPayease / MeetFusion driver pattern (interface + factory + facade).
- API v1 envelope (`ApiResponser`), Sanctum token flow, `UserResource`.
- Laraguppy messenger; optionbuilder/pagebuilder settings & CMS.
- Breadcrumbs, permissions seeders, upgrade-version seeders.

**Refactor (priority):**
- `BookingService` → split into Booking, Reservation, FreePurchase, Calendar/Meeting, Session orchestration.
- `SiteService` query building → tutor-search domain service + repository queries.
- `NotificationService`/`DbNotificationService` → notification registry + contracts.
- Helpers → typed support classes (TimeZone, Currency, Storage, FileUpload, SessionRole).
- User role resolution to a first-class principal object (web vs API vs queue aware).

**Rebuild (target architecture):**
- API controllers to thin adapters calling domain services; introduce DTO/commands.
- Add-on hooks → capability registry instead of scattered `Module::has()`.
- AI assistant auth + deployment topology.
- Tests for core domains.

**Remove:**
- Blog system remnants, empty `resources/views/pages`, `TaxonomyService`, unused exports,
  dead comments, duplicate currency data, `getOrdeWrWithItem`.
- Legacy `certs/` SSL hacks / `updateSaas` env-file mutation if not needed.

---

*End of audit — see `docs/TARGET_ARCHITECTURE.md` and `docs/IMPLEMENTATION_PLAN.md` for the forward plan.*
