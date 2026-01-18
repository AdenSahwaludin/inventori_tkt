<?php

namespace App\Filament\Resources\TransaksiBarangs;

use App\Filament\Resources\TransaksiBarangs\Pages\CreateTransaksiBarang;
use App\Filament\Resources\TransaksiBarangs\Pages\EditTransaksiBarang;
use App\Filament\Resources\TransaksiBarangs\Pages\ListTransaksiBarangs;
use App\Filament\Resources\TransaksiBarangs\Pages\ViewTransaksiBarang;
use App\Filament\Resources\TransaksiBarangs\Schemas\TransaksiBarangForm;
use App\Filament\Resources\TransaksiBarangs\Schemas\TransaksiBarangInfolist;
use App\Filament\Resources\TransaksiBarangs\Tables\TransaksiBarangsTable;
use App\Models\TransaksiBarang;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TransaksiBarangResource extends Resource
{
    protected static ?string $model = TransaksiBarang::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static ?string $recordTitleAttribute = 'kode_transaksi';

    protected static string|UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Transaksi Masuk';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return TransaksiBarangForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TransaksiBarangInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransaksiBarangsTable::configure($table);
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
            'index' => ListTransaksiBarangs::route('/'),
            'create' => CreateTransaksiBarang::route('/create'),
            'view' => ViewTransaksiBarang::route('/{record}'),
            'edit' => EditTransaksiBarang::route('/{record}/edit'),
        ];
    }
}
