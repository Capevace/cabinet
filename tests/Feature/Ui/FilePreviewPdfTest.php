<?php

namespace Cabinet\Tests\Feature\Ui;

use Cabinet\Tests\TestCase;

class FilePreviewPdfTest extends TestCase
{
    public function test_pdf_preview_uses_inline_preview_url_instead_of_original_file_url(): void
    {
        $viewPath = __DIR__ . '/../../../../cabinet-ui/resources/views/components/forms/file-preview.blade.php';
        $content = file_get_contents($viewPath);

        // The PDF case should use the inline preview URL instead of the raw original file url()
        $this->assertStringContainsString("case('pdf')", $content);

        // Extract the PDF block
        $pdfCasePattern = '/@case\(\'pdf\'\)(.*?)@break/s';
        $this->assertMatchesRegularExpression($pdfCasePattern, $content);

        preg_match($pdfCasePattern, $content, $matches);
        $pdfBlock = $matches[1] ?? '';

        // The PDF block should reference getPreviewUrl so the browser renders inline
        // instead of downloading the original file
        $this->assertStringContainsString('getPreviewUrl', $pdfBlock);

        // It must not use the raw original file URL which triggers a download
        $this->assertStringNotContainsString('$file->url()', $pdfBlock);
    }
}
