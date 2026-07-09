<?php

declare(strict_types=1);

namespace App\Services\Common;

use App\Contracts\Common\FileUploadServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class FileUploadService implements FileUploadServiceInterface
{
    protected array $imageMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public function uploadImage(UploadedFile $file, string $directory = 'uploads/images', string $disk = 'public'): string
    {
        if (! in_array((string) $file->getMimeType(), $this->imageMimeTypes, true)) {
            throw new InvalidArgumentException('Uploaded file must be a valid image.');
        }

        return $this->storeFile($file, $directory, $disk);
    }

    public function uploadDocument(UploadedFile $file, string $directory = 'uploads/documents', string $disk = 'public'): string
    {
        if (! $file->isValid()) {
            throw new InvalidArgumentException('Uploaded document is invalid.');
        }

        return $this->storeFile($file, $directory, $disk);
    }

    public function deleteFile(string $path, string $disk = 'public'): bool
    {
        $path = trim($path);

        if ($path === '') {
            throw new InvalidArgumentException('File path is required.');
        }

        if (! $this->fileExists($path, $disk)) {
            return false;
        }

        if ($disk === 'public_path') {
            return File::delete(public_path($path));
        }

        return Storage::disk($disk)->delete($path);
    }

    public function fileExists(string $path, string $disk = 'public'): bool
    {
        $path = trim($path);

        if ($path === '') {
            return false;
        }

        if ($disk === 'public_path') {
            return File::exists(public_path($path));
        }

        return Storage::disk($disk)->exists($path);
    }

    public function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->extension();
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $name = Str::slug($name) ?: 'file';

        return sprintf('%s-%s.%s', $name, Str::uuid(), strtolower($extension));
    }

    protected function storeFile(UploadedFile $file, string $directory, string $disk): string
    {
        if (! $file->isValid()) {
            throw new InvalidArgumentException('Uploaded file is invalid.');
        }

        $directory = trim($directory, '/');

        if ($directory === '') {
            throw new InvalidArgumentException('Upload directory is required.');
        }

        if ($disk === 'public_path') {
            $destination = public_path($directory);

            if (! File::isDirectory($destination)) {
                File::makeDirectory($destination, 0755, true);
            }

            $filename = $this->generateUniqueFilename($file);
            $file->move($destination, $filename);

            return $directory.'/'.$filename;
        }

        return $file->storeAs($directory, $this->generateUniqueFilename($file), $disk);
    }
}
