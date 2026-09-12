<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informação da encomenda')
                    ->schema([
                        TextEntry::make('id')
                            ->label('Número'),

                        TextEntry::make('customer_name')
                            ->label('Cliente'),

                        TextEntry::make('customer_email')
                            ->label('Email'),

                        TextEntry::make('status')
                            ->label('Estado')
                            ->badge(),

                        TextEntry::make('created_at')
                            ->label('Data')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2),

                Section::make('Produtos')
                    ->schema([
                        TextEntry::make('items')
                            ->label('')
                            ->html()
                            ->state(function ($record) {
                                return $record->items
                                    ->map(function ($item) {
                                        return sprintf(
                                            '<div style="margin-bottom: 12px;">
                                                <strong>%s</strong><br>
                                                Quantidade: %d<br>
                                                Preço unitário: €%s
                                            </div>',
                                            e($item->product->name),
                                            $item->quantity,
                                            number_format($item->unit_price, 2, ',', '.')
                                        );
                                    })
                                    ->implode('');
                            }),
                    ]),
            ]);
    }
}