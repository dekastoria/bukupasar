# Bukupasar 

**Aplikasi Web Multi-Tenant untuk Manajemen Keuangan Pasar Tradisional**

---

## 🎯 Tentang Project

Bukupasar adalah sistem web untuk mengelola keuangan pasar tradisional dengan fitur:

- 💰 **Input Pemasukan/Pengeluaran** - Pencatatan kas harian yang mudah
- 🏪 **Manajemen Penyewa** - Tracking penyewa kios/lapak dan pembayaran sewa
- 📊 **Laporan Keuangan** - Laporan harian, bulanan, laba rugi
- 🏢 **Multi-Tenant** - Satu sistem untuk beberapa pasar dengan admin independen
- 📱 **Mobile-First** - UX dioptimalkan untuk pengguna lansia di perangkat mobile

---

## 🏗️ Tech Stack

**Backend:**
- Laravel 11 + Filament 3 (Admin Panel)
- MySQL 8
- Laravel Sanctum (API Auth)
- Spatie Permission (RBAC)

**Frontend:**
- Next.js 14 (App Router)
- Tailwind CSS + shadcn/ui
- TanStack Query
- React Hook Form + Zod

**Infrastructure:**
- Development: Laragon (Windows)
- Production: aaPanel VPS

---

## 📚 Documentation

### 🚀 Start Here

**New to this project?** Read in this order:

1. **[01-PROJECT-SPEC.md](01-PROJECT-SPEC.md)** (15-20 min)
   - Vision & goals
   - System architecture
   - Database design (ERD + DDL)
   - Multi-tenant strategy
   - RBAC & business rules

2. **[05-AI-ASSISTANT-GUIDE.md](05-AI-ASSISTANT-GUIDE.md)** (10-15 min)
   - How to work with AI
   - Prompt templates library ⭐ **Most Valuable**
   - Weekly implementation plan
   - Common pitfalls & solutions

3. **[TO-DO-LIST.md](TO-DO-LIST.md)** (5 min)
   - Current progress
   - Week-by-week checklist
   - Daily log

4. **[LOGIN-CREDENTIALS.md](LOGIN-CREDENTIALS.md)** 🔐 (2 min)
   - Login credentials untuk testing
   - Username & password semua role
   - Market ID untuk development

5. **[START-DEV-SERVERS.md](START-DEV-SERVERS.md)** 🚀 (3 min)
   - Cara menjalankan backend & frontend servers
   - Troubleshooting network errors
   - Quick restart scripts

---

### 📖 Implementation Guides

**Backend (Laravel):**
- **[02-BACKEND-GUIDE.md](02-BACKEND-GUIDE.md)**
  - Laravel project setup
  - Database migrations (complete DDL)
  - Eloquent models with relationships
  - API endpoints (REST)
  - Filament admin panel
  - Testing

**Frontend (Next.js):**
- **[03-FRONTEND-GUIDE.md](03-FRONTEND-GUIDE.md)**
  - Next.js project setup
  - Authentication flow
  - Pages specification (Dashboard, Forms, Reports)
  - UX guidelines for elderly users
  - API integration with TanStack Query

**Deployment:**
- **[04-DEPLOYMENT-OPS.md](04-DEPLOYMENT-OPS.md)**
  - Local development (Laragon)
  - Production deployment (aaPanel VPS)
  - Backup & restore procedures
  - Monitoring & troubleshooting

---

## 🤖 Working with AI

**Every AI Session, Start With:**

```
"Saya sedang develop project Bukupasar - aplikasi keuangan pasar multi-tenant.

Please load these files untuk context:
1. 01-PROJECT-SPEC.md - Architecture & database
2. 02-BACKEND-GUIDE.md (or 03 for frontend) - Implementation guide
3. 05-AI-ASSISTANT-GUIDE.md - Workflow & prompts

Current progress: lihat TO-DO-LIST.md → [Week X]

Task hari ini: [specific task]

Ready?"
```

**Then:** Use prompt templates from [05-AI-ASSISTANT-GUIDE.md](05-AI-ASSISTANT-GUIDE.md) Section 3.

**Example Tasks:**
- Generate migration for transactions table → Use "Generate Migration" template
- Create Transaction model → Use "Generate Model" template
- Build Transaction API → Use "Generate API Controller" template
- Create Filament resource → Use "Generate Filament Resource" template
- Build Next.js form → Use "Generate Next.js Page" template

---

## 🚦 Quick Start

### Prerequisites ✅ (Already Ready)

- [x] Laragon (PHP 8.2+, MySQL 8, Nginx)
- [x] Node.js 18+
- [x] Composer 2.x
- [x] Git

### Step 1: Create Projects

**Laravel Backend:**
```bash
cd C:\laragon\www
composer create-project laravel/laravel bukupasar-backend
cd bukupasar-backend
```

**Next.js Frontend:**
```bash
cd C:\laragon\www
npx create-next-app@latest bukupasar-frontend
cd bukupasar-frontend
```

### Step 2: Follow Implementation Plan

Open [TO-DO-LIST.md](TO-DO-LIST.md) and follow Phase 1 → Week 1 → Day 1.

Each task has:
- Clear checklist
- AI prompt template to use
- Verification steps

---

## 📂 Project Structure

```
D:\belajar-website\pasar\
├── 01-PROJECT-SPEC.md          # Architecture & database (1200 lines)
├── 02-BACKEND-GUIDE.md         # Laravel implementation (1000 lines)
├── 03-FRONTEND-GUIDE.md        # Next.js implementation (800 lines)
├── 04-DEPLOYMENT-OPS.md        # Deployment & ops (600 lines)
├── 05-AI-ASSISTANT-GUIDE.md    # AI prompts & workflow (800 lines) ⭐
├── TO-DO-LIST.md               # Progress tracker (updated daily)
├── README.md                   # This file (navigation)
│
├── archive/                    # Backup of original files
│   ├── readme-original.md      # Original 2800-line documentation
│   ├── progress-original.md    # Original progress notes
│   ├── idea-1.md              # Brainstorming document
│   └── idea-2.md              # Additional ideas
│
├── bukupasar-backend/          # Laravel project (to be created)
└── bukupasar-frontend/         # Next.js project (to be created)
```

---

## 📋 Development Workflow

### Daily Routine

**Morning (5 min):**
1. Open [TO-DO-LIST.md](TO-DO-LIST.md)
2. Review task untuk hari ini
3. Note any blockers dari kemarin

**Development Session (2-4 hours):**
1. Start AI session dengan context loading
2. Copy-paste prompt template dari [05-AI-ASSISTANT-GUIDE.md](05-AI-ASSISTANT-GUIDE.md)
3. Generate code dengan AI
4. Test feature
5. Commit to Git

**End of Day (5 min):**
1. Update [TO-DO-LIST.md](TO-DO-LIST.md):
   - [x] Mark completed tasks
   - Add blocker notes
   - Plan besok
2. Git push changes

---

## 🎯 Roadmap

| Phase | Duration | Goal |
|-------|----------|------|
| **Phase 0** ✅ | 1 day | Documentation setup |
| **Phase 1** ⏳ | Week 1-2 | Database & models |
| **Phase 2** ⏳ | Week 3-4 | Backend API |
| **Phase 3** ⏳ | Week 5-6 | Filament admin panel |
| **Phase 4** ⏳ | Week 7-8 | Frontend SPA |
| **Phase 5** ⏳ | Week 9-10 | Testing & integration |
| **Phase 6** ⏳ | Week 11-12 | Deployment to VPS |

**Current Status:** Phase 0 Complete ✅ | **Next:** Phase 1 Week 1 Day 1

**Total Timeline:** 12-17 weeks (realistic untuk non-coder dengan AI assistance)

---

## 🏆 Success Criteria

**MVP Complete When:**
- [x] All 5 documentation files created
- [ ] Database schema implemented
- [ ] Backend API functional
- [ ] Filament admin working
- [ ] Frontend SPA deployed
- [ ] Can complete end-to-end transaction flow:
  - Login → Input Pemasukan → View Laporan → Logout
- [ ] Mobile responsive
- [ ] Deployed to production VPS
- [ ] Backup automated

---

## 💡 Tips for Non-Coders

1. **Follow the checklist** - TO-DO-LIST.md is your daily guide
2. **Use AI prompts** - Copy-paste templates, don't write from scratch
3. **Test frequently** - After each feature, test immediately
4. **Commit often** - Git commit after each working feature
5. **Don't skip** - Follow order: Database → Backend → Frontend → Deploy
6. **Ask AI when stuck** - Use "Debug Error" template from guide

---

## 🆘 Need Help?

**Common Issues:**
- See [04-DEPLOYMENT-OPS.md](04-DEPLOYMENT-OPS.md) Section 6: Troubleshooting
- See [05-AI-ASSISTANT-GUIDE.md](05-AI-ASSISTANT-GUIDE.md) Section 5: Common Pitfalls

**Stuck on a task?**
Use this AI prompt:
```
"I'm stuck on [task name] for Bukupasar project.

Error/Problem: [describe]

What I've tried: [list]

Reference: [relevant doc section]

Please help me debug step-by-step."
```

---

## 📞 Support

**Documentation Issues:**
- Check [archive/readme-original.md](archive/readme-original.md) for complete original spec

**Project Status:**
- Check [TO-DO-LIST.md](TO-DO-LIST.md) for latest progress

---

## 📄 License

This is a private project for internal use.

---

## 🎉 Let's Build!

**Ready to start?**

1. ✅ Read [01-PROJECT-SPEC.md](01-PROJECT-SPEC.md) (understand architecture)
2. ✅ Read [05-AI-ASSISTANT-GUIDE.md](05-AI-ASSISTANT-GUIDE.md) (learn workflow)
3. ➡️ **Open [TO-DO-LIST.md](TO-DO-LIST.md)** (start Phase 1 Week 1 Day 1)

**First Task:** Create Laravel and Next.js projects (Est. 2-3 hours)

Good luck! 🚀

---

**Last Updated:** 2025-01-15
