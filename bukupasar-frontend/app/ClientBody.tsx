'use client';

import { AppProviders } from "./providers";
import { Toaster } from "sonner";

export default function ClientBody({ children }: { children: React.ReactNode }) {
  return (
    <>
      <AppProviders>
        <div className="min-h-screen bg-slate-50 text-slate-900">
          {children}
        </div>
      </AppProviders>
      <Toaster richColors closeButton position="top-center" />
    </>
  );
}
