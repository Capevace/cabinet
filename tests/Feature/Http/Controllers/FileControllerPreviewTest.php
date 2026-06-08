<?php

namespace Cabinet\Tests\Feature\Http\Controllers;

use Cabinet\Facades\Cabinet;
use Cabinet\File;
use Cabinet\Http\Controllers\FileController;
use Cabinet\RollingSignature\Signature;
use Cabinet\Tests\TestCase;
use Cabinet\Types\PDF;
use Illuminate\Http\Request;
use Mockery;

class FileControllerPreviewTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_preview_route_is_registered(): void
    {
        $this->assertTrue(
            app('router')->has('cabinet.files.preview'),
            'The cabinet.files.preview route should be registered'
        );
    }

    public function test_preview_streams_file_with_inline_content_disposition(): void
    {
        $controller = new FileController();

        // Create a mock file
        $file = new File(
            id: 'test-pdf-id',
            source: 'spatie-media',
            type: new PDF('application/pdf'),
            name: 'Titelblatt',
            slug: 'titelblatt.pdf',
            mimeType: 'application/pdf',
            size: 12345,
            previewUrl: null,
            createdAt: now(),
        );

        // Mock Cabinet facade
        Cabinet::shouldReceive('file')
            ->with('spatie-media', 'test-pdf-id')
            ->once()
            ->andReturn($file);

        Cabinet::shouldReceive('generateFileUrl')
            ->with($file, null)
            ->once()
            ->andReturn('php://memory');

        // Generate a valid signed URL for the preview route
        $signedUrl = Signature::route('cabinet.files.preview', [
            'source' => 'media',
            'id' => 'test-pdf-id',
        ])->signedUrl();

        // Make a request to the preview route
        $response = $this->get($signedUrl);

        // The response should stream successfully with inline disposition
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'inline; filename="Titelblatt.pdf"');
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_preview_rejects_invalid_signature(): void
    {
        $response = $this->get('/files/media/test-id/preview?signature=invalid');

        $response->assertStatus(403);
    }
}
