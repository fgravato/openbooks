# OpenBooks — Product Requirements Document

**Cloud-Based Invoicing & Accounting Platform**
*FreshBooks-Equivalent Open-Source Implementation — Built with PHP 8.5 + Laravel 12*

| Document Info | Details |
|---|---|
| Version | 1.0 |
| Date | February 5, 2026 |
| Author | Frankie (Lookout) |
| Status | Draft |
| Classification | Internal |
| Target Platform | PHP 8.5 / Laravel 12 / MySQL 8+ / Redis |

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Technology Stack](#2-technology-stack)
3. [System Architecture](#3-system-architecture)
4. [Feature Specifications](#4-feature-specifications)
   - 4.1 Dashboard
   - 4.2 Invoicing
   - 4.3 Billing & Payments
   - 4.4 Expense Tracking
   - 4.5 Time Tracking
   - 4.6 Projects & Collaboration
   - 4.7 Client Management
   - 4.8 Estimates & Proposals
   - 4.9 Accounting & Bookkeeping
   - 4.10 Financial Reporting
   - 4.11 Team Management
   - 4.12 Payroll Integration
   - 4.13 Mobile Application
   - 4.14 Integrations & API
5. [Database Schema](#5-database-schema)
6. [Security Requirements](#6-security-requirements)
7. [Performance Requirements](#7-performance-requirements)
8. [Subscription Tier Architecture](#8-subscription-tier-architecture)
9. [Deployment Architecture](#9-deployment-architecture)
10. [Testing Strategy](#10-testing-strategy)
11. [Development Roadmap](#11-development-roadmap)
12. [Glossary](#12-glossary)
13. [Appendix](#13-appendix)

---

## 1. Executive Summary

OpenBooks is a full-featured, open-source cloud-based invoicing and accounting platform designed to replicate the complete feature set of FreshBooks. Built on PHP 8.5 with the Laravel 12 framework, it targets freelancers, solopreneurs, small businesses with employees, and businesses working with contractors.

The platform provides professional invoicing, online payment acceptance, expense tracking, time tracking, project management, payroll integration, double-entry accounting, financial reporting, team management, client portals, estimates/proposals, and a comprehensive REST API for third-party integrations.

### 1.1 Product Vision

Deliver a self-hosted or cloud-deployed accounting solution that matches FreshBooks feature-for-feature, providing small businesses with Fortune 500-level professional financial management without recurring SaaS subscription costs.

### 1.2 Target Users

- Freelancers and independent contractors managing 1–5 clients
- Solopreneurs running service-based businesses
- Small businesses with employees (2–50 staff)
- Agencies and firms managing contractors
- Accountants and bookkeepers serving multiple clients

### 1.3 Key Differentiators from FreshBooks

- Self-hosted option with full data ownership
- No per-client limits on any tier
- Built on modern PHP 8.5 with fibers, typed properties, and enums
- Open REST API with no rate-limit tiers
- Plugin/extension architecture for custom modules

---

## 2. Technology Stack

### 2.1 Core Stack

| Layer | Technology | Version | Purpose |
|---|---|---|---|
| Language | PHP | 8.5 | Server-side logic with fibers, enums, typed properties |
| Framework | Laravel | 12.x | MVC framework, Eloquent ORM, queues, events, scheduling |
| Database | MySQL | 8.0+ | Primary relational data store with InnoDB |
| Cache/Queue | Redis | 7.x | Session cache, job queues, real-time broadcasting |
| Search | Meilisearch | 1.x | Full-text search for invoices, clients, expenses |
| File Storage | S3-Compatible | Any | Receipt images, invoice PDFs, logos, attachments |
| Mail | SMTP / SES / Mailgun | Any | Transactional email delivery |
| PDF Engine | wkhtmltopdf / Dompdf | Latest | Invoice and report PDF generation |
| Task Scheduler | Laravel Scheduler | 12.x | Recurring invoices, reminders, reports |

### 2.2 Frontend Stack

| Technology | Version | Purpose |
|---|---|---|
| Inertia.js | 2.x | SPA-like experience with server-side routing |
| Vue.js | 3.x | Reactive UI components |
| Tailwind CSS | 4.x | Utility-first styling |
| Alpine.js | 3.x | Lightweight interactivity for dropdowns, modals |
| Chart.js | 4.x | Dashboard charts and financial visualizations |
| Vite | 6.x | Asset bundling and HMR |

### 2.3 Infrastructure & DevOps

| Component | Technology | Notes |
|---|---|---|
| Web Server | Nginx + PHP-FPM | Reverse proxy with FastCGI |
| Containerization | Docker + Docker Compose | Development and production deployment |
| CI/CD | GitHub Actions | Automated testing, linting, deployment |
| Monitoring | Laravel Telescope + Sentry | Debug, performance monitoring, error tracking |
| Testing | PHPUnit + Pest | Unit, feature, and integration tests |
| API Docs | Swagger / OpenAPI 3.1 | Auto-generated API documentation |
| Code Quality | PHPStan Level 8 + Pint | Static analysis and code formatting |

### 2.4 PHP 8.5 Features Utilized

- **Fibers** for non-blocking payment gateway callbacks and webhook processing
- **Enums** for invoice statuses, expense categories, payment methods, user roles
- **Readonly classes** for value objects (Money, Address, TaxRate)
- **First-class callable syntax** for event listeners and middleware
- **Named arguments** throughout configuration and service binding
- **Intersection types** for repository interfaces
- **Match expressions** for status-dependent business logic

---

## 3. System Architecture

### 3.1 Architecture Pattern

The application follows a Domain-Driven Design (DDD) approach with clearly bounded contexts, layered on top of Laravel's MVC foundation. Each major feature module is a self-contained domain with its own models, services, repositories, events, and policies.

#### 3.1.1 Bounded Contexts

| Domain | Responsibility | Key Entities |
|---|---|---|
| Identity | Authentication, authorization, user management, OAuth | User, Role, Permission, Team, OAuthClient |
| Client Management | Client profiles, contacts, client portal access | Client, Contact, ClientPortalToken |
| Invoicing | Invoice lifecycle, line items, templates, recurring profiles | Invoice, InvoiceLine, InvoiceProfile, InvoiceTemplate |
| Payments | Payment recording, online payment gateways, refunds | Payment, PaymentGateway, Refund, CreditNote |
| Expenses | Expense tracking, receipts, categories, bank imports | Expense, ExpenseCategory, Receipt, BankConnection |
| Time Tracking | Timers, manual entries, billable hours | TimeEntry, Timer, Service |
| Projects | Project management, budgets, tasks, team assignments | Project, Task, ProjectMember, Budget |
| Accounting | Double-entry ledger, journal entries, chart of accounts, reconciliation | Account, JournalEntry, Transaction, Reconciliation |
| Reporting | Financial reports, P&L, balance sheet, tax summaries | Report, ReportFilter, ReportExport |
| Estimates | Estimates, proposals, conversion to invoices | Estimate, Proposal, EstimateLine |
| Team | Staff, contractors, roles, permissions, payroll integration | TeamMember, Contractor, PayrollRun |
| Notifications | Email, in-app, SMS notifications, reminders | Notification, Reminder, NotificationPreference |
| Settings | Organization settings, taxes, currencies, templates | Organization, TaxRate, Currency, BrandingConfig |
| Integrations | REST API, webhooks, OAuth apps, third-party connectors | ApiToken, Webhook, OAuthApp, Integration |

### 3.2 Multi-Tenancy Strategy

The system uses a single-database, shared-schema multi-tenancy approach. Every business entity is scoped by an `organization_id` foreign key. Laravel global scopes automatically filter all queries to the current tenant. Tenant resolution occurs via subdomain (`acme.openbooks.app`) or via API authentication token.

### 3.3 Authentication & Authorization

- OAuth 2.0 with PKCE for API authentication (Laravel Passport)
- Session-based authentication for web UI (Laravel Fortify)
- Two-factor authentication (TOTP) with recovery codes
- Role-based access control: Owner, Admin, Manager, Employee, Contractor, Accountant, Client
- Fine-grained permissions per module (view, create, edit, delete, export)
- Client portal with magic-link and token-based access

---

## 4. Feature Specifications

### 4.1 Dashboard

The main dashboard provides a real-time financial overview upon login, replicating FreshBooks' central dashboard experience.

#### 4.1.1 Dashboard Widgets

- **Revenue Summary:** Total revenue for current month/quarter/year with trend comparison
- **Outstanding Invoices:** Count and total amount of unpaid invoices
- **Overdue Invoices:** Highlighted with aging breakdown (30/60/90 days)
- **Recent Expenses:** Last 10 expenses with running total for the period
- **Profit & Loss Snapshot:** Revenue minus expenses with period comparison
- **Unbilled Time:** Total hours and dollar value of unbilled time entries
- **Upcoming Payments:** Scheduled and expected incoming payments
- **Recent Activity:** Timeline of recent invoices sent, payments received, expenses logged

#### 4.1.2 Dashboard Requirements

| Req ID | Requirement | Priority |
|---|---|---|
| DASH-001 | Dashboard loads all widgets within 2 seconds on standard connection | P0 |
| DASH-002 | Revenue chart supports daily, weekly, monthly, quarterly, yearly views | P0 |
| DASH-003 | Outstanding invoices widget links directly to filtered invoice list | P1 |
| DASH-004 | Dashboard data refreshes automatically every 60 seconds via polling | P2 |
| DASH-005 | Mobile-responsive layout with stacked widgets on screens < 768px | P0 |
| DASH-006 | Currency formatting respects organization locale settings | P0 |

---

### 4.2 Invoicing

The invoicing module is the core of the platform, replicating FreshBooks' full invoicing capabilities including customizable templates, recurring invoices, automated reminders, online payment integration, and detailed status tracking.

#### 4.2.1 Invoice Lifecycle

Invoices follow a strict state machine:

```
Draft → Sent → Viewed → Partial → Paid
                                  ↗
                        Overdue ──┘ (auto-triggered by due date)

Draft → Cancelled
Any state → Archived
```

#### 4.2.2 Invoice Features

- Custom invoice templates with logo, colors, fonts, and layout selection
- Line items with description, quantity, unit price, tax, and discount
- Support for hourly, flat-rate, and itemized billing
- Automatic addition of tracked billable time and expenses to invoice lines
- Multi-currency support with real-time exchange rate lookup
- Configurable payment terms (Net 15, Net 30, Net 60, custom)
- Deposit/retainer invoicing with configurable percentage or fixed amount
- Late fee configuration: flat fee or percentage, with grace period
- Tax calculation with support for multiple tax rates (e.g., HST, GST+PST, VAT)
- Compound tax support (tax-on-tax)
- Invoice numbering with configurable prefix and auto-increment pattern
- PO number field and custom reference fields
- Notes and terms fields with default templates
- File attachments on invoices (PDF, images)
- Invoice duplication for quick creation
- Batch operations: send, archive, delete multiple invoices

#### 4.2.3 Recurring Invoices (Invoice Profiles)

- Create recurring invoice profiles with frequency: weekly, bi-weekly, monthly, quarterly, annually, custom
- Configurable start date, end date, and occurrence count
- Auto-send on generation or save as draft for review
- Email notification when recurring invoice is generated
- Pause/resume recurring profiles
- Next issue date preview

#### 4.2.4 Invoice Delivery

- Send via email with customizable subject and body templates
- Shareable secure link for online viewing and payment
- PDF download and print support
- Read receipts: track when client opens/views the invoice
- Automated payment reminders: configurable schedule (before due, on due, after due)
- Bulk email sending for multiple invoices

#### 4.2.5 Invoice Requirements

| Req ID | Requirement | Priority |
|---|---|---|
| INV-001 | Create, edit, delete, duplicate invoices with full line-item support | P0 |
| INV-002 | Support Draft, Sent, Viewed, Partial, Paid, Overdue, Cancelled statuses | P0 |
| INV-003 | Generate PDF invoices matching selected template with sub-second rendering | P0 |
| INV-004 | Send invoices via email with tracking pixel for view confirmation | P0 |
| INV-005 | Recurring invoice engine processes profiles via scheduled job | P0 |
| INV-006 | Multi-currency invoicing with automatic exchange rate lookup | P1 |
| INV-007 | Accept online payments via Stripe integration on invoice | P0 |
| INV-008 | Automatically calculate single, multiple, and compound taxes | P0 |
| INV-009 | Support deposit/retainer invoicing with partial payment tracking | P1 |
| INV-010 | Late fee auto-application based on configurable rules | P1 |
| INV-011 | Invoice search by number, client, amount, date range, status | P0 |
| INV-012 | Audit log for all invoice modifications with user attribution | P1 |

---

### 4.3 Billing & Payments

The payments module handles all aspects of receiving and recording payments, replicating FreshBooks' Stripe-powered online payment system with support for credit cards, ACH, Apple Pay, Google Pay, and PayPal.

#### 4.3.1 Online Payment Methods

| Method | Provider | Processing Fee | Settlement |
|---|---|---|---|
| Credit/Debit Card | Stripe | 2.9% + $0.30 | 2 business days |
| ACH Bank Transfer | Stripe | 1.0% (capped $5) | 5–7 business days |
| Apple Pay | Stripe | 2.9% + $0.30 | 2 business days |
| Google Pay | Stripe | 2.9% + $0.30 | 2 business days |
| PayPal | PayPal API | 2.9% + $0.30 | Instant to PayPal |
| Buy Now, Pay Later | Stripe (Affirm/Klarna) | Varies | 2 business days |

#### 4.3.2 Payment Features

- One-click payment from invoice email or shareable link
- Partial payment acceptance with balance tracking
- Payment recording for offline payments (check, cash, bank transfer)
- Automatic invoice status update on payment receipt
- Credit notes and refund processing
- Client credit balance management
- Payment receipt auto-email to client
- Virtual terminal for manual card charges (Advanced Payments add-on equivalent)
- Stored card / subscription billing for retainer clients
- PCI-DSS compliance through Stripe Elements (no card data touches server)
- Payout reporting and reconciliation

#### 4.3.3 Payment Requirements

| Req ID | Requirement | Priority |
|---|---|---|
| PAY-001 | Stripe integration for credit card, ACH, Apple Pay, Google Pay | P0 |
| PAY-002 | PayPal integration as alternative payment method | P1 |
| PAY-003 | Record offline payments with method, reference, and date | P0 |
| PAY-004 | Auto-update invoice status upon full or partial payment | P0 |
| PAY-005 | Generate and email payment receipts automatically | P0 |
| PAY-006 | Support credit notes and refund workflows | P1 |
| PAY-007 | PCI-DSS compliant payment form via Stripe Elements | P0 |
| PAY-008 | Webhook listener for Stripe payment events (success, failure, dispute) | P0 |

---

### 4.4 Expense Tracking

The expense module provides comprehensive tracking, categorization, receipt management, and bank import capabilities matching FreshBooks' expense system.

#### 4.4.1 Expense Features

- Create expenses with date, amount, merchant/vendor, category, description, and tax
- Multi-level expense categories with custom subcategories
- Receipt attachment via file upload or mobile camera capture
- OCR receipt scanning to auto-populate expense fields
- Bank account connection via Plaid for automatic expense import
- CSV/OFX file import for bank transactions
- Duplicate expense detection with flagging and resolution workflow
- Expense assignment to clients and projects for rebilling
- Markup configuration on rebilled expenses (percentage or fixed)
- Recurring expense profiles (weekly, monthly, yearly, custom)
- Expense splitting across multiple categories
- Multi-currency expense recording with conversion
- Tax tracking per expense with override capability
- Bulk actions: categorize, assign, archive, delete
- Expense reports by client, project, category, date range, team member

#### 4.4.2 Bank Connection

- Plaid integration for connecting bank accounts and credit cards
- Automatic transaction import and categorization via machine learning
- Bank reconciliation: match imported transactions to recorded expenses
- Transfer recording between accounts (e.g., credit card payments)
- Expense refund/credit handling

#### 4.4.3 Expense Requirements

| Req ID | Requirement | Priority |
|---|---|---|
| EXP-001 | CRUD operations for expenses with full field support | P0 |
| EXP-002 | Receipt image upload with S3 storage and thumbnail generation | P0 |
| EXP-003 | OCR receipt scanning via Google Vision or AWS Textract | P1 |
| EXP-004 | Plaid integration for bank account connection and transaction import | P1 |
| EXP-005 | CSV/OFX bank statement import with field mapping | P0 |
| EXP-006 | Duplicate detection algorithm comparing date, amount, description | P1 |
| EXP-007 | Expense rebilling: assign to client, add to invoice with markup | P0 |
| EXP-008 | Recurring expense automation via scheduled jobs | P1 |
| EXP-009 | Bank reconciliation matching engine | P1 |
| EXP-010 | Expense category management with default and custom categories | P0 |

---

### 4.5 Time Tracking

Built-in time tracking allows users to log billable and non-billable hours against clients, projects, and services, with direct conversion to invoice line items.

#### 4.5.1 Time Tracking Features

- Start/stop timer with real-time clock display
- Manual time entry with date, hours, minutes, service, and notes
- Assign time entries to clients, projects, and services
- Mark entries as billable or non-billable
- Bulk time entry for entering multiple days at once
- Weekly timesheet view with daily breakdown
- Timer persistence across browser sessions (stored server-side)
- Round time entries to nearest configurable increment (5, 6, 10, 15, 30 min)
- Convert unbilled time entries directly to invoice line items
- Team member time tracking with manager approval workflow
- Time reports by team member, client, project, service, date range

#### 4.5.2 Services

Services represent types of work offered (e.g., Design, Development, Consulting). Each service has a name, hourly rate, and description. Services are assigned to projects and automatically converted to invoice line item tasks.

---

### 4.6 Projects & Collaboration

Project management features allow tracking budgets, assigning team members, logging time and expenses against specific projects, and monitoring profitability.

#### 4.6.1 Project Features

- Create projects with name, client assignment, and description
- Project types: flat-rate, hourly, or non-billable
- Budget tracking: set budget by hours or fixed amount with alerts at thresholds
- Assign team members and contractors to projects with service/rate overrides
- Project-scoped time tracking and expense logging
- Project profitability dashboard: budget vs. actual, revenue vs. costs
- Task management within projects (to-do lists with assignees and due dates)
- Project status: active, completed, archived
- Discussion threads for team and client collaboration

---

### 4.7 Client Management

Comprehensive client profiles with contact management, communication history, and a self-service client portal.

#### 4.7.1 Client Features

- Client profiles with name, organization, email, phone, address, and custom fields
- Multiple contacts per client with primary contact designation
- Client-specific currency and language preferences
- Late fee configuration per client (override global settings)
- Payment terms per client (override default)
- Client activity history: invoices, payments, estimates, expenses
- Account statements generation per client
- Client notes and internal-only comments
- Client tagging and grouping for organization
- Import clients from CSV

#### 4.7.2 Client Portal

- Login-free access via secure token links
- View and pay invoices online
- View estimates and proposals with accept/decline actions
- Comment on invoices and estimates for feedback
- Download invoice and estimate PDFs
- View payment history and account balance
- Branded portal matching organization identity

---

### 4.8 Estimates & Proposals

#### 4.8.1 Estimates

- Create estimates with same line-item structure as invoices
- Estimate statuses: Draft, Sent, Viewed, Accepted, Declined
- Client-facing accept/decline workflow via portal
- One-click conversion from accepted estimate to invoice
- Estimate numbering with configurable prefix
- Email delivery with customizable templates

#### 4.8.2 Proposals

- Rich-text proposals with cover page, scope sections, and pricing table
- Client e-signature for proposal acceptance
- Conversion from proposal to estimate or directly to invoice
- Proposal templates for common project types

---

### 4.9 Accounting & Bookkeeping

Full double-entry accounting system with chart of accounts, journal entries, and bank reconciliation matching FreshBooks' accounting capabilities.

#### 4.9.1 Chart of Accounts

- Default chart of accounts following standard accounting categories
- Account types: Asset, Liability, Equity, Revenue, Expense
- Sub-accounts for hierarchical organization
- Custom account creation and editing
- Account archival (no deletion to preserve journal integrity)

#### 4.9.2 Journal Entries

- Manual journal entries for adjustments and accruals
- Automatic journal entries generated by invoices, payments, and expenses
- Debit/credit validation ensuring balanced entries
- Reversing entries for period-end adjustments

#### 4.9.3 Bank Reconciliation

- Import bank statements (CSV, OFX, QFX)
- Auto-match imported transactions to recorded entries
- Manual matching for unmatched transactions
- Reconciliation reports with discrepancy highlighting
- Period-end reconciliation locking

#### 4.9.4 Accounts Payable

- Vendor management with contact information
- Bill creation and tracking (amount owed to vendors)
- Bill payment recording and matching to expenses
- Bill aging reports

---

### 4.10 Financial Reporting

Comprehensive reporting suite matching all FreshBooks report types with export capabilities.

#### 4.10.1 Available Reports

| Report | Description | Tier |
|---|---|---|
| Profit & Loss (Income Statement) | Revenue minus expenses for a period with category breakdown | All |
| Balance Sheet | Assets, liabilities, and equity at a point in time | Plus+ |
| General Ledger | All transactions grouped by account for a period | Plus+ |
| Tax Summary | Tax collected vs. tax paid with net amounts by tax type | All |
| Accounts Receivable Aging | Outstanding invoices grouped by age (30/60/90/90+) | All |
| Accounts Payable Aging | Outstanding bills grouped by age | Premium+ |
| Invoice Details | All invoices with line items, payments, and status | All |
| Expense Report | Expenses by category, vendor, client, and team member | All |
| Payments Collected | All payments received with method, date, and client | All |
| Time Entry Details | Time logged by team member, client, project, and service | Plus+ |
| Project Profitability | Revenue vs. costs per project | Premium+ |
| Cash Flow Statement | Cash inflows and outflows by category | Premium+ |
| Revenue by Client | Revenue breakdown per client for a period | Plus+ |

#### 4.10.2 Report Features

- Date range filtering for all reports (custom range, presets: this month, quarter, year, last year)
- Client, project, team member filters where applicable
- Export to PDF, CSV, and Excel
- Email reports directly to stakeholders
- Print-optimized layouts
- Comparison view (current period vs. prior period)

---

### 4.11 Team Management

#### 4.11.1 Team Roles

| Role | Capabilities |
|---|---|
| Owner | Full access to all features, billing, and organization settings |
| Admin | Full access except billing management and ownership transfer |
| Manager | Manage projects, clients, invoices; view team member time entries |
| Employee (Staff) | Track time, log expenses, view assigned projects and clients |
| Contractor | Track time against assigned projects, submit invoices to organization |
| Accountant | Read-only access to all financial data, reports, and journal entries |

#### 4.11.2 Team Features

- Invite team members via email with role assignment
- Per-user permissions overriding role defaults
- Contractor management with 1099 tracking
- Team member cost rates for profitability calculations
- Time entry approval workflow (manager reviews employee entries)
- Activity logging per team member

---

### 4.12 Payroll Integration

Payroll processing as an optional add-on module, matching FreshBooks' payroll feature set.

- Payroll run processing with configurable pay periods (weekly, bi-weekly, semi-monthly, monthly)
- Employee salary and hourly rate management
- Automatic federal and state tax calculation and withholding
- Direct deposit via ACH integration
- Pay stub generation and distribution
- Payroll tax filing automation (941, W-2, 1099)
- Overtime calculation rules per jurisdiction
- PTO/vacation tracking and accrual
- Integration with time tracking module for hourly employees
- Payroll journal entries auto-posted to general ledger

---

### 4.13 Mobile Application

Native-equivalent mobile experience via a responsive PWA and optional native apps.

- Progressive Web App with offline capability and push notifications
- Mobile receipt capture via camera with OCR processing
- Create and send invoices from mobile
- Time tracking with start/stop timer
- View dashboard and financial summaries
- Mileage tracking via GPS with automatic trip detection
- Push notifications for payments received, invoice views, and overdue items
- Optional native iOS/Android apps via React Native wrapper

---

### 4.14 Integrations & API

A comprehensive REST API and integration ecosystem replicating FreshBooks' connectivity.

#### 4.14.1 REST API

- Full CRUD API for all entities: clients, invoices, expenses, time entries, projects, payments, reports
- OAuth 2.0 authentication with PKCE for third-party apps
- API key authentication for server-to-server integrations
- JSON request and response format
- Pagination, filtering, sorting, and include (eager loading) parameters
- Rate limiting: 100 requests per minute per API key (configurable)
- Webhook callbacks for real-time event notifications
- OpenAPI 3.1 specification with auto-generated documentation
- SDK support: PHP, JavaScript/Node.js, Python

#### 4.14.2 API Endpoints

| Resource | Base URL | Methods |
|---|---|---|
| Clients | `/api/v1/clients` | GET, POST, PUT, DELETE |
| Contacts | `/api/v1/clients/{id}/contacts` | GET, POST, PUT, DELETE |
| Invoices | `/api/v1/invoices` | GET, POST, PUT, DELETE, ACTIONS |
| Invoice Profiles | `/api/v1/invoice-profiles` | GET, POST, PUT, DELETE |
| Payments | `/api/v1/payments` | GET, POST, PUT, DELETE |
| Expenses | `/api/v1/expenses` | GET, POST, PUT, DELETE |
| Expense Categories | `/api/v1/expense-categories` | GET, POST, PUT, DELETE |
| Time Entries | `/api/v1/time-entries` | GET, POST, PUT, DELETE |
| Projects | `/api/v1/projects` | GET, POST, PUT, DELETE |
| Services | `/api/v1/services` | GET, POST, PUT, DELETE |
| Estimates | `/api/v1/estimates` | GET, POST, PUT, DELETE |
| Taxes | `/api/v1/taxes` | GET, POST, PUT, DELETE |
| Reports | `/api/v1/reports/{type}` | GET |
| Webhooks | `/api/v1/webhooks` | GET, POST, PUT, DELETE |
| Users/Team | `/api/v1/team-members` | GET, POST, PUT, DELETE |
| Accounts (COA) | `/api/v1/accounts` | GET, POST, PUT |
| Journal Entries | `/api/v1/journal-entries` | GET, POST |

#### 4.14.3 Webhook Events

| Event | Trigger |
|---|---|
| `invoice.created` | New invoice created |
| `invoice.sent` | Invoice emailed or marked as sent |
| `invoice.viewed` | Client views invoice for the first time |
| `invoice.paid` | Invoice fully paid |
| `payment.created` | Payment recorded (online or manual) |
| `payment.failed` | Online payment attempt failed |
| `expense.created` | New expense recorded |
| `client.created` | New client added |
| `estimate.accepted` | Client accepts an estimate |
| `estimate.declined` | Client declines an estimate |
| `recurring.generated` | Recurring invoice profile generates new invoice |

#### 4.14.4 Third-Party Integrations

| Integration | Purpose | Method |
|---|---|---|
| Stripe | Online payment processing | Stripe PHP SDK + webhooks |
| PayPal | Alternative online payments | PayPal REST API |
| Plaid | Bank account connections | Plaid Link + API |
| Zapier | Workflow automation | Zapier app with triggers and actions |
| Gusto | Payroll processing | Gusto Partner API |
| HubSpot | CRM sync (clients and deals) | HubSpot API v3 |
| Google Workspace | Calendar, Contacts, Drive integration | Google APIs |
| Slack | Payment and invoice notifications | Slack incoming webhooks |
| Mailchimp | Client email marketing sync | Mailchimp API v3 |
| Square | POS payment import | Square API |

---

## 5. Database Schema

The database design uses a normalized relational schema with soft deletes, audit timestamps, and tenant scoping on all tables. Below are the primary tables and their key columns.

### 5.1 Core Tables

#### `organizations`

```
id, name, slug, owner_id, currency_code, timezone, logo_path, settings (JSON)
→ Has many: users, clients, invoices, expenses, projects
```

#### `users`

```
id, org_id, name, email, password, role, avatar, mfa_secret, last_login_at
→ Belongs to: organization
→ Has many: time_entries, expenses
```

#### `clients`

```
id, org_id, fname, lname, organization, email, phone, address (JSON),
currency_code, language, late_fee_type, late_fee_amount, payment_terms
→ Belongs to: organization
→ Has many: contacts, invoices, expenses, projects
```

#### `contacts`

```
id, client_id, fname, lname, email, phone, is_primary
→ Belongs to: client
```

#### `invoices`

```
id, org_id, client_id, invoice_number, status, create_date, due_date,
currency_code, discount_value, discount_type, tax_amount, subtotal, total,
amount_paid, amount_outstanding, notes, terms, template, po_number,
sent_at, viewed_at, paid_at
→ Belongs to: organization, client
→ Has many: invoice_lines, payments
```

#### `invoice_lines`

```
id, invoice_id, type (item|time|expense), description, quantity, unit_price,
tax_name, tax_percent, amount, expense_id, time_entry_id
→ Belongs to: invoice
```

#### `invoice_profiles`

```
id, org_id, client_id, frequency, next_issue_date, end_date,
occurrences_remaining, auto_send, template_data (JSON)
→ Belongs to: organization, client
```

#### `payments`

```
id, org_id, invoice_id, amount, currency_code, method,
gateway_transaction_id, date, notes, type (payment|refund|credit)
→ Belongs to: organization, invoice
```

#### `expenses`

```
id, org_id, client_id, project_id, category_id, vendor, amount, currency_code,
tax_name, tax_percent, tax_amount, date, notes, receipt_path, is_billable,
markup_percent, is_recurring, profile_id, bank_transaction_id
→ Belongs to: organization, client, project, category
```

#### `expense_categories`

```
id, org_id, name, parent_id, is_default
→ Belongs to: organization
→ Self-referencing parent
```

#### `time_entries`

```
id, org_id, user_id, client_id, project_id, service_id, duration_seconds,
date, notes, is_billable, is_billed, timer_started_at
→ Belongs to: organization, user, client, project, service
```

#### `projects`

```
id, org_id, client_id, name, description, type (hourly|flat_rate|non_billable),
budget_type, budget_amount, status
→ Belongs to: organization, client
→ Has many: time_entries, expenses, tasks
```

#### `services`

```
id, org_id, name, rate, description, is_active
→ Belongs to: organization
```

#### `estimates`

```
id, org_id, client_id, estimate_number, status, create_date, total,
accepted_at, declined_at, converted_invoice_id
→ Belongs to: organization, client
```

#### `accounts`

```
id, org_id, name, type (asset|liability|equity|revenue|expense),
account_number, parent_id, balance, is_active
→ Belongs to: organization
→ Self-referencing parent
```

#### `journal_entries`

```
id, org_id, date, description, reference, debit_account_id,
credit_account_id, amount, source_type, source_id
→ Belongs to: organization
→ Polymorphic source
```

#### `webhooks`

```
id, org_id, url, events (JSON), secret, is_active, last_triggered_at
→ Belongs to: organization
```

#### `tax_rates`

```
id, org_id, name, percent, is_compound, is_default
→ Belongs to: organization
```

### 5.2 Indexing Strategy

- Composite index on `(org_id, status)` for all entity tables
- Composite index on `(org_id, client_id)` for client-scoped queries
- Date range indexes on `(org_id, date/create_date)` for report queries
- Full-text index on invoice notes, expense descriptions via Meilisearch
- Unique index on `(org_id, invoice_number)` for invoice numbering

---

## 6. Security Requirements

| Req ID | Requirement | Priority |
|---|---|---|
| SEC-001 | All data in transit encrypted via TLS 1.3 | P0 |
| SEC-002 | Sensitive data at rest encrypted using AES-256 (database and file storage) | P0 |
| SEC-003 | Password hashing via Argon2id with configurable cost parameters | P0 |
| SEC-004 | OWASP Top 10 mitigation: SQL injection, XSS, CSRF, SSRF protection | P0 |
| SEC-005 | Rate limiting on authentication endpoints (5 attempts per minute) | P0 |
| SEC-006 | Two-factor authentication (TOTP) with backup recovery codes | P0 |
| SEC-007 | API authentication via OAuth 2.0 with short-lived access tokens (1 hour) and refresh tokens | P0 |
| SEC-008 | PCI-DSS compliance via Stripe Elements (no raw card data on server) | P0 |
| SEC-009 | Comprehensive audit logging for all financial transactions | P0 |
| SEC-010 | Role-based access control with principle of least privilege | P0 |
| SEC-011 | Content Security Policy headers on all responses | P1 |
| SEC-012 | Automated dependency vulnerability scanning in CI/CD pipeline | P1 |
| SEC-013 | Data retention and deletion policies compliant with GDPR and CCPA | P1 |
| SEC-014 | Session management with configurable timeout and concurrent session limits | P0 |

---

## 7. Performance Requirements

| Req ID | Metric | Target | Priority |
|---|---|---|---|
| PERF-001 | Page load time (TTFB) | < 200ms for authenticated pages | P0 |
| PERF-002 | API response time (95th percentile) | < 300ms for list endpoints, < 150ms for single resource | P0 |
| PERF-003 | Invoice PDF generation | < 2 seconds per invoice | P0 |
| PERF-004 | Dashboard load time | < 2 seconds with all widgets | P0 |
| PERF-005 | Report generation (P&L, Balance Sheet) | < 5 seconds for 1 year of data | P1 |
| PERF-006 | Concurrent users per instance | > 500 simultaneous users | P1 |
| PERF-007 | Database query optimization | No N+1 queries; all list queries use eager loading | P0 |
| PERF-008 | Static asset delivery | CDN-served with 1-year cache headers | P1 |
| PERF-009 | Search response time | < 100ms via Meilisearch for full-text queries | P1 |
| PERF-010 | Background job processing | < 30 second queue latency for email and webhook jobs | P0 |

---

## 8. Subscription Tier Architecture

The platform supports a tiered feature-gating system matching the FreshBooks pricing model. Feature flags control access per organization based on their subscription tier.

| Feature | Lite | Plus | Premium | Select |
|---|---|---|---|---|
| Monthly Price (Self-Hosted) | Free | Free | Free | Free |
| Monthly Price (Cloud) | $19 | $33 | $60 | Custom |
| Clients | 5 | 50 | Unlimited | Unlimited |
| Invoices | Unlimited | Unlimited | Unlimited | Unlimited |
| Expense Tracking | ✅ | ✅ | ✅ | ✅ |
| Time Tracking | ✅ | ✅ | ✅ | ✅ |
| Estimates | ✅ | ✅ | ✅ | ✅ |
| Proposals | ❌ | ✅ | ✅ | ✅ |
| Recurring Invoices | ❌ | ✅ | ✅ | ✅ |
| Automated Late Fees | ❌ | ✅ | ✅ | ✅ |
| Late Payment Reminders | ❌ | ✅ | ✅ | ✅ |
| Client Retainers | ❌ | ✅ | ✅ | ✅ |
| Bank Reconciliation | ❌ | ✅ | ✅ | ✅ |
| Accountant Access | ❌ | ✅ | ✅ | ✅ |
| Double-Entry Accounting | ❌ | ✅ | ✅ | ✅ |
| Accounts Payable | ❌ | ❌ | ✅ | ✅ |
| Project Profitability | ❌ | ❌ | ✅ | ✅ |
| Custom Email Templates | ❌ | ❌ | ✅ | ✅ |
| Remove Branding | ❌ | ❌ | ✅ | ✅ |
| Lower Transaction Fees | ❌ | ❌ | ❌ | ✅ |
| Dedicated Account Manager | ❌ | ❌ | ❌ | ✅ |
| Custom Onboarding | ❌ | ❌ | ❌ | ✅ |
| Data Migration Support | ❌ | ❌ | ❌ | ✅ |
| Team Members (Add-On) | $11/user | $11/user | $11/user | 2 included |
| Advanced Payments (Add-On) | $20/mo | $20/mo | $20/mo | Included |
| Payroll (Add-On) | $40 + $6/emp | $40 + $6/emp | $40 + $6/emp | $40 + $6/emp |

---

## 9. Deployment Architecture

### 9.1 Self-Hosted Deployment

- Docker Compose single-command deployment with Nginx, PHP-FPM, MySQL, Redis, Meilisearch
- Environment configuration via `.env` file with sensible defaults
- Automatic database migration on container startup
- Built-in health check endpoints for container orchestration
- SSL termination via Caddy or Nginx with Let's Encrypt auto-renewal
- Backup automation via `mysqldump` with S3/local storage

### 9.2 Cloud Deployment (SaaS Mode)

- Kubernetes deployment with horizontal pod autoscaling
- RDS MySQL with read replicas for reporting queries
- ElastiCache Redis cluster for session and queue management
- S3 for file storage with CloudFront CDN
- SES for transactional email delivery
- CloudWatch for monitoring and alerting
- Blue-green deployment strategy with zero-downtime releases

### 9.3 Minimum System Requirements (Self-Hosted)

| Resource | Minimum | Recommended |
|---|---|---|
| CPU | 2 vCPUs | 4 vCPUs |
| RAM | 4 GB | 8 GB |
| Storage | 20 GB SSD | 100 GB SSD |
| PHP | 8.5+ | 8.5+ |
| MySQL | 8.0+ | 8.0+ |
| Redis | 6.0+ | 7.x |
| OS | Ubuntu 22.04+ / Debian 12+ | Ubuntu 24.04 |

---

## 10. Testing Strategy

| Test Type | Coverage Target | Framework | Scope |
|---|---|---|---|
| Unit Tests | 90%+ on domain logic | Pest PHP | Models, services, value objects, enums |
| Feature Tests | 85%+ on HTTP layer | Pest PHP + Laravel | API endpoints, controllers, middleware |
| Integration Tests | 100% on payment flows | Pest PHP | Stripe, Plaid, email, S3 integrations |
| Browser Tests | Critical user journeys | Laravel Dusk | Invoice creation, payment flow, onboarding |
| API Contract Tests | 100% of endpoints | Spectator | OpenAPI spec compliance |
| Performance Tests | Key endpoints | k6 / Artillery | Load testing, response time benchmarks |
| Security Tests | OWASP Top 10 | PHPStan + SAST | Static analysis, dependency scanning |

---

## 11. Development Roadmap

| Phase | Timeline | Deliverables |
|---|---|---|
| **Phase 1: Foundation** | Weeks 1–6 | Project scaffolding, auth system, multi-tenancy, user/org management, database migrations, CI/CD pipeline, development environment |
| **Phase 2: Core Invoicing** | Weeks 7–12 | Client management, invoice CRUD, PDF generation, email delivery, invoice templates, recurring invoices, tax engine |
| **Phase 3: Payments & Expenses** | Weeks 13–18 | Stripe integration, payment recording, expense tracking, receipt upload, bank CSV import, expense categories |
| **Phase 4: Time & Projects** | Weeks 19–24 | Time tracking, timers, services, project management, budgets, billable time-to-invoice conversion |
| **Phase 5: Accounting & Reports** | Weeks 25–30 | Chart of accounts, journal entries, bank reconciliation, all financial reports, P&L, balance sheet |
| **Phase 6: Advanced Features** | Weeks 31–36 | Estimates/proposals, client portal, team management, role permissions, accounts payable, payroll integration |
| **Phase 7: API & Integrations** | Weeks 37–42 | REST API, OAuth provider, webhooks, Zapier app, Plaid bank connection, API documentation |
| **Phase 8: Polish & Launch** | Weeks 43–48 | Mobile PWA, performance optimization, security audit, load testing, documentation, beta launch |

---

## 12. Glossary

| Term | Definition |
|---|---|
| ACH | Automated Clearing House – electronic bank-to-bank payment network |
| CRUD | Create, Read, Update, Delete – basic data operations |
| DDD | Domain-Driven Design – software architecture pattern |
| HST | Harmonized Sales Tax (Canadian combined federal/provincial tax) |
| Multi-Tenancy | Architecture where a single instance serves multiple organizations |
| OAuth 2.0 | Authorization framework for API access delegation |
| OFX | Open Financial Exchange – bank statement format |
| PCI-DSS | Payment Card Industry Data Security Standard |
| PKCE | Proof Key for Code Exchange – OAuth security extension |
| PWA | Progressive Web App – web app with native-like capabilities |
| TOTP | Time-based One-Time Password – 2FA standard |
| TTFB | Time To First Byte – server response speed metric |

---

## 13. Appendix

### 13.1 FreshBooks Feature Parity Checklist

| FreshBooks Feature | OpenBooks Module | Status |
|---|---|---|
| Invoicing with customizable templates | Invoicing | 🔲 Planned |
| Online payment acceptance (Stripe) | Payments | 🔲 Planned |
| Recurring invoices | Invoicing (Profiles) | 🔲 Planned |
| Automated payment reminders | Notifications | 🔲 Planned |
| Expense tracking with receipt capture | Expenses | 🔲 Planned |
| Bank account import | Expenses (Bank Connection) | 🔲 Planned |
| Time tracking with timers | Time Tracking | 🔲 Planned |
| Project management | Projects | 🔲 Planned |
| Double-entry accounting | Accounting | 🔲 Planned |
| Bank reconciliation | Accounting | 🔲 Planned |
| Financial reports (P&L, Balance Sheet, etc.) | Reporting | 🔲 Planned |
| Client portal | Client Management | 🔲 Planned |
| Estimates and proposals | Estimates | 🔲 Planned |
| Team/contractor management | Team | 🔲 Planned |
| Payroll (add-on) | Payroll | 🔲 Planned |
| Mileage tracking | Mobile | 🔲 Planned |
| Mobile app (receipt capture, invoicing) | Mobile (PWA) | 🔲 Planned |
| REST API with OAuth 2.0 | Integrations | 🔲 Planned |
| Webhooks | Integrations | 🔲 Planned |
| Multi-currency support | Core (all modules) | 🔲 Planned |
| Multi-language support | Core (i18n) | 🔲 Planned |
| Client retainers | Invoicing | 🔲 Planned |
| Accounts payable | Accounting | 🔲 Planned |
| Custom email templates | Notifications | 🔲 Planned |

### 13.2 Document Revision History

| Version | Date | Author | Changes |
|---|---|---|---|
| 1.0 | 2026-02-05 | Frankie | Initial PRD creation with full FreshBooks feature parity specification |

---

*End of Document*
