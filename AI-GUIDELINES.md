# AI Development Guidelines - Bukupasar

**Purpose:** Panduan untuk AI assistant agar tidak merusak code existing saat melakukan perubahan.

---

## 🚫 CRITICAL FILES - DO NOT MODIFY

### Backend Critical (Laravel)

#### Database & Models:
- `bukupasar-backend/app/Models/*.php` - Eloquent models dengan relationships
- `bukupasar-backend/database/migrations/*.php` - Database schema
- `bukupasar-backend/database/seeders/*.php` - Initial data
- `bukupasar-backend/.env` - Environment configuration

#### API & Routes:
- `bukupasar-backend/routes/api.php` - API endpoints
- `bukupasar-backend/app/Http/Controllers/Api/*.php` - API logic
- `bukupasar-backend/app/Http/Middleware/*.php` - Market scoping, auth
- `bukupasar-backend/bootstrap/app.php` - Application bootstrap

#### Authentication:
- `bukupasar-backend/config/sanctum.php` - Token auth config
- `bukupasar-backend/config/cors.php` - CORS configuration

---

### Frontend Critical (Next.js)

#### Core Infrastructure:
- `bukupasar-frontend/contexts/AuthContext.tsx` - Authentication state & logic
- `bukupasar-frontend/lib/api.ts` - Axios instance & interceptors
- `bukupasar-frontend/.env.local` - Environment variables
- `bukupasar-frontend/app/layout.tsx` - Root layout
- `bukupasar-frontend/app/(authenticated)/layout.tsx` - Auth layout

#### Custom Hooks (Data Fetching):
- `bukupasar-frontend/hooks/useCategories.ts` - Categories API
- `bukupasar-frontend/hooks/useTenants.ts` - Tenants API
- `bukupasar-frontend/hooks/useSearchTenants.ts` - Tenant search
- `bukupasar-frontend/hooks/useTransactions.ts` - Transactions API
- `bukupasar-frontend/hooks/useReports.ts` - Reports API

---

## ⚠️ MODIFY WITH CAUTION

### Can modify BUT require review:

#### Filament Resources:
- `bukupasar-backend/app/Filament/Resources/*.php`
- **Caution:** Jangan ubah query scopes, authorization, relationships

#### API Controllers:
- `bukupasar-backend/app/Http/Controllers/Api/*.php`
- **Caution:** Jangan ubah validation rules, market scoping, business logic

#### Frontend Components:
- `bukupasar-frontend/components/ui/*.tsx` (shadcn components)
- **Caution:** Preserve TypeScript types, props interface

---

## ✅ SAFE TO MODIFY (UI Changes)

### Frontend Pages:
- `bukupasar-frontend/app/(authenticated)/dashboard/page.tsx`
- `bukupasar-frontend/app/(authenticated)/pemasukan/tambah/page.tsx`
- `bukupasar-frontend/app/(authenticated)/pengeluaran/tambah/page.tsx`
- `bukupasar-frontend/app/(authenticated)/sewa/page.tsx`
- `bukupasar-frontend/app/(authenticated)/laporan/*/page.tsx`

### Allowed Changes:
- ✅ Tailwind classes (colors, spacing, typography)
- ✅ Layout structure (grid, flex, positioning)
- ✅ Button styles (size, color, border radius)
- ✅ Card designs (shadow, border, padding)
- ✅ Text sizes & font weights
- ✅ Icons (lucide-react)
- ✅ Responsive breakpoints

### NOT Allowed:
- ❌ React Query hooks (useQuery, useMutation)
- ❌ Form validation logic (Zod schemas)
- ❌ API endpoints or parameters
- ❌ State management (useState, useContext)
- ❌ Event handlers logic
- ❌ TypeScript types

---

## 📋 AI Request Template

When requesting changes, use this format:

```markdown
=== BUKUPASAR CHANGE REQUEST ===

Context: [Backend/Frontend] - [brief description]

Change Scope: [UI Only / Logic Change / New Feature]

Files to Modify:
1. [exact file path]
2. [exact file path]

Files to PROTECT (do not touch):
- [list critical files from above]
- All files not listed in "Files to Modify"

Specific Changes:
- [Detail apa yang diubah]
- [Contoh: ganti warna, tambah spacing, dll]

Requirements:
1. Show DIFF before applying
2. Explain impact of changes
3. Preserve existing functionality
4. No logic changes (if UI only)
5. Maintain responsive design
6. Maintain UX guidelines (large text for elderly users)

STOP and WAIT for approval before modifying files.
```

---

## 🧪 Testing Protocol

After AI makes changes, run this checklist:

### Frontend Changes:
```bash
cd bukupasar-frontend

# 1. TypeScript check
npx tsc --noEmit
# Expected: No errors

# 2. Build check
npm run build
# Expected: Build completed successfully

# 3. Manual test
npm run dev
# Test: login → dashboard → input forms → reports

# 4. Responsive test
# Open DevTools → Responsive mode → 375px width
```

### Backend Changes:
```bash
cd bukupasar-backend

# 1. Syntax check
php -l app/path/to/changed/file.php
# Expected: No syntax errors

# 2. Run tests
php artisan test
# Expected: All tests passing

# 3. Test API endpoint
# Use Postman or curl to test changed endpoint
```

---

## 🔄 Git Workflow

**ALWAYS before AI changes:**
```bash
# 1. Commit current working state
git add .
git commit -m "feat: working state before [change description]"

# 2. Create new branch
git checkout -b [feature-branch-name]

# 3. Optional: Create backup tag
git tag backup-[timestamp]
```

**AFTER AI changes:**
```bash
# If OK:
git add .
git commit -m "[type]: [description]

- [detail change 1]
- [detail change 2]"

git checkout main
git merge [feature-branch-name]

# If BROKEN:
git reset --hard HEAD
# Or: git checkout backup-[timestamp]
```

---

## 🎯 Common UI Change Scenarios

### Scenario 1: Change Color Theme

**Safe Approach:**
```
Task: Change primary color dari sky (blue) ke emerald (green)

Files to change:
- app/(authenticated)/dashboard/page.tsx
- app/(authenticated)/pemasukan/tambah/page.tsx
- app/(authenticated)/pengeluaran/tambah/page.tsx

Change only:
- bg-sky-500 → bg-emerald-500
- bg-sky-600 → bg-emerald-600
- hover:bg-sky-600 → hover:bg-emerald-600

Do NOT change:
- Any logic or functions
- Event handlers
- API calls
- Form validation
```

---

### Scenario 2: Adjust Spacing

**Safe Approach:**
```
Task: Increase spacing between dashboard cards

File to change:
- app/(authenticated)/dashboard/page.tsx (line 45-60)

Change only:
- gap-4 → gap-6
- p-4 → p-6

Preserve:
- Grid columns (grid-cols-1 md:grid-cols-3)
- Responsive breakpoints
- Card content and logic
```

---

### Scenario 3: Add New Component

**Safe Approach:**
```
Task: Add loading skeleton to dashboard

Files to modify:
1. Create: components/ui/skeleton.tsx (new file)
2. Modify: app/(authenticated)/dashboard/page.tsx

Changes:
1. Create Skeleton component (using shadcn pattern)
2. Wrap existing dashboard cards with conditional render:
   {isLoading ? <Skeleton /> : <DashboardCard />}

Do NOT change:
- useQuery configuration
- API endpoints
- Existing card logic
```

---

## ⚠️ Warning Signs

**STOP immediately if AI suggests:**

❌ "Let me refactor your authentication logic..."
❌ "I'll update your API endpoints to be more RESTful..."
❌ "Let me migrate from TanStack Query to SWR..."
❌ "I'll change your database schema to optimize..."
❌ "Let me update all your dependencies..."

**These are OUT OF SCOPE for safe UI changes!**

---

## 📚 UX Guidelines (Must Preserve)

### For Elderly Users:

1. **Text Size:** Minimum 18px (`text-lg`)
   - Headings: 24-30px (`text-2xl` to `text-3xl`)
   
2. **Touch Targets:** Minimum 48px (`h-12`)
   - Buttons: `h-12` or `h-14`
   - Navbar: `h-20`
   
3. **Contrast:** Minimum 7:1 ratio (AAA standard)
   - Dark text on light background
   - Current: slate-800 on white (12.6:1) ✅
   
4. **Spacing:** Generous whitespace
   - Button spacing: `gap-4` minimum
   - Card padding: `p-6` minimum
   
5. **Color Coding:**
   - Pemasukan: Green shades
   - Pengeluaran: Red shades
   - Neutral: Slate/Gray

**AI must preserve these in all UI changes!**

---

## 📞 When in Doubt

**If unsure whether a change is safe:**

1. ✅ Ask first: "Is this file safe to modify per AI-GUIDELINES.md?"
2. ✅ Show diff: "Let me show you what will change"
3. ✅ Explain impact: "This might affect X, Y, Z"
4. ✅ Wait for approval: "Should I proceed?"

**Better to ask than to break!**

---

## 🎓 Philosophy

> "Treat working code like a precious artifact. Changes should be surgical, not destructive."

**Principles:**
1. **Minimal Change:** Only change what's necessary
2. **Incremental:** One small change at a time
3. **Reversible:** Always have a rollback plan
4. **Tested:** Every change must be tested
5. **Documented:** Commit messages explain WHY

---

**Last Updated:** 2025-01-15  
**Version:** 1.0  
**Status:** Active - Enforce Strictly
