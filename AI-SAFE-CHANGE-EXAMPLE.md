# Contoh: Mengubah Warna Theme dengan AI (Safe)

## 🎯 Goal
Mengubah warna primary dari biru (sky) menjadi hijau (emerald) di seluruh aplikasi frontend.

---

## ✅ Step 1: Backup & Branch

```bash
cd C:\laragon\www\bukupasar

# Commit current state
git add .
git commit -m "feat: working state before theme color change"

# Create branch
git checkout -b theme-color-emerald

# Tag backup
git tag backup-before-theme-change
```

---

## ✅ Step 2: Identify Files to Change

```bash
# Search untuk semua file yang pakai warna sky
cd bukupasar-frontend
grep -r "sky-500" app/

# Results (contoh):
# app/(authenticated)/dashboard/page.tsx: bg-sky-500
# app/(authenticated)/pemasukan/tambah/page.tsx: bg-sky-500
# app/(authenticated)/pengeluaran/tambah/page.tsx: bg-sky-500
```

**Files to change:**
1. `app/(authenticated)/dashboard/page.tsx`
2. `app/(authenticated)/pemasukan/tambah/page.tsx`
3. `app/(authenticated)/pengeluaran/tambah/page.tsx`

---

## ✅ Step 3: Prompt untuk AI

```
Context: Bukupasar Next.js frontend
Task: Change theme color dari sky (blue) ke emerald (green)

Files to change (ONLY these 3 files):
1. bukupasar-frontend/app/(authenticated)/dashboard/page.tsx
2. bukupasar-frontend/app/(authenticated)/pemasukan/tambah/page.tsx  
3. bukupasar-frontend/app/(authenticated)/pengeluaran/tambah/page.tsx

Changes needed in EACH file:
- Replace: bg-sky-500 → bg-emerald-500
- Replace: bg-sky-600 → bg-emerald-600
- Replace: hover:bg-sky-600 → hover:bg-emerald-600
- Replace: text-sky-500 → text-emerald-500
- Replace: border-sky-500 → border-emerald-500

IMPORTANT:
- ONLY change color classes (sky → emerald)
- DO NOT change any logic, functions, or structure
- DO NOT change any other files
- DO NOT touch backend files
- DO NOT change AuthContext, api.ts, or layout files
- Preserve all spacing, sizing, and responsive classes

Process:
1. Show me DIFF for file #1 first
2. Wait for my approval
3. Then proceed to file #2, then #3

Start with file #1 only.
```

---

## ✅ Step 4: Review AI Changes

AI akan show diff seperti ini:

```diff
// dashboard/page.tsx
- <button className="bg-sky-500 hover:bg-sky-600 text-white">
+ <button className="bg-emerald-500 hover:bg-emerald-600 text-white">
```

**Check:**
- ✅ Hanya warna yang berubah
- ✅ Tidak ada perubahan logic
- ✅ Tidak ada file lain tersentuh

**Response:** "OK, apply perubahan untuk file #1. Lanjut file #2."

---

## ✅ Step 5: Testing

```bash
cd bukupasar-frontend

# Build check
npm run build
# Expected: Build completed without errors

# TypeScript check  
npx tsc --noEmit
# Expected: No errors

# Manual test
npm run dev
```

**Manual test checklist:**
- [ ] Dashboard load dengan warna hijau
- [ ] Button "Tambah Pemasukan" hijau
- [ ] Button "Tambah Pengeluaran" hijau
- [ ] Hover effect masih kerja
- [ ] Tidak ada layout rusak
- [ ] Mobile responsive masih OK

---

## ✅ Step 6: Commit atau Rollback

**Jika semua OK:**
```bash
git add .
git commit -m "ui: change primary color from sky to emerald

- Update dashboard button colors
- Update pemasukan form button colors  
- Update pengeluaran form button colors
- No logic changes, UI only"

git checkout main
git merge theme-color-emerald
```

**Jika ADA YANG RUSAK:**
```bash
# Buang semua perubahan
git reset --hard HEAD

# Atau balik ke backup tag
git checkout backup-before-theme-change
```

---

## 🎓 Lessons Learned

1. ✅ **Specific scope** → AI hanya ubah 3 file yang diminta
2. ✅ **Incremental** → File by file, review tiap step
3. ✅ **Testing** → Build + manual test sebelum commit
4. ✅ **Backup** → Git branch + tag, easy rollback
5. ✅ **Clear instructions** → AI tahu apa yang BOLEH dan TIDAK BOLEH diubah

---

## 🚫 What NOT to Do

❌ **Bad Prompt:**
```
"Change color theme aplikasi jadi lebih modern dan keren"
```
**Problem:** 
- Tidak spesifik file mana
- "Modern dan keren" subjektif
- AI bisa ubah banyak hal tidak perlu

---

❌ **Bad Workflow:**
```
1. Langsung minta AI ubah tanpa commit
2. Tidak review diff
3. Tidak testing
4. Langsung commit tanpa test
5. Baru sadar rusak setelah 10 commits kemudian
```

**Problem:** Susah rollback, tidak tahu commit mana yang rusak

---

## ✅ Always Remember

> "Treat AI like junior developer: Give clear instructions, review their work, test before merging."

**Golden Rule:**
```
BACKUP → SPECIFIC PROMPT → REVIEW DIFF → TEST → COMMIT/ROLLBACK
```

---

**Last Updated:** 2025-01-15
