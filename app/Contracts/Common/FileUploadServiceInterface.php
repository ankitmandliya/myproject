<?php

declare(strict_types=1);

namespace App\Contracts\Common;

use Illuminate\Http\UploadedFile;

interface FileUploadServiceInterface
{
    public function uploadImage(UploadedFile $file, string $directory = 'uploads/images', string $disk = 'public'): string;

    public function uploadDocument(UploadedFile $file, string $directory = 'uploads/documents', string $disk = 'public'): string;

    public function deleteFile(string $path, string $disk = 'public'): bool;

    public function fileExists(string $path, string $disk = 'public'): bool;

    public function generateUniqueFilename(UploadedFile $file): string;
}
