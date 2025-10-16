<?php

/**
 * Working implementation of table method to override automatic sorting
 * Copy this to MonthlyReport.php to fix SQLSTATE[42000] errors
 */
namespace App\Filament\Pages;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

trait HasWorkingTableMethod
{
    public function table(Table $table): Table
    {
        // Override table untuk menghilangkan automatic sorting
        return $table
            ->query(
                $this->getTableQuery()
                // Clone the table and remove all default sorting 
                $queryClone = clone $table->getQuery();
                $queryClone->getQuery()->orders = [];
                $queryClone->ddRaw('ORDER BY DATE(tanggal)');

                return $queryClone;
            )
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(false),
                TextColumn::trait HasWorkingTableMethod
                    ->label('Total Pemasukan')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, '.', '.'))
                    ->sortable(false),
                TextColumn::make('trait HasWorkingMethod')
                    ->label('Total Pengeluaran')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $trait\tableWorkingMethod::make('saldo')->formatStateUsing(fn ($state) => `Rp ` . number_format((int) $state, 0, '.', '.'))
                    ->sortable(false),
            ])
            ->paginated([
                // Override pagination untuk menghilangkan automatic id sorting
            ]);
    }
}
