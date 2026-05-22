# 🏡 EMERALD-BH

**Emerald Housing** is a multi-tenant property rental platform that bridges **Customers** (tenants), **Land Owners**, and **Community Mediators**. The system ensures fair, legitimate, and transparent rental transactions through role-based access control, multi-step approval workflows, and a clear separation of concerns between Super Admin (operations) and Dev Admin (technical changes).

---

## 🎨 Color Palette & Design System

| Role          | Primary Color | Secondary Color | Accent (Notifications/Actions) |
|---------------|---------------|----------------|--------------------------------|
| Customer      | `#2C7DA0`     | `#E9F5F9`       | `#F6AE6D`                      |
| Land Owner    | `#1A5F4A`     | `#E8F3EE`       | `#D96C3A`                      |
| Community     | `#5E4B8C`     | `#F2EFF9`       | `#F4A261`                      |
| Super Admin   | `#C44536`     | `#FDF2F0`       | `#E9C46A`                      |
| Dev Admin     | `#264653`     | `#E9ECEF`       | `#E76F51`                      |

### Typography
- Headings: **Inter** (sans-serif) – clean, modern
- Body: **Roboto** (sans-serif) – highly readable
- Monospace: **Fira Code** – for admin/dev panels (logs, DB viewer)

### UI Principles
- **Mobile-first** responsive layout (Tailwind CSS 3+)
- **Glassmorphism** cards for dashboards (backdrop blur, semi-transparent)
- **Dark mode** ready – uses CSS variables for dynamic switching
- **Progress steps** for rental lifecycle (Listed → Approved → Rented → Completed)

---

## 🗄️ Database Structure (Laravel Migration Schema)

### Core Tables

#### `users` (extends Laravel’s default)
| Column             | Type      | Description                                  |
|--------------------|-----------|----------------------------------------------|
| id                 | bigint    |                                              |
| name               | string    |                                              |
| email              | string    | unique                                       |
| role               | enum      | customer, land_owner, community, super_admin, dev_admin |
| email_verified_at  | timestamp |                                              |
| password           | string    |                                              |
| profile_photo      | string    | nullable – path to avatar                    |
| is_active          | boolean   | deactivate by super admin                    |
| remember_token     | string    |                                              |
| timestamps         |           |                                              |

#### `properties`
| Column           | Type      | Description                                  |
|------------------|-----------|----------------------------------------------|
| id               | bigint    |                                              |
| land_owner_id    | foreign   | references users(id)                         |
| title            | string    |                                              |
| description      | text      |                                              |
| address          | text      | full address                                |
| city             | string    |                                              |
| price_per_month  | decimal   |                                              |
| deposit          | decimal   |                                              |
| terms_of_rental  | text      | e.g., "minimum 6 months", "no pets"          |
| status           | enum      | pending, approved, rejected, rented, inactive |
| approved_by      | foreign   | super_admin_id (nullable)                   |
| approved_at      | timestamp | nullable                                     |
| created_at       | timestamp |                                              |
| updated_at       | timestamp |                                              |

#### `rental_agreements`
| Column              | Type      | Description                                  |
|---------------------|-----------|----------------------------------------------|
| id                  | bigint    |                                              |
| property_id         | foreign   |                                              |
| customer_id         | foreign   |                                              |
| community_id        | foreign   | assigned mediator (nullable at start)        |
| start_date          | date      |                                              |
| end_date            | date      |                                              |
| total_rent          | decimal   |                                              |
| deposit_paid        | boolean   |                                              |
| status              | enum      | requested, community_review, active, terminated, completed |
| community_notes     | text      | feedback from mediator                      |
| super_admin_override| text      | notes if super admin intervenes              |
| signed_by_customer  | timestamp | nullable – digital signature                 |
| signed_by_owner     | timestamp | nullable                                     |
| timestamps          |           |                                              |

#### `transactions`
| Column            | Type      | Description                                  |
|-------------------|-----------|----------------------------------------------|
| id                | bigint    |                                              |
| rental_agreement_id| foreign  |                                              |
| amount            | decimal   |                                              |
| type              | enum      | rent, deposit, fee, refund                   |
| status            | enum      | pending, completed, failed, disputed         |
| payment_method    | string    |                                              |
| receipt_url       | string    | nullable                                     |
| disputed_by       | foreign   | user_id                                      |
| resolution        | text      | decided by community or super admin          |

#### `change_requests` (Dev Admin queue)
| Column            | Type      | Description                                  |
|-------------------|-----------|----------------------------------------------|
| id                | bigint    |                                              |
| requested_by      | foreign   | super_admin_id                               |
| request_type      | enum      | feature, bugfix, config_change, ui_update   |
| description       | text      |                                              |
| priority          | enum      | low, medium, high, critical                  |
| status            | enum      | pending, in_progress, deployed, rejected    |
| dev_admin_note    | text      | response from dev admin                      |
| deployed_at       | timestamp | nullable                                     |

#### Additional tables
- `property_media` – images/videos for properties
- `notifications` – for all roles (in-app + email)
- `community_votes` – when multiple mediators are involved (optional)
- `audit_logs` – track all status changes (for compliance)

---

## 🏗️ Architecture Design (Least Privilege)

### Laravel (Backend) – Role-based Access Control (RBAC)

| Role          | Permissions                                                                                     |
|---------------|-------------------------------------------------------------------------------------------------|
| Customer      | View approved properties, request rental, sign agreement, pay rent, dispute transaction, view own history |
| Land Owner    | CRUD own properties, view rental requests, accept/reject tenant, view agreements linked to their properties |
| Community     | View all active agreements assigned to them, verify fairness, moderate disputes, add notes, pause transactions |
| Super Admin   | Approve/reject properties, override agreements, assign community mediators, view all data, create `change_requests` |
| Dev Admin     | Only `change_requests` table + `migrations` + system configs. No access to rental/financial data. |

> **Dev Admin isolation**: separate guard `dev` with its own login route. Dev admin cannot query `users`, `properties`, or `transactions` – only read `change_requests` and deploy changes.

### API Layer (RESTful + Resource Controllers)

| Endpoint                         | Method | Role Access              | Description                         |
|----------------------------------|--------|--------------------------|-------------------------------------|
| `/api/properties`                | GET    | Customer, Guest          | List approved properties (filterable) |
| `/api/properties`                | POST   | Land Owner               | Create new property (status=pending) |
| `/api/properties/{id}/approve`   | PUT    | Super Admin              | Approve property                    |
| `/api/rental-requests`           | POST   | Customer                 | Request to rent                     |
| `/api/rental-agreements/{id}`    | GET    | Customer, Owner, Community | View agreement details             |
| `/api/rental-agreements/{id}/status` | PATCH | Community or Super Admin | Update status                       |
| `/api/transactions`              | POST   | Customer                 | Pay rent/deposit                    |
| `/api/transactions/{id}/dispute` | POST   | Customer, Owner          | Raise a dispute                     |
| `/api/change-requests`           | GET/POST | Super Admin            | Manage change requests              |
| `/api/change-requests/{id}/deploy` | PUT  | Dev Admin                | Mark as deployed                    |

> All endpoints validated by **Laravel Policies** and **middleware `role:`** .

---

## ⚙️ Project Logic (Workflow)

### 1. Property Listing Lifecycle
1. Land Owner submits property → status `pending`
2. Super Admin reviews → either `approved` or `rejected`
3. Once approved, becomes visible to Customers.

### 2. Rental Agreement Flow
1. Customer finds approved property → clicks **"Request to Rent"**
2. System creates `rental_agreement` with status `requested`.
3. Super Admin assigns a **Community** member to this agreement.
4. Community mediator validates terms & fairness → sets status `community_review` → if OK, sets to `active`.
5. Customer and Land Owner sign digitally (timestamp in DB).
6. Customer pays deposit + first rent → transaction recorded.
7. On end date → status `completed` (or `terminated` early by community/super admin).

### 3. Dispute Resolution
- Customer or Land Owner raises dispute on a `transaction`.
- Community mediator investigates → proposes resolution.
- If either party disagrees → Super Admin final decision.
- Resolution logged in `transactions.resolution`.

### 4. Change Management (Dev Admin)
- Super Admin creates a `change_request` (e.g., "Add monthly invoice PDF").
- Dev Admin sees task in their dashboard → works on feature branch → merges → marks `deployed`.
- No direct DB access for Dev Admin in production – only via migrations/seeds provided by Super Admin.

---

## 🔌 Supporting APIs for Efficient Development

### Backend (Laravel 11)
- **Laravel Sanctum** – API token authentication + role scopes.
- **Laravel Pint** – code style enforcement.
- **Laravel Telescope** (non-prod) – debug requests & queries.
- **Laravel Pulse** – monitor performance.
- **Database Queues** – for email notifications & PDF generation.

### Frontend (Vue 3 + Inertia.js or Standalone)
- **Pinia** – state management (store user role, notifications).
- **Vue Router** – role-based route guards.
- **Axios** – interceptors to add Bearer token.
- **Vite** – fast HMR.
- **Tailwind CSS** + **Headless UI** – accessible components.
- **Vee-Validate** + **Zod** – form validation.

### External Integrations
| Service           | Purpose                                    |
|-------------------|--------------------------------------------|
| **Stripe Connect** | Split payments between Owner, Community fee, Platform fee. |
| **Mailgun / SES**  | Email notifications (agreement signed, payment received). |
| **Cloudinary**     | Image uploads for properties (optimization). |
| **PDF Generator**  | Generate rental agreement as PDF (Laravel DomPDF). |

### Development Efficiency Tools
- **Laravel IDE Helper** – better autocomplete.
- **Laravel Debugbar** – local only.
- **GitHub Actions** – run tests & lint on PR.
- **Pre-commit hooks** – Laravel Pint + ESLint + Prettier.

---

## 🚀 Getting Started (Local Setup)

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 20+
- MySQL 8.0 or PostgreSQL
- Redis (optional for cache)

### Installation
```bash
git clone https://github.com/your-org/emerald-bh.git
cd emerald-bh

# Backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# Frontend
npm install
npm run dev

# Serve
php artisan serve