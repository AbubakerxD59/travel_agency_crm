<?php

namespace App\Support;

use Throwable;

class EnsurePublicStorage
{
    public static function run(): void
    {
        $target = storage_path('app/public');

        if (! is_dir($target)) {
            mkdir($target, 0755, true);
        }

        foreach (['companies', 'folder-payments'] as $subdir) {
            $dir = $target.DIRECTORY_SEPARATOR.$subdir;
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $link = public_path('storage');

        if (file_exists($link) || is_link($link)) {
            return;
        }

        try {
            symlink($target, $link);
        } catch (Throwable) {
            // Shared hosts may block symlinks; /storage/* is served via routes/web.php fallback.
        }
    }
}
