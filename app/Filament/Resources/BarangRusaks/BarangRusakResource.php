<?php

namespace App\Filament\Resources\BarangRusaks;

use App\Filament\Resources\BarangRusaks\Pages\CreateBarangRusak;
use App\Filament\Resources\BarangRusaks\Pages\EditBarangRusak;
use App\Filament\Resources\BarangRusaks\Pages\ListBarangRusaks;
use App\Filament\Resources\BarangRusaks\Pages\ViewBarangRusak;
use App\Filament\Resources\BarangRusaks\Schemas\BarangRusakForm;
use App\Filament\Resources\BarangRusaks\Schemas\BarangRusakInfolist;
use App\Filament\Resources\BarangRusaks\Tables\BarangRusaksTable;
use App\Models\BarangRusak;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BarangRusakResource extends Resource
{
    protected static ?string $model = BarangRusak::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $recordTitleAttribute = 'unit_barang_id';

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Barang Rusak';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return BarangRusakForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BarangRusakInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BarangRusaksTable::configure($table);
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
            'index' => ListBarangRusaks::route('/'),
            'create' => CreateBarangRusak::route('/create'),
            'view' => ViewBarangRusak::route('/{record}'),
            'edit' => EditBarangRusak::route('/{record}/edit'),
        ];
    }
}
