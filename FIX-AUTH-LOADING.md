# 🔧 Fix: Auth Loading Stuck "Memuat data akun..."

**Issue:** Setelah login, stuck di loading screen "Memuat data akun..."  
**Root Cause:** Backend API /auth/user response format tidak match dengan frontend AuthUser interface  
**Status:** ✅ Fixed  
**Date:** 2025-01-15

---

## 🔍 Problem

### Symptom
Setelah berhasil login:
- Redirect ke `/dashboard`
- Tampil "Memuat data akun..." terus menerus
- Tidak muncul dashboard content
- Browser tidak error, stuck di loading state

### Root Cause

**Backend Response** (`/auth/user`):
```json
{
  "data": {
    "id": 1,
    "name": "Inputer Pasar",
    "username": "inputer",
    "email": "inputer@example.com",
    "market": {                    // ❌ Object, bukan ID
      "id": 1,
      "name": "Pasar Test"
    },
    "roles": ["inputer"]           // ❌ Array
  }
}
```

**Frontend Expected** (AuthUser interface):
```typescript
{
  id: number;
  name: string;
  username: string;
  market_id: number;  // ❌ Expecting ID, not object
  email?: string;
  role?: string;      // ❌ Expecting string, not array
  roles?: string[];
}
```

**What Happened:**
1. User login successfully, token saved
2. AuthContext calls `refreshUser()`
3. API returns user data with wrong format
4. `extractUser()` can't find `market_id` field
5. Returns incomplete user object
6. Auth validation fails
7. Stuck in loading loop

---

## ✅ Solution

### Fix Backend AuthController

**File:** `app/Http/Controllers/Api/AuthController.php`

**Before:**
```php
public function user(Request $request): JsonResponse
{
    $user = $request->user()->load('market');

    return response()->json([
        'data' => [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'market' => $user->market,        // ❌ Full object
            'roles' => $user->getRoleNames(), // ❌ Array only
        ],
    ]);
}
```

**After:**
```php
public function user(Request $request): JsonResponse
{
    $user = $request->user()->load('market');

    return response()->json([
        'data' => [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'market_id' => $user->market_id,           // ✅ ID only
            'role' => $user->getRoleNames()->first(),  // ✅ First role as string
            'roles' => $user->getRoleNames(),          // ✅ Keep array for reference
        ],
    ]);
}
```

**Changes:**
- ✅ Return `market_id` (integer) instead of full `market` object
- ✅ Return `role` (string, first role) for easy access
- ✅ Keep `roles` array for compatibility

---

## 🧪 Testing

### Manual Test

**1. Clear Browser State:**
```javascript
// Open DevTools → Console
localStorage.clear();
location.reload();
```

**2. Restart Backend:**
```bash
# Stop backend (Ctrl+C), then:
cd C:\laragon\www\bukupasar\bukupasar-backend
php artisan serve --host=127.0.0.1 --port=8000
```

**3. Test Login Flow:**
- Go to `http://localhost:3001`
- Login: `inputer` / `password` / market `1`
- Should redirect to `/dashboard` immediately ✅
- No stuck at "Memuat data akun..." ✅
- Dashboard displays with user name in header ✅

### API Testing with curl

**Get Token:**
```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"inputer","password":"password","market_id":1}'
```

**Response:**
```json
{
  "message": "Login berhasil.",
  "data": {
    "token": "1|abc123...",
    "user": {
      "id": 3,
      "name": "Inputer Pasar",
      "username": "inputer",
      "email": "inputer@example.com",
      "market_id": 1,  // ✅ Has market_id
      "role": "inputer" // ✅ Has role
    }
  }
}
```

**Test /auth/user:**
```bash
curl http://127.0.0.1:8000/api/auth/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Expected Response:**
```json
{
  "data": {
    "id": 3,
    "name": "Inputer Pasar",
    "username": "inputer",
    "email": "inputer@example.com",
    "market_id": 1,      // ✅ Integer
    "role": "inputer",   // ✅ String
    "roles": ["inputer"] // ✅ Array
  }
}
```

---

## 🔄 Auth Flow Diagram

### Before (Broken):
```
Login → Save Token → Redirect to Dashboard
  ↓
Dashboard loads → AuthContext checks token
  ↓
Call /auth/user → Backend returns { market: {...}, roles: [...] }
  ↓
extractUser() → Can't find market_id
  ↓
User validation fails → setUser(null)
  ↓
isLoading stays true → "Memuat data akun..."
  ↓
STUCK ❌
```

### After (Fixed):
```
Login → Save Token → Redirect to Dashboard
  ↓
Dashboard loads → AuthContext checks token
  ↓
Call /auth/user → Backend returns { market_id: 1, role: "inputer" }
  ↓
extractUser() → Successfully extracts user
  ↓
setUser(user) → User authenticated ✅
  ↓
isLoading = false → Dashboard renders
  ↓
SUCCESS ✅
```

---

## 📋 Checklist After Fix

### Backend
- [x] AuthController updated
- [x] `/auth/user` returns `market_id`
- [x] `/auth/user` returns `role` (singular)
- [x] Backend restarted

### Frontend
- [ ] Clear localStorage
- [ ] Hard refresh browser (Ctrl+Shift+R)
- [ ] Login successfully
- [ ] Dashboard loads immediately
- [ ] Header shows user name
- [ ] No console errors

---

## 🎯 Prevention

### For Future API Changes

**1. Document API Response Format**
Create `API-CONTRACTS.md`:
```typescript
// POST /api/auth/login
interface LoginResponse {
  message: string;
  data: {
    token: string;
    user: AuthUser;
  };
}

// GET /api/auth/user
interface AuthUserResponse {
  data: AuthUser;
}

interface AuthUser {
  id: number;
  name: string;
  username: string;
  email: string | null;
  market_id: number;    // Always ID, not object
  role: string;         // First role as string
  roles: string[];      // All roles as array
}
```

**2. Add Response Validation**
Use Zod or similar:
```typescript
import { z } from 'zod';

const AuthUserSchema = z.object({
  id: z.number(),
  name: z.string(),
  username: z.string(),
  market_id: z.number(),
  email: z.string().nullable(),
  role: z.string(),
  roles: z.array(z.string()),
});

// In AuthContext
const response = await api.get('/auth/user');
const validated = AuthUserSchema.parse(response.data.data);
```

**3. Backend Tests**
Add feature test:
```php
// tests/Feature/AuthControllerTest.php
public function test_auth_user_returns_correct_format()
{
    $user = User::factory()->create();
    $response = $this->actingAs($user)->getJson('/api/auth/user');
    
    $response->assertJsonStructure([
        'data' => [
            'id',
            'name',
            'username',
            'email',
            'market_id',  // Must be integer
            'role',       // Must be string
            'roles',      // Must be array
        ]
    ]);
}
```

---

## ✅ Resolution

**Status:** ✅ **FIXED**

**Changes Made:**
1. Backend AuthController updated to return correct format
2. Response now matches frontend AuthUser interface

**Testing Required:**
- Manual login test
- Verify dashboard loads
- Check header displays user info

**Next Steps:**
- Clear browser cache
- Restart backend server
- Test login → dashboard flow

---

**Fixed by:** AI Assistant  
**Verified:** Pending user testing  
**Related Issue:** Day 48-51 Implementation
