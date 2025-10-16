'use client';

import { useQuery } from '@tanstack/react-query';

import api from '@/lib/api';

export const useSearchTenants = (query: string) =>
  useQuery({
    queryKey: ['tenants', 'search', query],
    queryFn: async () => {
      if (!query || query.length < 2) return [];
      
      const response = await api.get(`/tenants/search/${query}`);
      // Backend returns { data: [...] }
      return response.data?.data ?? response.data ?? [];
    },
    staleTime: 2 * 60 * 1000, // 2 minutes
    enabled: query.length >= 2,
  });
