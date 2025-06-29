<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Attraction;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

class ProductResource extends Resource
{
    protected static ?string $model = Attraction::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Attractions';
    protected static ?string $pluralModelLabel = 'Attractions';
    protected static ?string $modelLabel = 'Attraction';
    protected static ?string $navigationGroup = 'Travel Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('slug')
                    ->label('Slug')
                    ->required(),
                TextInput::make('name')
                    ->label('Name')
                    ->required(),
                FileUpload::make('img')
                    ->label('Image')
                    ->disk('public')
                    ->visibility('public'),
                TextInput::make('loc')
                    ->label('Location')
                    ->required(),
                Textarea::make('desc')
                    ->label('Description')
                    ->required(),
                TextInput::make('rate')
                    ->label('Rating')
                    ->numeric(),
                TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->required(),
                TextInput::make('tags')
                    ->label('Tags (comma separated)')
                    ->required()
                    ->afterStateHydrated(function ($component, $state) {
                        // Convert array to comma string for display
                        if (is_array($state)) {
                            $component->state(implode(',', $state));
                        }
                    })
                    ->dehydrateStateUsing(function ($state) {
                        // Convert comma string to array for saving
                        return array_map('trim', explode(',', $state));
                    })
                    ->helperText('Enter tags separated by commas'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('img')
                    ->label('Image')
                    ->disk('public')
                    ->width(100)
                    ->height(100)
                    ->square(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('loc')
                    ->label('Location'),
                TextColumn::make('desc')
                    ->label('Description')
                    ->limit(50),
                TextColumn::make('rate')
                    ->label('Rating'),
                TextColumn::make('price')
                    ->label('Price')
                    ->money('IDR', true)
                    ->sortable(),
                TextColumn::make('tags')
                    ->label('Tags')
                    ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : $state),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
