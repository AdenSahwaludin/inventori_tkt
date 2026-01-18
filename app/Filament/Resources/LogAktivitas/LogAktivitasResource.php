<?php

namespace App\Filament\Resources\LogAktivitas;

use App\Filament\Resources\LogAktivitas\Pages\CreateLogAktivitas;
use App\Filament\Resources\LogAktivitas\Pages\EditLogAktivitas;
use App\Filament\Resources\LogAktivitas\Pages\ListLogAktivitas;
use App\Filament\Resources\LogAktivitas\Pages\ViewLogAktivitas;
use App\Filament\Resources\LogAktivitas\Schemas\LogAktivitasForm;
use App\Filament\Resources\LogAktivitas\Schemas\LogAktivitasInfolist;
use App\Filament\Resources\LogAktivitas\Tables\LogAktivitasTable;
use App\Models\LogAktivitas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class LogAktivitasResource extends Resource
{
    protected static ?string $model = LogAktivitas::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'deskripsi';

    protected static string|UnitEnum|null $navigationGroup = 'Monitoring';

    protected static ?int $navigationSort = 10;

    /**
     * Petugas hanya bisa melihat log aktivitas miliknya sendiri.
     * Admin & Kepala Sekolah bisa melihat semua log.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (! auth()->user()->hasRole(['Admin', 'Kepala Sekolah'])) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('Admin') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return LogAktivitasForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LogAktivitasInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LogAktivitasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLogAktivitas::route('/'),
            'create' => CreateLogAktivitas::route('/create'),
            'view' => ViewLogAktivitas::route('/{record}'),
            'edit' => EditLogAktivitas::route('/{record}/edit'),
        ];
    }
}
