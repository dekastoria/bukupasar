'use client';

import { useMutation, useQueryClient } from '@tanstack/react-query';

import api from '@/lib/api';

interface CreateTransactionPayload {
  tanggal: string;
  jenis: 'pemasukan' | 'pengeluaran';
  subkategori: string;
  jumlah: number;
  catatan?: string | null;
  tenant_id?: number | null;
}

export const useCreateTransaction = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (payload: CreateTransactionPayload) => {
      const response = await api.post('/transactions', payload);
      return response.data;
    },
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['dashboard-summary'] });
      void queryClient.invalidateQueries({ queryKey: ['transactions'] });
    },
  });
};
