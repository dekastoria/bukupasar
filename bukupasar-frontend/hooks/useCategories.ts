'use client';

import { useQuery } from '@tanstack/react-query';

import api from '@/lib/api';

export const useCategories = (jenis: 'pemasukan' | 'pengeluaran') =>
  useQuery({
    queryKey: ['categories', jenis],
    queryFn: async () => {
      const response = await api.get(`/categories`, {
        params: { jenis, aktif: 1 },
      });

      // Backend returns { data: [...] }
      return response.data?.data ?? response.data ?? [];
    },
    staleTime: 10 * 60 * 1000,
    enabled: true, // Enable query to prevent hydration mismatch
  });
