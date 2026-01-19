<?php

namespace App\Filament\Resources\UnitBarangs;

use App\Filament\Resources\UnitBarangs\Pages\CreateUnitBarang;
use App\Filament\Resources\UnitBarangs\Pages\EditUnitBarang;
use App\Filament\Resources\UnitBarangs\Pages\ListUnitBarangs;
use App\Filament\Resources\UnitBarangs\Pages\ViewUnitBarang;
use App\Filament\Resources\UnitBarangs\Schemas\UnitBarangForm;
use App\Filament\Resources\UnitBarangs\Schemas\UnitBarangInfolist;
use App\Filament\Resources\UnitBarangs\Tables\UnitBarangsTable;
use App\Models\UnitBarang;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UnitBarangResource extends Resource
{
    protected static ?string $model = UnitBarang::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $recordTitleAttribute = 'kode_unit';

    protected static string|UnitEnum|null $navigationGroup = 'Inventaris';

    protected static ?string $navigationLabel = 'Unit Barang';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return UnitBarangForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UnitBarangInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UnitBarangsTable::configure($table);
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
            'index' => ListUnitBarangs::route('/'),
            'create' => CreateUnitBarang::route('/create'),
            'view' => ViewUnitBarang::route('/{record}'),
            'edit' => EditUnitBarang::route('/{record}/edit'),
        ];
    }
}
