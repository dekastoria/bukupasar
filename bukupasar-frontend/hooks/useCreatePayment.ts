'use client';

import { useMutation, useQueryClient } from '@tanstack/react-query';

import api from '@/lib/api';

interface CreatePaymentPayload {
  tenant_id: number;
  jumlah: number;
  tanggal: string;
  catatan?: string | null;
}

export const useCreatePayment = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (payload: CreatePaymentPayload) => {
      const response = await api.post('/payments', payload);
      return response.data;
    },
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['tenants'] });
      void queryClient.invalidateQueries({ queryKey: ['dashboard-summary'] });
      void queryClient.invalidateQueries({ queryKey: ['payments'] });
    },
  });
};
