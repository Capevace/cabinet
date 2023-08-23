<?php

namespace Cabinet\Types;

use Cabinet\Types\Concerns\StringableAsSlug;
use Cabinet\Types\Concerns\UsesDefaultIcon;
use Cabinet\Types\Concerns\WithMime;

class Document implements \Cabinet\FileType
{
    use StringableAsSlug;
    use UsesDefaultIcon;
    use WithMime;

    public function name(): string
    {
        return ($mime = $this->formattedMimeType())
            ? __('cabinet::files.document') . " ({$mime})"
            : __('cabinet::files.document');
    }

    public function slug(): string
    {
        return 'document';
    }

    public static function supportedMimeTypes(): array
    {
        return [
            // Text file
            'text/plain',

            // CSV
            'text/csv',
            'text/x-csv',
            'application/csv',

            // JSON
            'application/json',
            'application/x-json',

            // XML
            'application/xml',
            'text/xml',

            // Microsoft Office
            'application/msword',
            'application/vnd.ms-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',

            'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.spreadsheet',
            'application/vnd.oasis.opendocument.presentation',
            'application/vnd.oasis.opendocument.graphics',
            'application/vnd.oasis.opendocument.chart',
            'application/vnd.oasis.opendocument.database',
            'application/vnd.oasis.opendocument.formula',
            'application/vnd.oasis.opendocument.image',
            'application/vnd.sun.xml.writer',
            'application/vnd.sun.xml.writer.template',
            'application/vnd.sun.xml.calc',
            'application/vnd.sun.xml.calc.template',
            'application/vnd.sun.xml.draw',
            'application/vnd.sun.xml.draw.template',
            'application/vnd.sun.xml.impress',
            'application/vnd.sun.xml.impress.template',
            'application/vnd.sun.xml.writer.global',
            'application/vnd.sun.xml.math',
            'application/vnd.stardivision.writer',
            'application/vnd.stardivision.writer-global',
            'application/vnd.stardivision.calc',
            'application/vnd.stardivision.impress',
            'application/vnd.stardiv',

            // Mac OS
            'application/vnd.apple.pages',
            'application/vnd.apple.numbers',
            'application/vnd.apple.keynote',

            // Mac OS (iWork)
            'application/x-iwork-pages-sffpages',
            'application/x-iwork-numbers-sffnumbers',
            'application/x-iwork-keynote-sffkey',
        ];
    }

    public function formattedMimeType(): ?string
    {
        return match ($this->mime) {
            'text/plain' => 'TXT',

            'text/csv',
            'text/x-csv',
            'application/csv' => 'CSV',

            'application/json',
            'application/x-json' => 'JSON',

            'application/xml',
            'text/xml' => 'XML',

            'application/msword' => 'DOC',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'DOCX',
            'application/vnd.ms-excel' => 'XLS',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'XLSX',
            'application/vnd.ms-powerpoint' => 'PPT',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'PPTX',

            'application/vnd.oasis.opendocument.text' => 'ODT',
            'application/vnd.oasis.opendocument.spreadsheet' => 'ODS',
            'application/vnd.oasis.opendocument.presentation' => 'ODP',
            'application/vnd.oasis.opendocument.graphics' => 'ODG',
            'application/vnd.oasis.opendocument.chart' => 'ODC',
            'application/vnd.oasis.opendocument.database' => 'ODB',
            'application/vnd.oasis.opendocument.formula' => 'ODF',
            'application/vnd.oasis.opendocument.image' => 'ODI',

            'application/vnd.sun.xml.writer' => 'SXW',
            'application/vnd.sun.xml.writer.template' => 'STW',
            'application/vnd.sun.xml.calc' => 'SXC',
            'application/vnd.sun.xml.calc.template' => 'STC',
            'application/vnd.sun.xml.draw' => 'SXD',
            'application/vnd.sun.xml.draw.template' => 'STD',
            'application/vnd.sun.xml.impress' => 'SXI',
            'application/vnd.sun.xml.impress.template' => 'STI',
            'application/vnd.sun.xml.writer.global' => 'SXI',
            'application/vnd.sun.xml.math' => 'SXM',
            'application/vnd.stardivision.writer' => 'SDW',
            'application/vnd.stardivision.writer-global' => 'SGW',
            'application/vnd.stardivision.calc' => 'SDC',
            'application/vnd.stardivision.impress' => 'SDD',
            'application/vnd.stardiv' => 'SDF',

            'application/vnd.apple.pages',
            'application/x-iwork-pages-sffpages' => 'Apple Pages',
            'application/vnd.apple.numbers',
            'application/x-iwork-numbers-sffnumbers' => 'Apple Numbers',
            'application/vnd.apple.keynote',
            'application/x-iwork-keynote-sffkey' => 'Apple Keynote',

            default => $this->mime,
        };
    }
}
