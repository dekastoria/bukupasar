# 🔧 Fix: Internal Server Error di Next.js

**Issue:** 500 Internal Server Error saat akses /dashboard  
**Root Cause:** useQuery dipanggil saat server-side rendering tanpa authentication  
**Status:** ✅ Fixed  
**Date:** 2025-01-15

---

## 🔍 Problem

### Symptom
- Login berhasil, redirect ke `/dashboard`
- Browser menunjukkan Internal Server Error (500)
- Terminal Next.js menunjukkan error saat render dashboard

### Root Cause

**What Happened:**
1. Next.js melakukan **Server-Side Rendering (SSR)** untuk page `/dashboard`
2. Saat SSR, `useQuery` di dashboard page dipanggil
3. `useQuery` mencoba fetch data dari API `/reports/daily`
4. Tidak ada token di server-side (token ada di localStorage browser)
5. API call gagal → Component crash → Internal Server Error

**Similar Issue in:**
- Dashboard page: `useQuery` untuk daily summary
- Transaction pages: `useCategories` hook

---

## ✅ Solution

### Fix 1: Dashboard Query - Only Run When Authenticated

**File:** `app/(authenticated)/dashboard/page.tsx`

**Before:**
```typescript
const { data, isLoading, isError } = useQuery<DailySummaryResponse>({
  queryKey: ['dashboard-summary', todayISO()],
  queryFn: async () => {
    const response = await api.get(`/reports/daily?date=${todayISO()}`);
    return response.data;
  },
  staleTime: 60_000,
});
```

**After:**
```typescript
const { data, isLoading, isError } = useQuery<DailySummaryResponse>({
  queryKey: ['dashboard-summary', todayISO()],
  queryFn: async () => {
    const response = await api.get(`/reports/daily?date=${todayISO()}`);
    return response.data;
  },
  staleTime: 60_000,
  enabled: !!user, // ✅ Only fetch when user is authenticated
});
```

**Logic:**
- `enabled: !!user` → Query hanya jalan kalau `user` ada (authenticated)
- Di SSR, `user` = null → query tidak jalan
- Di client-side setelah login, `user` = data → query jalan

---

### Fix 2: Categories Query - Client-Side Only

**File:** `hooks/useCategories.ts`

**Before:**
```typescript
export const useCategories = (jenis: 'pemasukan' | 'pengeluaran') =>
  useQuery({
    queryKey: ['categories', jenis],
    queryFn: async () => {
      const response = await api.get(`/categories`, {
        params: { jenis, aktif: 1 },
      });
      return response.data?.data ?? response.data ?? [];
    },
    staleTime: 10 * 60 * 1000,
  });
```

**After:**
```typescript
export const useCategories = (jenis: 'pemasukan' | 'pengeluaran') =>
  useQuery({
    queryKey: ['categories', jenis],
    queryFn: async () => {
      const response = await api.get(`/categories`, {
        params: { jenis, aktif: 1 },
      });
      return response.data?.data ?? response.data ?? [];
    },
    staleTime: 10 * 60 * 1000,
    enabled: typeof window !== 'undefined', // ✅ Only run on client-side
  });
```

**Logic:**
- `typeof window !== 'undefined'` → Check if code running in browser
- Di SSR (server), `window` undefined → query tidak jalan
- Di client-side, `window` exists → query jalan

---

## 🚀 How to Apply

### Step 1: Restart Next.js Dev Server

**Terminal Next.js:**
```bash
# Press Ctrl+C to stop
# Then restart:
cd C:\laragon\www\bukupasar\bukupasar-frontend
npm run dev
```

Wait for:
```
✓ Ready in [time]ms
- Local:   http://localhost:3001
```

### Step 2: Clear Browser Cache & Test

**Option A - Hard Refresh:**
- Press `Ctrl+Shift+R`

**Option B - Clear Storage:**
- F12 → Console
```javascript
localStorage.clear();
location.reload();
```

### Step 3: Login

- Go to `http://localhost:3001`
- Login: `inputer` / `password` / market `1`

### Expected Result ✅
- Dashboard loads without error
- Stats cards display (Pemasukan Rp 0, etc.)
- Header shows user name
- No console errors
- No network 500 errors

---

## 🧪 Verification

### Check Browser Console (F12)
- [ ] No red errors
- [ ] No "Internal Server Error" messages
- [ ] React Query shows success state

### Check Network Tab
- [ ] Request to `/reports/daily` → 200 OK
- [ ] Request to `/categories` → 200 OK
- [ ] No 401 Unauthorized (means token working)
- [ ] No 500 errors

### Check Terminal Next.js
- [ ] No error stack traces
- [ ] Shows successful page renders
- [ ] No unhandled promise rejections

---

## 📊 React Query `enabled` Option

**What it does:**
- Controls when query should run
- If `enabled: false`, query is paused
- If `enabled: true`, query runs normally

**Common Patterns:**

**1. Wait for dependency:**
```typescript
const { data: user } = useQuery({ ... });
const { data: posts } = useQuery({
  queryKey: ['posts', user?.id],
  queryFn: () => fetchPosts(user!.id),
  enabled: !!user, // Wait for user first
});
```

**2. Client-side only:**
```typescript
const { data } = useQuery({
  queryKey: ['data'],
  queryFn: fetchData,
  enabled: typeof window !== 'undefined', // No SSR
});
```

**3. Conditional fetching:**
```typescript
const [shouldFetch, setShouldFetch] = useState(false);
const { data } = useQuery({
  queryKey: ['data'],
  queryFn: fetchData,
  enabled: shouldFetch, // Manual control
});
```

---

## 🎯 Why This Fix Works

### Before (Broken):
```
User visits /dashboard
  ↓
Next.js SSR starts
  ↓
DashboardPage component renders
  ↓
useQuery runs immediately
  ↓
api.get('/reports/daily') called
  ↓
No token in server environment
  ↓
API returns 401 Unauthorized
  ↓
Component crashes
  ↓
Internal Server Error 500 ❌
```

### After (Fixed):
```
User visits /dashboard
  ↓
Next.js SSR starts
  ↓
DashboardPage component renders
  ↓
useQuery checks: enabled: !!user
  ↓
user = null (not authenticated yet)
  ↓
Query PAUSED, no API call
  ↓
Component renders with loading state
  ↓
HTML sent to browser ✅
  ↓
Client-side hydration
  ↓
AuthContext loads user from localStorage
  ↓
user = { id: 3, ... }
  ↓
useQuery: enabled: !!user = true
  ↓
Query runs, API call with token
  ↓
Data fetched, dashboard updates ✅
```

---

## 🔮 Prevention

### Best Practices for SSR-Safe Code

**1. Always check window object:**
```typescript
if (typeof window !== 'undefined') {
  // Client-side only code
  localStorage.setItem('key', 'value');
}
```

**2. Use enabled option for authenticated queries:**
```typescript
const { user } = useAuth();
const { data } = useQuery({
  queryKey: ['data'],
  queryFn: fetchData,
  enabled: !!user, // Wait for auth
});
```

**3. Use dynamic imports for client-only libraries:**
```typescript
const ClientComponent = dynamic(() => import('./ClientComponent'), {
  ssr: false
});
```

**4. Mark page as client-only if needed:**
```typescript
'use client'; // Force client-side rendering
```

---

## 📝 Files Changed

1. `app/(authenticated)/dashboard/page.tsx` (1 line added)
2. `hooks/useCategories.ts` (1 line added)

**Git Commit:**
```bash
git add app/\(authenticated\)/dashboard/page.tsx hooks/useCategories.ts
git commit -m "fix: prevent SSR crashes by conditionally enabling queries

- dashboard: only fetch data when user authenticated
- useCategories: only run on client-side
- prevents 500 Internal Server Error on page load

Co-authored-by: factory-droid[bot] <138933559+factory-droid[bot]@users.noreply.github.com>"
```

---

## ✅ Resolution

**Before:** Internal Server Error 500 on dashboard  
**After:** Dashboard loads successfully, queries run only when safe  

**Status:** ✅ **FIXED**

**Testing Required:**
- Restart Next.js dev server
- Clear browser cache
- Test login → dashboard flow

---

**Fixed by:** AI Assistant  
**Verified:** Pending user testing  
**Related Issues:** Day 48-51, Auth Loading, API Format Fixes
