<?php

namespace App\Filament\Resources\LogAktivitas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LogAktivitasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('jenis_aktivitas')
                    ->options([
                        'login' => 'Login',
                        'logout' => 'Logout',
                        'create' => 'Create',
                        'update' => 'Update',
                        'delete' => 'Delete',
                        'view' => 'View',
                    ])
                    ->required(),
                TextInput::make('nama_tabel'),
                TextInput::make('record_id'),
                Textarea::make('deskripsi')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('perubahan_data'),
                TextInput::make('ip_address'),
                Textarea::make('user_agent')
                    ->columnSpanFull(),
            ]);
    }
}
