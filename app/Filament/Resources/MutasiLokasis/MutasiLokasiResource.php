<?php

namespace App\Filament\Resources\MutasiLokasis;

use App\Filament\Resources\MutasiLokasis\Pages\CreateMutasiLokasi;
use App\Filament\Resources\MutasiLokasis\Pages\EditMutasiLokasi;
use App\Filament\Resources\MutasiLokasis\Pages\ListMutasiLokasis;
use App\Filament\Resources\MutasiLokasis\Pages\ViewMutasiLokasi;
use App\Filament\Resources\MutasiLokasis\Schemas\MutasiLokasiForm;
use App\Filament\Resources\MutasiLokasis\Schemas\MutasiLokasiInfolist;
use App\Filament\Resources\MutasiLokasis\Tables\MutasiLokasisTable;
use App\Models\MutasiLokasi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MutasiLokasiResource extends Resource
{
    protected static ?string $model = MutasiLokasi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $recordTitleAttribute = 'unit_barang_id';

    protected static string|UnitEnum|null $navigationGroup = 'Monitoring';

    protected static ?string $navigationLabel = 'Mutasi Lokasi';

    protected static ?int $navigationSort = 8;

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('Admin') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return MutasiLokasiForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MutasiLokasiInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MutasiLokasisTable::configure($table);
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
            'index' => ListMutasiLokasis::route('/'),
            'create' => CreateMutasiLokasi::route('/create'),
            'view' => ViewMutasiLokasi::route('/{record}'),
            'edit' => EditMutasiLokasi::route('/{record}/edit'),
        ];
    }
}
