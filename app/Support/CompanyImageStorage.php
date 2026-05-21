<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class CompanyImageStorage
{
    public function store(?UploadedFile $file): ?string
    {
        if ($file === null || ! $file->isValid()) {
            return null;
        }

        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $path = Storage::disk('public')->putFileAs('companies', $file, $filename);

        if ($path === false || $path === '') {
            throw new RuntimeException('Could not store company image.');
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
        if ($path === null || $path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalized = str_replace('\\', '/', $path);

        if (Str::startsWith($normalized, '/storage/')) {
            return $normalized;
        }

        if (! Storage::disk('public')->exists($normalized)) {
            return null;
        }

        // Relative URL so images work regardless of APP_URL host/port (e.g. 127.0.0.1:8000 vs localhost).
        return '/storage/'.ltrim($normalized, '/');
    }
}
