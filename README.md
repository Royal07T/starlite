# Starlite (Lernen) — Online Tutoring & Education Marketplace

A full-featured online tutoring and education marketplace platform built with Laravel 11, Livewire 3, and modular architecture. Starlite connects tutors with students for 1-on-1 sessions, and supports courses, course bundles, and real-time communication.

**Version:** 3.0.1  
**License:** MIT

---

## About

Starlite (internally branded "Lernen") is a complete SaaS platform for online education that provides:

- **1-on-1 Tutoring** — Tutors create profiles, set subjects with hourly rates, define available time slots, and students book and pay for sessions
- **Course Marketplace** — Instructors create video/audio/live/article courses with sections, curriculum, pricing, and enrollments
- **Course Bundles** — Package multiple courses together at a discounted price
- **Real-Time Chat** — Built-in messaging system between tutors and students via LaraGuppy
- **Payment Processing** — Integrated Stripe, Paystack, and PayFast gateways with wallet-based payouts
- **Admin Dashboard** — Full platform management with insights, user management, bookings, disputes, and settings
- **Video Conferencing** — Zoom and Google Meet integration for online sessions
- **Google Calendar Sync** — Automatic calendar event creation for bookings

---

## Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | Laravel 11.9 (PHP 8.2+) |
| **Frontend UI** | Livewire 3 + Alpine.js + Blade templates |
| **CSS** | Tailwind CSS 3 + Bootstrap 5 (hybrid) |
| **Build Tool** | Vite 5 |
| **Database** | MySQL |
| **Auth** | Laravel Sanctum (API) + Session-based (web) |
| **Roles/Permissions** | Spatie Laravel Permission v6 |
| **Real-Time** | Laravel Reverb (WebSockets) + LaraGuppy (chat) |
| **Modularization** | nwidart/laravel-modules v11 |
| **Payments** | Stripe, Paystack, PayFast |
| **Video** | Zoom, Google Meet (via MeetFusion module) |
| **AI** | OpenAI API integration |
| **PDF Generation** | DomPDF + Browsershot (Puppeteer) |
| **Page Builder** | Larabuild PageBuilder (drag & drop) |
| **Settings** | Larabuild OptionBuilder (23 admin panels) |

---

## Features

### Tutoring
- Tutor profiles with subjects, hourly rates, and availability
- Recurring and single time slot creation
- Slot booking with reservation system (auto-expiry)
- Session rescheduling and cancellation
- Booking logs and activity tracking
- Google Calendar integration for session management
- Meeting link generation (Zoom / Google Meet)

### Courses & Bundles
- Course creation with sections and curriculum items (video, audio, live, article)
- Course pricing with discounts and session packs
- Student enrollment and progress tracking (watchtime)
- Course categories, tags, and search filtering
- Discussion forums per course
- Course bundles with multi-course pricing
- Certificate generation on completion

### Payments & Wallets
- Multi-gateway support: Stripe, Paystack, PayFast
- Shopping cart with coupon support (KuponDeal module)
- Two-stage wallet system: pending → available
- Tutor withdrawal requests with admin approval
- Platform commission with configurable rates
- Invoice generation (PDF)

### Admin Panel
- Platform insights and analytics dashboard
- User management (admin, sub_admin, tutor, student)
- Identity verification (KYC) management
- Booking and invoice management
- Dispute resolution system
- Email and notification template management
- Blog and content management
- Language translator
- Taxonomy management (languages, subjects, subject groups)
- Commission and payment settings
- Package/addon management

### Real-Time
- LaraGuppy messaging system
- In-app notifications
- Email notifications (template-driven)
- Online user tracking

### Content
- CMS page builder with 40+ homepage sections
- Blog system with categories and tags
- Breadcrumb navigation
- 8 homepage color variations
- RTL support

---

## Requirements

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 16.x & NPM
- **MySQL** >= 5.7
- **PHP Extensions:** OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath, GD, Imagick

---

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/shamszy/starlite.git
cd starlite
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Environment Configuration

```bash
cp .env.example .env
```

Edit `.env` and configure the following:

```env
# Application
APP_NAME=Lernen
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=starlite_db
DB_USERNAME=root
DB_PASSWORD=

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Cache
CACHE_STORE=file

# Queue
QUEUE_CONNECTION=sync
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Create Database & Run Migrations

```bash
# Create the database (MySQL)
mysql -u root -e "CREATE DATABASE starlite_db;"

# Run migrations
php artisan migrate --force

# Seed the database
php artisan db:seed
```

### 6. Install Frontend Dependencies & Build

```bash
npm install
npm run build
```

### 7. Create Storage Symlink

```bash
php artisan storage:link
```

### 8. Start the Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

---

## Environment Configuration

### Database
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=starlite_db
DB_USERNAME=root
DB_PASSWORD=
```

### Mail (SMTP)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=hello@laravel.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Google OAuth (Social Login)
```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

### Google Calendar
```env
GOOGLE_CALENDAR_CLIENT_ID=
GOOGLE_CALENDAR_CLIENT_SECRET=
GOOGLE_CALENDAR_REDIRECT_URI="${APP_URL}/google/callback"
```

### OpenAI
```env
OPENAI_API_KEY=
OPENAI_ORGANIZATION=
```

### Zoom
```env
ZOOM_API_KEY=
ZOOM_API_SECRET=
```

### DigitalOcean Spaces (S3-compatible storage)
```env
DO_SPACES_KEY=
DO_SPACES_SECRET=
DO_SPACES_ENDPOINT=
DO_SPACES_REGION=
DO_SPACES_BUCKET=
DO_SPACES_URL=
```

### Broadcasting (Laravel Reverb)
```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http
```

---

## Project Structure

```
starlite/
├── app/
│   ├── Casts/                      # 12 custom Eloquent casts
│   ├── Console/Commands/           # 5 Artisan commands
│   ├── Exports/                    # Excel exports (Maatwebsite)
│   ├── Facades/                    # Cart, DbNotification facades
│   ├── Helpers/                    # Global helper functions
│   ├── Http/
│   │   ├── Controllers/            # 27 controllers
│   │   │   ├── Admin/              # Admin controllers
│   │   │   ├── Api/                # 19 REST API controllers
│   │   │   ├── Auth/               # Social auth, email verification
│   │   │   └── Frontend/           # Search controller
│   │   ├── Middleware/             # 7 custom middleware
│   │   ├── Requests/              # 33 form request classes
│   │   └── Resources/             # 41 API resource transformers
│   ├── Jobs/                       # 11 queued jobs
│   ├── Listeners/                  # 3 event listeners
│   ├── Livewire/                   # Livewire components (primary UI)
│   │   ├── Components/            # 6 reusable components
│   │   ├── Forms/                 # Livewire form objects
│   │   ├── Frontend/              # Public-facing pages
│   │   └── Pages/                 # Dashboard pages (Admin, Tutor, Student)
│   ├── Models/                     # 46 Eloquent models
│   │   └── Scopes/                # Global scopes
│   ├── Notifications/              # Email + DB notification classes
│   ├── Observers/                  # 3 Eloquent observers
│   ├── Optionbuilder/              # 23 admin settings panels
│   ├── Providers/                  # 4 service providers
│   ├── Services/                   # 27 business logic service classes
│   ├── Spotlight/                  # Cmd+K search integrations
│   ├── Traits/                     # ApiResponser, PrepareForValidation
│   └── View/                       # Blade view components & composers
├── Modules/
│   ├── Courses/                    # "Learnty" — Course management
│   │   ├── Http/Controllers/       # Course + Video controllers
│   │   ├── Livewire/              # Course pages
│   │   ├── Models/                # 13 models (Course, Section, Curriculum, etc.)
│   │   ├── Services/              # CourseService, CurriculumService
│   │   └── routes/                # web.php, api.php
│   ├── CourseBundles/              # "LearntySuite" — Bundle management
│   │   ├── Livewire/              # Bundle pages
│   │   ├── Models/                # 3 models (Bundle, CourseBundle, BundlePurchase)
│   │   └── Services/              # BundleService
│   ├── LaraPayease/               # Payment gateway integration
│   │   ├── Drivers/               # Stripe, Paystack drivers
│   │   └── Http/Controllers/      # Payment controllers
│   └── MeetFusion/                 # Video conferencing
│       ├── Drivers/               # GoogleMeet, Zoom drivers
│       └── Http/Controllers/      # Meeting controllers
├── config/                         # 28 configuration files
├── database/
│   ├── migrations/                 # 73 migration files
│   ├── seeders/                    # 22 seeder classes
│   └── factories/                  # 4 model factories
├── packages/                       # Local Composer packages
│   ├── larabuild/optionbuilder/    # Admin settings builder
│   ├── larabuild/pagebuilder/      # Drag & drop page builder
│   ├── laraguppy/                  # Real-time messaging
│   └── laravel-installer/          # Web installer
├── resources/
│   ├── css/                        # Main CSS (Tailwind + vendor)
│   ├── js/                         # Main JS entry
│   └── views/                      # 228 Blade templates
│       ├── components/             # 37 Blade components
│       ├── layouts/                # 4 master layouts
│       ├── livewire/               # 82 Livewire views
│       ├── pagebuilder/            # 40 CMS section templates
│       └── skeletons/              # 19 loading placeholders
├── routes/
│   ├── web.php                     # Main web routes
│   ├── admin.php                   # Admin panel routes
│   ├── api.php                     # REST API routes
│   ├── auth.php                    # Authentication routes
│   ├── breadcrumbs.php             # Breadcrumb definitions
│   ├── optionbuilder.php           # Settings routes
│   └── pagebuilder.php             # CMS page routes
├── public/                          # Web-accessible assets
│   ├── build/                      # Vite compiled output
│   ├── css/                        # Pre-compiled CSS
│   ├── js/                         # Vendor JavaScript
│   ├── admin/                      # Admin panel assets
│   ├── images/                     # Static images
│   └── modules/                    # Module-specific assets
├── .env                            # Environment configuration
├── artisan                         # Laravel CLI
├── composer.json                   # PHP dependencies
├── package.json                    # Node dependencies
├── vite.config.js                  # Vite build configuration
├── tailwind.config.js              # Tailwind CSS configuration
└── phpunit.xml                     # PHPUnit configuration
```

---

## Architecture

### Service Layer
The project uses a hand-built service layer (no repository pattern). 27 service classes in `app/Services/` encapsulate all business logic and interact directly with Eloquent models. Key services:

| Service | Responsibility |
|---|---|
| `BookingService` | Slot creation, booking lifecycle, reservations, meeting links |
| `CartService` | Session-based shopping cart with coupon support |
| `OrderService` | Order management, platform fees, order item CRUD |
| `WalletService` | Two-stage wallet: pending → available funds |
| `PayoutService` | Withdrawal requests, payout methods |
| `NotificationService` | Template-driven email notifications (25+ types) |
| `DbNotificationService` | Template-driven in-app notifications |
| `DisputeService` | Dispute lifecycle, conversation threads |
| `CourseService` | Course CRUD, enrollment, filtering |
| `RegisterService` | User registration, social login |
| `SiteService` | Public tutor search and discovery |
| `GoogleCalender` | Google Calendar API integration |
| `ZoomService` | Zoom meeting creation |

### Modular Design
The platform uses `nwidart/laravel-modules` with 4 active modules:

| Module | Purpose | Models |
|---|---|---|
| **Courses** | Course creation, enrollment, progress tracking | 13 |
| **CourseBundles** | Bundle multiple courses | 3 |
| **LaraPayease** | Stripe & Paystack payment drivers | — |
| **MeetFusion** | Zoom & Google Meet video drivers | — |

Each module is self-contained with its own Models, Controllers, Services, Routes, Views, and Migrations. Module tables use a `courses_` prefix.

### Authentication & Authorization
- **Web:** Session-based with Livewire Volt (login, register, password reset)
- **API:** Laravel Sanctum token-based auth (7-day token expiry)
- **Social Login:** Google, Facebook, GitHub via Laravel Socialite
- **Roles:** admin, sub_admin, tutor, student (Spatie Permission)
- **Role Switching:** Users can toggle between tutor/student roles via session
- **Permissions:** 33 granular permissions for admin routes
- **Admin Bypass:** Admin role bypasses all Gate/authorization checks

### Frontend
- **Livewire 3** — Primary UI framework (30+ components)
- **Alpine.js** — Lightweight client-side interactivity
- **Blade Templates** — 228 templates across 4 layouts
- **Tailwind CSS + Bootstrap 5** — Hybrid styling approach
- **Vite 5** — Asset bundling with custom module loader
- **Livewire Spotlight** — Cmd+K search overlay

---

## API Documentation

### Public Endpoints (No Auth Required)

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/login` | User login |
| POST | `/api/register` | User registration |
| POST | `/api/social-login` | Social OAuth login |
| POST | `/api/forget-password` | Request password reset |
| GET | `/api/find-tutors` | Search tutors |
| GET | `/api/tutor/{slug}` | Tutor detail |
| GET | `/api/recommended-tutors` | Recommended tutors |
| GET | `/api/tutor-available-slots` | Available slots |
| GET | `/api/countries` | List countries |
| GET | `/api/languages` | List languages |
| GET | `/api/subject-groups` | List subject groups |
| GET | `/api/subjects` | List subjects |
| GET | `/api/settings` | Public settings |

### Authenticated Endpoints (Sanctum Required)

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/logout` | Logout |
| POST | `/api/reset-password` | Reset password |
| GET | `/api/profile-settings/{id}` | Get profile |
| POST | `/api/profile-settings/{id}` | Update profile |
| GET | `/api/upcoming-bookings` | Upcoming bookings |
| POST | `/api/book-free-slot` | Book free slot |
| POST | `/api/complete-booking/{id}` | Complete booking |
| POST | `/api/checkout` | Process checkout |
| GET/POST | `/api/billing-detail` | Billing details |
| GET | `/api/invoices` | List invoices |
| GET | `/api/favourite-tutors` | Favourite tutors |
| POST | `/api/review/{id}` | Add review |
| POST | `/api/dispute/{id}` | Create dispute |
| GET | `/api/notifications` | List notifications |
| POST | `/api/notifications/{id}/read` | Mark as read |
| POST | `/api/user-withdrawal` | Request withdrawal |
| CRUD | `/api/booking-cart` | Shopping cart |
| CRUD | `/api/tutor-education` | Education records |
| CRUD | `/api/tutor-experience` | Experience records |
| CRUD | `/api/tutor-certification` | Certifications |
| CRUD | `/api/identity-verification` | Identity verification |

### Course API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/courses` | List courses |
| GET | `/api/course-detail/{slug}` | Course detail |
| GET | `/api/categories` | Course categories |
| GET | `/api/enrolled-courses` | Enrolled courses (auth) |
| POST | `/api/enroll-course` | Enroll in course (auth) |
| POST | `/api/update-progress` | Update watch progress (auth) |

---

## Roles & Permissions

### Roles
| Role | Description |
|---|---|
| `admin` | Full platform access (bypasses all authorization) |
| `sub_admin` | Limited admin with granular permissions |
| `tutor` | Creates profiles, subjects, sessions; earns from bookings |
| `student` | Books sessions, purchases courses, leaves reviews |

### Admin Permissions (33 total)
| Permission | Grants Access To |
|---|---|
| `can-manage-insights` | Platform analytics dashboard |
| `can-manage-menu` | Navigation menu management |
| `can-manage-all-blogs` | Blog listing |
| `can-manage-create-blogs` | Blog creation |
| `can-manage-update-blogs` | Blog editing |
| `can-manage-blog-categories` | Blog category management |
| `can-manage-language-translations` | Language translator |
| `can-manage-languages` | Language taxonomy |
| `can-manage-subjects` | Subject taxonomy |
| `can-manage-subject-groups` | Subject group taxonomy |
| `can-manage-commission-settings` | Commission rates |
| `can-manage-payment-methods` | Payment method config |
| `can-manage-withdraw-requests` | Withdrawal approvals |
| `can-manage-admin-users` | Admin user management |
| `can-manage-users` | User management |
| `can-manage-identity-verification` | KYC verification |
| `can-manage-reviews` | Review management |
| `can-manage-bookings` | Booking management |
| `can-manage-invoices` | Invoice management |
| `can-manage-email-settings` | Email template management |
| `can-manage-notification-settings` | Notification template management |
| `can-manage-upgrade` | System upgrade |
| `can-manage-addons` | Package/addon management |
| `can-manage-disputes-list` | Dispute listing |
| `can-manage-dispute` | Dispute resolution |
| `can-manage-option-builder` | Settings, SMTP, social login |

---

## Default Admin Credentials

After seeding, you can log in with:

- **Email:** admin@starlite.com
- **Password:** password

> **Note:** Change these credentials in production.

---

## Available Modules (Addons)

| Module | Name | Type | Price |
|---|---|---|---|
| `optionbuilder` | Option Builder | Core | Free |
| `pagebuilder` | Drag & Drop Page Builder | Core | Free |
| `laraguppy` | LaraGuppy (Real-Time Chat) | Core | Free |
| `larapayease` | LaraPayEase | External | $29 (lite) |
| `meetfusion` | MeetFusion | External | $20 (lite) |
| `upcertify` | upCertify (Certificates) | External | $20 |
| `forumwise` | ForumWise (Community Board) | External | $20 |
| `s3konnect` | S3Konnect (AWS S3) | External | $20 |
| `kupondeal` | KuponDeal (Coupons) | External | $20 |
| `starup` | StarUp (Badges) | External | $20 |
| `courses` | Learnty (Courses) | External | $29 |
| `subscriptions` | LearnTier (Subscriptions) | External | $20 |
| `quiz` | QuizDeck (Quizzes) | External | $20 |
| `coursebundles` | LearntySuite (Course Bundles) | External | $20 |
| `assignments` | Assignora (Assignments) | External | $20 |
| `ipmanager` | SmartIpier (IP/Session Manager) | External | $20 |
| `spacekonnect` | SpaceKonnect (DigitalOcean Spaces) | External | $20 |

---

## Testing

```bash
# Run all tests
php artisan test

# Run with PHPUnit
./vendor/bin/phpunit

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

---

## Queue & Jobs

Starlite uses queued jobs for async operations. Key jobs:

| Job | Purpose |
|---|---|
| `CompletePurchaseJob` | Process paid orders, credit wallets |
| `CompleteFreePurchaseJob` | Process free orders |
| `CompleteBookingJob` | Auto-complete bookings after session end |
| `CreateGoogleCalendarEventJob` | Create calendar events |
| `RemoveBookingReservationJob` | Auto-expire reserved bookings |
| `GenerateCertificateJob` | Generate completion certificates |
| `SendNotificationJob` | Send email notifications |
| `SendDbNotificationJob` | Send in-app notifications |

Run the queue worker:

```bash
php artisan queue:work
```

---

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---

## Support

- **Documentation:** [GitHub Repository](https://github.com/shamszy/starlite)
- **Issues:** [GitHub Issues](https://github.com/shamszy/starlite/issues)

---

## Acknowledgments

- [Laravel](https://laravel.com/) — The PHP framework
- [Livewire](https://livewire.laravel.com/) — Full-stack framework
- [Spatie](https://spatie.be/) — Laravel packages
- [nwidart](https://nwidart.com/) — Laravel Modules
- [Tailwind CSS](https://tailwindcss.com/) — Utility-first CSS
