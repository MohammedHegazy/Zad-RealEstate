<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    public function storeImage(UploadedFile $file, string $directory): string
    {
        return $this->storeFile($file, $directory);
    }

    public function storeFile(UploadedFile $file, string $directory): string
    {
        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $filename = Str::uuid()->toString().'.'.strtolower($extension);
        $relativePath = trim($directory, '/').'/'.$filename;

        $stored = Storage::disk('public')->putFileAs(
            trim($directory, '/'),
            $file,
            $filename
        );

        if ($stored === false) {
            throw new \RuntimeException('Failed to store uploaded file.');
        }

        return $relativePath;
    }

    public function deleteIfExists(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function publicUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $normalized = ltrim($path, '/');

        if (app()->bound('request') && ($request = request())) {
            return rtrim($request->getSchemeAndHttpHost(), '/').'/storage/'.$normalized;
        }

        return Storage::disk('public')->url($normalized);
    }
}
