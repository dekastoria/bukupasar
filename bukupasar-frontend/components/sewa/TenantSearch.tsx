'use client';

import { useState, useEffect } from 'react';
import { Search, User } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card } from '@/components/ui/card';
import { useSearchTenants } from '@/hooks/useSearchTenants';
import { cn } from '@/lib/utils';

interface Tenant {
  id: number;
  nama: string;
  nomor_lapak: string;
  outstanding: number;
  rental_type?: {
    nama: string;
  };
}

interface TenantSearchProps {
  onSelect: (tenant: Tenant) => void;
  selectedTenant: Tenant | null;
}

export function TenantSearch({ onSelect, selectedTenant }: TenantSearchProps) {
  const [searchQuery, setSearchQuery] = useState('');
  const [showResults, setShowResults] = useState(false);
  
  const { data: tenants = [], isLoading } = useSearchTenants(searchQuery);

  useEffect(() => {
    if (selectedTenant) {
      setSearchQuery(`${selectedTenant.nomor_lapak} - ${selectedTenant.nama}`);
      setShowResults(false);
    }
  }, [selectedTenant]);

  const handleInputChange = (value: string) => {
    setSearchQuery(value);
    setShowResults(true);
    
    // Clear selection if user modifies search
    if (selectedTenant) {
      onSelect(null as any);
    }
  };

  const handleSelectTenant = (tenant: Tenant) => {
    onSelect(tenant);
    setSearchQuery(`${tenant.nomor_lapak} - ${tenant.nama}`);
    setShowResults(false);
  };

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(value);
  };

  return (
    <div className="space-y-2">
      <Label htmlFor="tenant-search" className="text-xl font-medium">
        Cari Penyewa
      </Label>
      
      <div className="relative">
        <div className="relative">
          <Search className="absolute left-4 top-1/2 -translate-y-1/2 h-6 w-6 text-slate-400" />
          <Input
            id="tenant-search"
            type="text"
            placeholder="Ketik nomor atau nama penyewa..."
            value={searchQuery}
            onChange={(e) => handleInputChange(e.target.value)}
            onFocus={() => searchQuery.length >= 2 && setShowResults(true)}
            className={cn(
              "h-14 pl-12 text-xl",
              selectedTenant && "border-green-500 bg-green-50"
            )}
          />
        </div>

        {/* Search Results Dropdown */}
        {showResults && searchQuery.length >= 2 && (
          <Card className="absolute z-10 w-full mt-2 max-h-[400px] overflow-y-auto shadow-lg">
            {isLoading && (
              <div className="p-4 text-center text-slate-500">
                Mencari...
              </div>
            )}

            {!isLoading && tenants.length === 0 && (
              <div className="p-4 text-center text-slate-500">
                Tidak ada penyewa ditemukan
              </div>
            )}

            {!isLoading && tenants.length > 0 && (
              <div className="divide-y">
                {tenants.map((tenant: Tenant) => (
                  <button
                    key={tenant.id}
                    onClick={() => handleSelectTenant(tenant)}
                    className="w-full p-4 text-left hover:bg-slate-50 transition-colors focus:outline-none focus:bg-slate-100"
                  >
                    <div className="flex items-start gap-3">
                      <div className="mt-1 p-2 bg-blue-100 rounded-full">
                        <User className="h-5 w-5 text-blue-600" />
                      </div>
                      
                      <div className="flex-1 min-w-0">
                        <div className="flex items-baseline gap-2 mb-1">
                          <span className="font-bold text-lg text-slate-900">
                            {tenant.nomor_lapak}
                          </span>
                          {tenant.rental_type && (
                            <span className="text-sm px-2 py-0.5 bg-blue-100 text-blue-700 rounded">
                              {tenant.rental_type.nama}
                            </span>
                          )}
                        </div>
                        
                        <p className="text-lg text-slate-700 mb-1">
                          {tenant.nama}
                        </p>
                        
                        <p className={cn(
                          "text-base font-medium",
                          tenant.outstanding > 0 ? "text-red-600" : "text-green-600"
                        )}>
                          Outstanding: {formatCurrency(tenant.outstanding)}
                        </p>
                      </div>
                    </div>
                  </button>
                ))}
              </div>
            )}
          </Card>
        )}

        {/* Click outside to close */}
        {showResults && (
          <div
            className="fixed inset-0 z-0"
            onClick={() => setShowResults(false)}
          />
        )}
      </div>

      {selectedTenant && (
        <p className="text-sm text-green-600 flex items-center gap-2">
          <span className="inline-block w-2 h-2 bg-green-500 rounded-full" />
          Penyewa dipilih
        </p>
      )}
    </div>
  );
}
