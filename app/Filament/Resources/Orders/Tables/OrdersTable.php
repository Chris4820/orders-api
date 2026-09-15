<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
    ViewAction::make(),

    EditAction::make(),

    Action::make('cancel')
        ->label('Cancelar')
        ->icon(Heroicon::OutlinedXCircle)
        ->color('danger')
        ->requiresConfirmation()
        ->modalHeading('Cancelar encomenda')
        ->modalDescription(
            'Tens a certeza que queres cancelar esta encomenda? O stock dos produtos será reposto.'
        )
        ->action(function ($record) {
            app(\App\Http\Controllers\Api\OrderController::class)
                ->cancel($record);
        })
        ->visible(fn ($record) => $record->status !== 'cancelled'),
])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}