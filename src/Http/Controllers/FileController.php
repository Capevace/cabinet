<?php

namespace Cabinet\Http\Controllers;

use Cabinet\Facades\Cabinet;
use Cabinet\RollingSignature\Signature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FileController
{
    protected const CACHE_CONTROL_FOUND = 'public, max-age=86400, stale-while-revalidate=604800';
    protected const CACHE_CONTROL_MISSING = 'no-store';

    /**
     * Validate the rolling signature on the incoming request.
     */
    protected function validateSignature(Request $request): void
    {
        if (!Signature::validate($request->fullUrl())) {
            abort(403, 'Invalid signature');
        }
    }

    /**
     * Map public URL source slugs back to internal source names.
     */
    protected function internalSourceSlug(string $publicSlug): string
    {
        return match ($publicSlug) {
            'media' => 'spatie-media',
            default => $publicSlug,
        };
    }

    /**
     * Resolve a file from source + id, aborting if not found.
     */
    protected function resolveFile(string $source, string $id): \Cabinet\File
    {
        $file = Cabinet::file($this->internalSourceSlug($source), $id);

        if ($file === null) {
            abort(404, 'File not found');
        }

        return $file;
    }

    /**
     * Map the public ?variant= query param to an internal conversion name.
     *
     * @return string|null The internal conversion name, or null for original/no-conversion.
     */
    protected function resolveVariant(\Cabinet\File $file, ?string $variant): ?string
    {
        $variant ??= 'normal';

        return match ($variant) {
            'tiny' => config('cabinet.spatie_media_library.tiny_preview_conversion', 'tiny-thumbnail'),
            'normal' => config('cabinet.spatie_media_library.preview_conversion', 'thumbnail'),
            default => config('cabinet.spatie_media_library.preview_conversion', 'thumbnail'),
        };
    }

    /**
     * Generate a presigned URL for the file, with optional conversion.
     */
    protected function generateUrl(\Cabinet\File $file, ?string $conversion = null): ?string
    {
        if ($conversion !== null) {
            return $file->url($conversion);
        }

        return $file->url();
    }

    /**
     * Return a redirect response with cache headers for found files.
     */
    protected function redirectToUrl(string $url): RedirectResponse
    {
        return redirect()->to($url, 302)
            ->header('Cache-Control', self::CACHE_CONTROL_FOUND);
    }

    /**
     * Return a redirect to a placeholder for missing files.
     */
    protected function redirectToPlaceholder(): RedirectResponse
    {
        $placeholder = config('cabinet.placeholder_url', '/placeholders/document.svg');

        return redirect()->to($placeholder, 307)
            ->header('Cache-Control', self::CACHE_CONTROL_MISSING);
    }

    /**
     * Serve a thumbnail via 302 redirect to a presigned storage URL.
     *
     * Supports ?variant=tiny|normal (defaults to normal).
     */
    public function thumbnail(Request $request, string $source, string $id): RedirectResponse
    {
        $this->validateSignature($request);

        $file = $this->resolveFile($source, $id);
        $conversion = $this->resolveVariant($file, $request->query('variant'));
        $url = $this->generateUrl($file, $conversion);
        // dd($file, $conversion, $url);

        if ($url === null) {
            return $this->redirectToPlaceholder();
        }

        return $this->redirectToUrl($url);
    }

    /**
     * Serve the original file via 302 redirect to a presigned storage URL.
     */
    public function original(Request $request, string $source, string $id): RedirectResponse
    {
        $this->validateSignature($request);

        $file = $this->resolveFile($source, $id);
        $url = $this->generateUrl($file);

        if ($url === null) {
            return $this->redirectToPlaceholder();
        }

        return $this->redirectToUrl($url);
    }
}
