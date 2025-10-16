'use client';

import { useQuery } from '@tanstack/react-query';

import api from '@/lib/api';

export const useTenants = () =>
  useQuery({
    queryKey: ['tenants'],
    queryFn: async () => {
      const response = await api.get('/tenants');
      // Backend returns { data: [...] }
      return response.data?.data ?? response.data ?? [];
    },
    staleTime: 5 * 60 * 1000, // 5 minutes
    enabled: true,
  });
