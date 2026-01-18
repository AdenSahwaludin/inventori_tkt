<?php

namespace App\Filament\Resources\MasterBarangs;

use App\Filament\Resources\MasterBarangs\Pages\CreateMasterBarang;
use App\Filament\Resources\MasterBarangs\Pages\EditMasterBarang;
use App\Filament\Resources\MasterBarangs\Pages\ListMasterBarangs;
use App\Filament\Resources\MasterBarangs\Pages\ViewMasterBarang;
use App\Filament\Resources\MasterBarangs\Schemas\MasterBarangForm;
use App\Filament\Resources\MasterBarangs\Schemas\MasterBarangInfolist;
use App\Filament\Resources\MasterBarangs\Tables\MasterBarangsTable;
use App\Models\MasterBarang;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class MasterBarangResource extends Resource
{
    protected static ?string $model = MasterBarang::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $recordTitleAttribute = 'nama_barang';

    protected static string|UnitEnum|null $navigationGroup = 'Inventaris';

    protected static ?string $navigationLabel = 'Master Barang';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return MasterBarangForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MasterBarangInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterBarangsTable::configure($table);
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
            'index' => ListMasterBarangs::route('/'),
            'create' => CreateMasterBarang::route('/create'),
            'view' => ViewMasterBarang::route('/{record}'),
            'edit' => EditMasterBarang::route('/{record}/edit'),
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
