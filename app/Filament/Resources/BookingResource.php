<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\Attraction;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\Section;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Bookings';
    protected static ?string $pluralModelLabel = 'Bookings';
    protected static ?string $modelLabel = 'Booking';
    protected static ?string $navigationGroup = 'Travel Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Booking Information')
                    ->schema([
                        Select::make('user_id')
                            ->label('Customer')
                            ->options(User::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('attraction_id')
                            ->label('Attraction')
                            ->options(Attraction::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('name')
                            ->label('Customer Name')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->required(),
                    ])->columns(2),

                Section::make('Booking Details')
                    ->schema([
                        DatePicker::make('date')
                            ->label('Visit Date')
                            ->required(),
                        TextInput::make('quantity')
                            ->label('Number of Tickets')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        TextInput::make('total')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('Rp ')
                            ->required(),
                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'visa' => 'Visa',
                                'mastercard' => 'Mastercard',
                                'paypal' => 'PayPal',
                                'bank_transfer' => 'Bank Transfer',
                                'cash' => 'Cash',
                            ])
                            ->required(),
                        Select::make('status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'cancelled' => 'Cancelled',
                                'refunded' => 'Refunded',
                            ])
                            ->default('pending')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Booking ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('attraction.name')
                    ->label('Attraction')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Customer Name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Phone'),
                TextColumn::make('date')
                    ->label('Visit Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Tickets')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total Amount')
                    ->money('IDR', true)
                    ->sortable(),
                BadgeColumn::make('payment_method')
                    ->label('Payment Method')
                    ->colors([
                        'primary' => 'visa',
                        'secondary' => 'mastercard',
                        'success' => 'paypal',
                        'warning' => 'bank_transfer',
                        'danger' => 'cash',
                    ]),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'cancelled',
                        'info' => 'refunded',
                    ]),
                TextColumn::make('created_at')
                    ->label('Booked On')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'visa' => 'Visa',
                        'mastercard' => 'Mastercard',
                        'paypal' => 'PayPal',
                        'bank_transfer' => 'Bank Transfer',
                        'cash' => 'Cash',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'view' => Pages\ViewBooking::route('/{record}'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
} 