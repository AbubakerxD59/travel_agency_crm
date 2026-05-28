<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class FolderPaymentImageStorage
{
    public function store(?UploadedFile $file): ?string
    {
        if ($file === null || ! $file->isValid()) {
            return null;
        }

        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $path = Storage::disk('public')->putFileAs('folder-payments', $file, $filename);

        if ($path === false || $path === '') {
            throw new RuntimeException('Could not store folder payment image.');
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    public function url(?string $path): ?string
    {
        return public_storage_url($path);
    }
}
