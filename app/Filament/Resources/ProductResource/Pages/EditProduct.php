<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // Tambahkan method ini:
    protected function afterSave(): void
    {
        // Tampilkan notifikasi berhasil
        Notification::make()
            ->title('Produk berhasil diperbarui!')
            ->success()
            ->send();

        // Redirect ke halaman list product dengan menutup modal
        $this->redirect(ProductResource::getUrl(), navigate: true);
    }
}
