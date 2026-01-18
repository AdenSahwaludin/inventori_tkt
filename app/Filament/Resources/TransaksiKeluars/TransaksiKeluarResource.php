<?php

namespace App\Filament\Resources\TransaksiKeluars;

use App\Filament\Resources\TransaksiKeluars\Pages\CreateTransaksiKeluar;
use App\Filament\Resources\TransaksiKeluars\Pages\EditTransaksiKeluar;
use App\Filament\Resources\TransaksiKeluars\Pages\ListTransaksiKeluars;
use App\Filament\Resources\TransaksiKeluars\Pages\ViewTransaksiKeluar;
use App\Filament\Resources\TransaksiKeluars\Schemas\TransaksiKeluarForm;
use App\Filament\Resources\TransaksiKeluars\Schemas\TransaksiKeluarInfolist;
use App\Filament\Resources\TransaksiKeluars\Tables\TransaksiKeluarsTable;
use App\Models\TransaksiKeluar;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TransaksiKeluarResource extends Resource
{
    protected static ?string $model = TransaksiKeluar::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $recordTitleAttribute = 'kode_transaksi';

    protected static string|UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Transaksi Keluar';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return TransaksiKeluarForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TransaksiKeluarInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransaksiKeluarsTable::configure($table);
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
            'index' => ListTransaksiKeluars::route('/'),
            'create' => CreateTransaksiKeluar::route('/create'),
            'view' => ViewTransaksiKeluar::route('/{record}'),
            'edit' => EditTransaksiKeluar::route('/{record}/edit'),
        ];
    }
}
