<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function afterCreate(): void
    {
        \Filament\Notifications\Notification::make()
            ->title('Booking berhasil dibuat!')
            ->success()
            ->send();

        $this->redirect(BookingResource::getUrl(), navigate: true);
    }
} 