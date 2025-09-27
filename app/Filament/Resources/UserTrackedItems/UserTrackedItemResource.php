<?php

namespace App\Filament\Resources\UserTrackedItems;

use App\Filament\Resources\UserTrackedItems\Pages\CreateUserTrackedItem;
use App\Filament\Resources\UserTrackedItems\Pages\EditUserTrackedItem;
use App\Filament\Resources\UserTrackedItems\Pages\ListUserTrackedItems;
use App\Filament\Resources\UserTrackedItems\Schemas\UserTrackedItemForm;
use App\Filament\Resources\UserTrackedItems\Tables\UserTrackedItemsTable;
use App\Models\UserTrackedItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserTrackedItemResource extends Resource
{
    protected static ?string $model = UserTrackedItem::class;

    protected static ?string $navigationLabel = 'My Tracked Items';

    protected static ?string $modelLabel = 'Tracked Item';

    protected static ?string $pluralModelLabel = 'Tracked Items';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    public static function form(Schema $schema): Schema
    {
        return UserTrackedItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserTrackedItemsTable::configure($table);
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
            'index' => ListUserTrackedItems::route('/'),
            'create' => CreateUserTrackedItem::route('/create'),
            'edit' => EditUserTrackedItem::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id())
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
