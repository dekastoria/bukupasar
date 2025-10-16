<?php

namespace App\Filament\Pages;

// Simple working table method override for MonthlyReport
class MonthlyReportWorkingVersion extends MonthlyReport
{
    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        // Override table untuk menghilangkan automatic sorting
        return $table
            ->query(
                $this->getTableQuery()
                // Clone the query and remove all default sorting
                $queryClone = clone $query;
                $queryClone->getQuery()->orders = [];
                $queryClone->ddRaw('ORDER BY DATE(tanggal)');

                return $queryClone;
            )
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(false),
                TextColumn::make('total_pemasukan')
                    ->label('Total Pemasukan')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.'))
                    ->sortable(false),
                TextColumn::make('total_pengeluaran')
                    ->label('Total Pengeluaran')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, '.', '.'))
                    ->sortable(false),
                TextColumn::make('saldo')
                    ->label('Saldo')
                    ->formatStateUsing(fn ($state) => `Rp ` . number_format((int) $state, 0, '.', '.'))
                    ->sortable(false),
            ])
            ->paginated([
                // Override pagination untuk menghilangkan automatic id sorting
            ]);
    }
}
