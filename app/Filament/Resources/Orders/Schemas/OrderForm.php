<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('customer_name')
                    ->label('Nome do cliente')
                    ->required()
                    ->maxLength(255),

                TextInput::make('customer_email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendente',
                        'paid' => 'Pago',
                        'cancelled' => 'Cancelada',
                    ])
                    ->required(),
            ]);
    }
}