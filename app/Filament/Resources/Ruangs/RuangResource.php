<?php

namespace App\Filament\Resources\Ruangs;

use App\Filament\Resources\Ruangs\Pages\CreateRuang;
use App\Filament\Resources\Ruangs\Pages\EditRuang;
use App\Filament\Resources\Ruangs\Pages\ListRuangs;
use App\Filament\Resources\Ruangs\Pages\ViewRuang;
use App\Filament\Resources\Ruangs\Schemas\RuangForm;
use App\Filament\Resources\Ruangs\Tables\RuangsTable;
use App\Models\Ruang;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use UnitEnum;

class RuangResource extends Resource
{
    protected static ?string $model = Ruang::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $slug = 'ruangs';

    protected static ?string $recordTitleAttribute = 'nama_ruang';

    protected static string|UnitEnum|null $navigationGroup = 'Inventaris';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Ruang';

    public static function form(Schema $schema): Schema
    {
        return RuangForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RuangsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRuangs::route('/'),
            'create' => CreateRuang::route('/create'),
            'view' => ViewRuang::route('/{record}'),
            'edit' => EditRuang::route('/{record}/edit'),
        ];
    }
}
