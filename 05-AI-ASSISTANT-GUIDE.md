# 05-AI-ASSISTANT-GUIDE.md
# Bukupasar — AI Assistant Workflow & Prompt Library

**Panduan lengkap bekerja dengan AI untuk implementasi project.**

---

## 📋 Table of Contents

1. [How to Use This Guide](#how-to-use-this-guide)
2. [AI Workflow Strategy](#ai-workflow-strategy)
3. [Prompt Templates Library](#prompt-templates-library)
4. [Weekly Implementation Plan](#weekly-implementation-plan)
5. [Common Pitfalls & Solutions](#common-pitfalls--solutions)

---

## 1. How to Use This Guide

### Purpose
Guide ini adalah **daily companion** Anda. Setiap kali akan coding, ikuti workflow di sini dan gunakan prompt templates yang sudah disediakan.

### Quick Start

**Every AI Session, Start With:**
```
"Saya sedang develop project Bukupasar - aplikasi keuangan pasar multi-tenant.

Please load these files untuk context:
1. 01-PROJECT-SPEC.md - Architecture & database
2. 02-BACKEND-GUIDE.md - Laravel implementation
3. 05-AI-ASSISTANT-GUIDE.md (this file) - Workflow

Current progress: lihat TO-DO-LIST.md → [Week X]

Task hari ini: [specific task]

Ready?"
```

**Then:** Copy-paste prompt template sesuai task dari Section 3 di bawah.

---

## 2. AI Workflow Strategy

### Session Management

**Rule:** 1 Session = 1 Module/Task

**Good Example:**
- Session 1: "Generate migrations untuk tabel markets, users, tenants"
- Session 2: "Create Models dengan relationships"
- Session 3: "Build Transaction API endpoints"

**Bad Example:**
- Session 1: "Buatkan semua dari setup sampai deployment" ❌ (too broad)

### Context Loading Strategy

**Minimal Context (Fast):**
- File to load: 05-AI-ASSISTANT-GUIDE.md + TO-DO-LIST.md
- Use case: Simple, repetitive tasks

**Medium Context (Standard):**
- Files: 01-PROJECT-SPEC.md + 02 atau 03 (depending backend/frontend) + 05 + TO-DO-LIST
- Use case: Most development tasks

**Full Context (Deep Work):**
- Files: All 5 core docs
- Use case: Complex features, architecture decisions

### Error Handling with AI

**When Error Occurs:**

1. **Copy full error message** (jangan paraphrase)
2. **Share context:**
   - What you were doing
   - Code that caused error
   - Relevant file paths
3. **Ask AI for solution:**
   ```
   "Error terjadi saat [task]:
   
   [Full error message]
   
   File: [path/to/file.php]
   Code yang error: [paste code]
   
   Tolong analyze dan berikan solusi step-by-step."
   ```

---

## 3. Prompt Templates Library

### 🗄️ Database & Migrations

#### Template: Generate Migration

```
Context: Bukupasar project, creating database schema
Task: Generate Laravel migration for [table_name]

Reference: 01-PROJECT-SPEC.md section "Complete DDL" → [table_name]

Requirements:
- Laravel 11 syntax
- Include all columns per spec
- Add foreign keys with proper constraints (ON DELETE RESTRICT)
- Add indexes per spec: market_id, composite indexes
- Use proper data types (BIGINT for money, ENUM for jenis)

Output: Complete migration file with up() and down() methods.
```

**Example Usage:**
```
Context: Bukupasar project, creating database schema
Task: Generate Laravel migration for transactions table

Reference: 01-PROJECT-SPEC.md section "Complete DDL" → transactions

Requirements:
- Laravel 11 syntax
- Include all columns per spec
- Add foreign keys with proper constraints (ON DELETE RESTRICT)
- Add indexes per spec: market_id, composite indexes
- Use proper data types (BIGINT for money, ENUM for jenis)

Output: Complete migration file with up() and down() methods.
```

---

#### Template: Generate Seeder

```
Context: Bukupasar project, seeding initial data
Task: Generate seeder for [entity]

Data to seed:
- [list initial data needed]

Requirements:
- Check if data exists before seeding (avoid duplicates)
- Use proper relationships
- Seed data per market_id if applicable

Output: Complete seeder class.
```

---

### 🎨 Models & Relationships

#### Template: Generate Model

```
Context: Bukupasar project, creating Eloquent Models
Task: Generate Model for [ModelName]

Reference: 
- 01-PROJECT-SPEC.md section "Database Design" → ERD
- 02-BACKEND-GUIDE.md section "Eloquent Models" → [ModelName]

Requirements:
- Fillable fields from DDL
- Relationships: [list hasMany, belongsTo, etc.]
- Scopes: forMarket($marketId), and any specific scopes
- Casts: proper type casting (date, boolean, integer)
- Helper methods if specified

Output: Complete Model class with all relationships and methods.
```

**Example:**
```
Context: Bukupasar project, creating Eloquent Models
Task: Generate Model for Transaction

Reference: 
- 01-PROJECT-SPEC.md section "Database Design" → ERD
- 02-BACKEND-GUIDE.md section "Eloquent Models" → Transaction

Requirements:
- Fillable fields: market_id, tanggal, jenis, subkategori, jumlah, tenant_id, created_by, catatan
- Relationships: belongsTo Market, belongsTo Tenant, belongsTo User (creator)
- Scopes: forMarket, jenis, pemasukan, pengeluaran, byDate, dateRange, subkategori
- Casts: tanggal as date, jumlah as integer
- Helper methods: isPemasukan(), isPengeluaran(), canBeEditedBy(User), getFormattedJumlahAttribute()

Output: Complete Model class with all relationships and methods.
```

---

### 🔌 API Endpoints

#### Template: Generate API Controller

```
Context: Bukupasar project, building REST API
Task: Generate Controller for [Resource]

Reference:
- 01-PROJECT-SPEC.md section "RBAC & Access Control"
- 02-BACKEND-GUIDE.md section "API Endpoints" → [Resource]Controller

Endpoints needed:
- index: GET /api/[resource] with pagination, filters
- store: POST /api/[resource] with validation
- show: GET /api/[resource]/{id}
- update: PUT/PATCH /api/[resource]/{id}
- destroy: DELETE /api/[resource]/{id}

Requirements:
- Market scoping via $request->user()->market_id
- Authorization checks per RBAC spec
- Input validation with clear error messages
- Consistent JSON response format
- Load relationships with ->with([])

Output: Complete Controller class with all methods.
```

---

#### Template: Generate API Route

```
Context: Bukupasar project, API routing
Task: Add routes for [Resource] in routes/api.php

Requirements:
- Wrap in auth:sanctum middleware
- Use apiResource for REST endpoints
- Add custom routes if needed

Output: Route definitions to add to routes/api.php.
```

---

### 🎛️ Filament Admin Panel

#### Template: Generate Filament Resource

```
Context: Bukupasar project, creating Filament admin panel
Task: Generate Filament Resource for [ModelName]

Reference:
- 02-BACKEND-GUIDE.md section "Filament Admin Panel"
- 01-PROJECT-SPEC.md section "RBAC" for access control

Form Fields needed:
- [list fields with input types]

Table Columns needed:
- [list columns to display]

Filters:
- [list filters needed]

Requirements:
- Apply market scoping in query
- Add authorization checks: canViewAny(), canCreate(), etc. per RBAC
- Use proper field types (TextInput, Select, DatePicker, Textarea)
- Add validation rules
- Format display values (currency, dates)

Output: Complete Filament Resource class.
```

**Example:**
```
Context: Bukupasar project, creating Filament admin panel
Task: Generate Filament Resource for Transaction

Reference:
- 02-BACKEND-GUIDE.md section "Filament Admin Panel"
- 01-PROJECT-SPEC.md section "RBAC" for access control

Form Fields needed:
- tanggal (DatePicker)
- jenis (Select: pemasukan/pengeluaran)
- subkategori (Select from categories)
- jumlah (TextInput number)
- tenant_id (Select nullable)
- catatan (Textarea)

Table Columns needed:
- tanggal (sortable)
- jenis (badge colored)
- subkategori
- jumlah (formatted currency)
- tenant.nama (if exists)
- creator.name

Filters:
- By jenis
- By date range
- By subkategori

Requirements:
- Apply market scoping in query: ->where('market_id', auth()->user()->market_id)
- Authorization: admin_pusat and admin_pasar can access
- Format jumlah as Rupiah
- Add search by subkategori

Output: Complete Filament Resource class.
```

---

### ⚛️ Next.js Frontend

#### Template: Generate Next.js Page

```
Context: Bukupasar project, building Next.js SPA
Task: Create page component for [PageName]

Reference:
- 03-FRONTEND-GUIDE.md section "Pages Specification" → [PageName]
- 01-PROJECT-SPEC.md for business rules

Requirements:
- Use App Router (Next.js 14)
- Use shadcn/ui components
- Implement with TanStack Query for data fetching
- Responsive mobile-first design
- Large fonts and touch targets (UX for elderly)
- API integration via /lib/api.ts
- Loading and error states
- Proper TypeScript types

Output: Complete page component (.tsx file).
```

---

#### Template: Generate React Component

```
Context: Bukupasar project, building reusable component
Task: Create [ComponentName] component

Requirements:
- Use TypeScript with proper types
- Use shadcn/ui base components
- Props: [list props needed]
- State management if needed (useState, useReducer)
- Event handlers: [list events]
- Responsive design
- Accessibility (ARIA labels where needed)

Output: Complete component file.
```

---

#### Template: Generate Custom Hook

```
Context: Bukupasar project, creating data fetching hook
Task: Create useHook for [feature]

Requirements:
- Use TanStack Query (useQuery or useMutation)
- API endpoint: [endpoint path]
- Return data, isLoading, error, and mutation functions
- Handle errors with toast notifications
- Proper TypeScript types

Output: Complete custom hook file.
```

---

### 🐛 Debugging & Fixing

#### Template: Debug Error

```
Context: Bukupasar project, debugging issue
Problem: [Describe what's not working]

Error message:
[Paste full error message here]

Related code:
File: [path/to/file]
[Paste relevant code snippet]

What I tried:
- [list what you've tried]

Expected behavior:
[What should happen]

Request: Please analyze and provide step-by-step solution.
```

---

### 🧪 Testing

#### Template: Generate Test

```
Context: Bukupasar project, writing tests
Task: Create test for [feature/endpoint]

Reference: 02-BACKEND-GUIDE.md section "Testing"

Test scenarios:
- [list test cases needed]

Requirements:
- Use PHPUnit (Laravel)
- Test with proper user roles per RBAC
- Use factories for test data
- Assert expected responses
- Test validation rules

Output: Complete test class.
```

---

## 4. Weekly Implementation Plan

### Week 1-2: Foundation (Database & Models)

**Day 1-2: Setup Projects**
- Task: Create Laravel and Next.js projects
- AI Needed: Minimal (follow 02 and 03 guides)
- Verify: Both projects running locally

**Day 3-5: Migrations**
- Use: "Generate Migration" template
- Generate: markets, users, tenants, categories, transactions, payments, settings
- Run: `php artisan migrate`
- Verify: All tables created with proper indexes

**Day 6-8: Models**
- Use: "Generate Model" template
- Create: Market, Tenant, Category, Transaction, Payment, Setting
- Test: Relationships in `php artisan tinker`
- Verify: All relationships working

**Day 9-10: Seeders**
- Use: "Generate Seeder" template
- Seed: Initial market, admin user, categories
- Test: Login to Filament with seeded admin

---

### Week 3-4: Backend API

**Day 11-13: Auth API**
- Controllers: AuthController
- Endpoints: /login, /logout, /user
- Test: With Postman
- Verify: Token generation working

**Day 14-16: Core APIs**
- Use: "Generate API Controller" template
- Create: CategoryController, TenantController
- Test: CRUD operations via Postman
- Verify: Market scoping applied

**Day 17-20: Transaction APIs**
- Controllers: TransactionController, PaymentController
- Validation: Business rules implementation
- Test: Create, edit, delete with different roles
- Verify: Edit window rules working

---

### Week 5-6: Filament Admin

**Day 21-23: Basic Resources**
- Use: "Generate Filament Resource" template
- Create: MarketResource, TenantResource, CategoryResource
- Test: CRUD via Filament UI
- Verify: Access control per role

**Day 24-27: Transaction Management**
- Resources: TransactionResource, PaymentResource
- Dashboard: Widgets for overview
- Test: Input via admin panel
- Verify: Calculations correct

**Day 28-30: Reports & Settings**
- Reports: Daily, monthly views
- Settings: Configuration page
- Export: Basic PDF/Excel (optional)

---

### Week 7-8: Frontend SPA

**Day 31-33: Authentication**
- Use: "Generate Next.js Page" template
- Pages: Login
- Context: AuthContext
- Test: Login flow
- Verify: Token storage and API connection

**Day 34-37: Input Forms**
- Pages: Pemasukan/tambah, Pengeluaran/tambah
- Components: TransactionForm, wizard steps
- Test: Complete input flow
- Verify: Validation and API submission

**Day 38-42: Reports & Dashboard**
- Pages: Dashboard, Laporan harian, Laporan ringkasan
- Components: Tables, cards, charts (optional)
- Test: Data display
- Verify: Filters and date ranges

---

### Week 9-10: Integration & Testing

**Day 43-45: End-to-End Testing**
- Test: Complete user flows for all roles
- Test: Mobile responsiveness
- Test: Error handling
- Fix: Any bugs found

**Day 46-48: Performance & Security**
- Optimize: Database queries (N+1 check)
- Security: CSRF, CORS, input validation
- Test: Load time, concurrent users (basic)

**Day 49-50: Pre-Deployment Prep**
- Documentation: Update TO-DO-LIST
- Checklist: Deployment prerequisites
- Prepare: Environment variables for production

---

### Week 11-12: Deployment

**Day 51-54: Backend Deployment**
- Follow: 04-DEPLOYMENT-OPS.md → aaPanel section
- Deploy: Laravel to VPS
- Configure: Database, Nginx, SSL
- Test: Production API endpoints

**Day 55-58: Frontend Deployment**
- Deploy: Next.js with PM2
- Configure: Reverse proxy
- Test: Production SPA

**Day 59-60: Post-Deployment**
- Setup: Backups, monitoring
- Test: Complete flows in production
- Document: Credentials and procedures
- Handover: User training (optional)

---

## 5. Common Pitfalls & Solutions

### Pitfall 1: Forgetting Market Scoping

**Problem:** Queries return data from other markets

**Solution:**
Always add market scope:
```php
// In Controller
$marketId = $request->user()->market_id;
$transactions = Transaction::where('market_id', $marketId)->get();

// Or use scope
$transactions = Transaction::forMarket($marketId)->get();
```

**AI Prompt When Fixing:**
```
"I'm getting data from wrong market. Please review this code and add proper market_id scoping:

[paste code]

Reference: 01-PROJECT-SPEC.md section "Multi-Tenant Strategy""
```

---

### Pitfall 2: Validation Not Working

**Problem:** Invalid data being saved

**Solution:** Always validate in Controller:
```php
$validated = $request->validate([
    'tanggal' => 'required|date',
    'jumlah' => 'required|integer|min:1',
    // ...
]);
```

**AI Prompt:**
```
"Validation not catching errors. Help me add proper validation for [endpoint]:

Current code: [paste]

Expected validation rules from 01-PROJECT-SPEC.md section "Business Rules""
```

---

### Pitfall 3: Foreign Key Constraints Failing

**Problem:** Cannot insert due to FK violation

**Solution:** Ensure parent records exist and IDs match market

**AI Prompt:**
```
"Getting foreign key constraint error:

[paste error]

Migration code: [paste migration]

Help me fix the foreign key relationships."
```

---

### Pitfall 4: CORS Error in Frontend

**Problem:** API calls blocked by CORS

**Solution:** Configure Laravel CORS in `config/cors.php`

**AI Prompt:**
```
"Getting CORS error when calling API from Next.js:

Error: [paste]

API URL: [url]
Frontend URL: [url]

Help me configure CORS in Laravel."
```

---

### Pitfall 5: Token Not Persisting

**Problem:** User logged out on refresh

**Solution:** Save token to localStorage, not memory

**AI Prompt:**
```
"Token not persisting after page refresh in Next.js.

Current auth code: [paste]

Reference: 03-FRONTEND-GUIDE.md section "Authentication Flow"

Help me fix token persistence."
```

---

### Pitfall 6: PM2 Process Crashes

**Problem:** Next.js app stops after deployment

**Solution:** Check logs, fix errors, restart

**AI Prompt:**
```
"PM2 process keeps crashing:

PM2 logs: [paste]

Help me diagnose and fix."
```

---

## 📝 Daily Workflow Checklist

**Start of Day:**
- [ ] Review TO-DO-LIST.md untuk task hari ini
- [ ] Start AI session dengan context loading
- [ ] Clarify task dengan AI before coding

**During Development:**
- [ ] Use prompt templates dari guide ini
- [ ] Test setiap feature setelah implement
- [ ] Commit ke Git after each working feature
- [ ] Update TO-DO-LIST.md dengan progress

**End of Day:**
- [ ] Run all tests untuk ensure nothing broke
- [ ] Update TO-DO-LIST dengan:
  - [x] Tasks completed
  - Blockers encountered
  - Plan untuk besok
- [ ] Push changes ke Git repository

---

## 🎯 Success Metrics

**You're On Track If:**
- ✅ Following the weekly plan timeline (±2 days variance okay)
- ✅ All tests passing before moving to next week
- ✅ Able to use AI prompts with < 2 iterations to get working code
- ✅ No critical bugs accumulating (fix as you go)
- ✅ Code committed to Git regularly

**Warning Signs:**
- ⚠️ Stuck on same task for > 3 days
- ⚠️ More than 5 unresolved bugs
- ⚠️ No Git commits for 2+ days
- ⚠️ Skipping testing phase

**When Behind Schedule:**
1. Re-evaluate scope with simplified features
2. Focus on MVP only (defer nice-to-haves)
3. Get help: detailed AI prompts or community

---

## 💡 Pro Tips

**Tip 1: Batch Similar Tasks**
Generate all migrations at once, then all models, then all controllers. More efficient than jumping around.

**Tip 2: Test in Tinker Frequently**
```bash
php artisan tinker

# Test relationships
$market = Market::first();
$market->users;

# Test scopes
Transaction::forMarket(1)->pemasukan()->get();
```

**Tip 3: Use Postman Collections**
Save all API endpoints in Postman collection. Easy to re-test after changes.

**Tip 4: Keep AI Context Focused**
Don't load all 5 docs for simple tasks. Load relevant docs only to save tokens and get faster responses.

**Tip 5: Git Commit Messages Matter**
Good: "feat: add transaction create API with validation"
Bad: "update code"

Helps you (and AI) understand history later.

---

**Document Status:** ✅ Complete | **Last Updated:** 2025-01-15

---

## 📚 Quick Reference

| Task | Use Template | Reference Docs |
|------|-------------|----------------|
| Generate Migration | "Generate Migration" | 01, 02 |
| Create Model | "Generate Model" | 01, 02 |
| Build API | "Generate API Controller" | 01, 02 |
| Filament Resource | "Generate Filament Resource" | 01, 02 |
| Next.js Page | "Generate Next.js Page" | 01, 03 |
| Fix Bug | "Debug Error" | Relevant doc |
| Deploy | N/A (follow guide) | 04 |

Happy Coding! 🚀
