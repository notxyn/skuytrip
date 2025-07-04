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
        Notification::make()
            ->title('Produk berhasil diperbarui!')
            ->success()
            ->send();

        // Get the current page from the query string, default to 1
        $page = request()->query('page', 1);

        // Redirect back to the index with the same page number
        $this->redirect(ProductResource::getUrl(['page' => $page]), navigate: true);
    }
}
