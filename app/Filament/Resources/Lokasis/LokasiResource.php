<?php

namespace App\Filament\Resources\Lokasis;

use App\Filament\Resources\Lokasis\Pages\CreateLokasi;
use App\Filament\Resources\Lokasis\Pages\EditLokasi;
use App\Filament\Resources\Lokasis\Pages\ListLokasis;
use App\Filament\Resources\Lokasis\Pages\ViewLokasi;
use App\Filament\Resources\Lokasis\Schemas\LokasiForm;
use App\Filament\Resources\Lokasis\Schemas\LokasiInfolist;
use App\Filament\Resources\Lokasis\Tables\LokasisTable;
use App\Models\Lokasi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class LokasiResource extends Resource
{
    protected static ?string $model = Lokasi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'nama_lokasi';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return LokasiForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LokasiInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LokasisTable::configure($table);
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
            'index' => ListLokasis::route('/'),
            'create' => CreateLokasi::route('/create'),
            'view' => ViewLokasi::route('/{record}'),
            'edit' => EditLokasi::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
