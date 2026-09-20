<?php

declare(strict_types=1);

namespace App\Domain\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Disk-abstracted media storage foundation (R18). MVP stores on the local `public`
 * disk; the disk is injected so switching to object storage/CDN later is a config
 * change, not a rewrite (Principle IV). Naming is deterministic and non-guessable
 * (`products/{ulid}.{ext}`), and uploads are constrained to safe image types.
 *
 * Actual image resizing / WebP thumbnail generation is deferred to Phase N (T125);
 * this class owns validation rules, the naming/path strategy, and store/delete/url.
 */
final class MediaService
{
    /** Allowed upload extensions (safe, non-executable image types). */
    public const ALLOWED_EXTENSIONS = ['jpeg', 'jpg', 'png', 'webp'];

    /** Maximum upload size in kilobytes (4 MB). */
    public const MAX_KILOBYTES = 4096;

    public function __construct(
        private readonly string $disk = 'public',
    ) {
    }

    /**
     * Reusable Form Request validation rules for an image upload — keeps the mime/
     * size constraints defined once and shared by controllers/Filament.
     *
     * @return list<string>
     */
    public static function imageRules(bool $required = true): array
    {
        return array_values(array_filter([
            $required ? 'required' : 'nullable',
            'image',
            'mimes:jpeg,png,webp',
            'max:'.self::MAX_KILOBYTES,
        ]));
    }

    /** Store an uploaded image under a deterministic ULID name; returns the stored path. */
    public function store(UploadedFile $file, string $directory = 'products'): string
    {
        $extension = strtolower(
            $file->getClientOriginalExtension() ?: ($file->guessExtension() ?? 'jpg')
        );

        $directory = trim($directory, '/');
        $filename = (string) Str::ulid().'.'.$extension;

        $file->storeAs($directory, $filename, ['disk' => $this->disk]);

        return $directory.'/'.$filename;
    }

    /** Deterministic thumbnail path for a stored original (generation lands in T125). */
    public function thumbnailPath(string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $withoutExt = substr($path, 0, -(strlen($extension) + 1));

        return $withoutExt.'_thumb.'.$extension;
    }

    public function delete(?string $path): void
    {
        if ($path !== null && $path !== '' && Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
    }

    public function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return Storage::disk($this->disk)->url($path);
    }
}
