<?php

namespace Cabinet\Types;

class Document implements \Cabinet\FileType
{
    use \Cabinet\Types\Concerns\StringableAsSlug;

    public function name(): string
    {
        return __('cabinet::document');
    }

    public function slug(): string
    {
        return 'document';
    }

    public static function supportedMimeTypes(): array
    {
        return [
            'application/pdf',

            // Microsoft Office
            'application/msword',
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
            'application/vnd.oasis.opendocument.text-master',
            'application/vnd.oasis.opendocument.text-template',
            'application/vnd.oasis.opendocument.spreadsheet-template',
            'application/vnd.oasis.opendocument.presentation-template',
            'application/vnd.oasis.opendocument.graphics-template',
            'application/vnd.oasis.opendocument.chart-template',
            'application/vnd.oasis.opendocument.image-template',
            'application/vnd.oasis.opendocument.formula-template',
            'application/vnd.oasis.opendocument.text-web',
            'application/vnd.oasis.opendocument.text-flat-xml',
            'application/vnd.oasis.opendocument.spreadsheet-flat-xml',
            'application/vnd.oasis.opendocument.presentation-flat-xml',
            'application/vnd.oasis.opendocument.graphics-flat-xml',
            'application/vnd.oasis.opendocument.chart-flat-xml',
            'application/vnd.oasis.opendocument.image-flat-xml',
            'application/vnd.oasis.opendocument.formula-flat-xml',
            'application/vnd.oasis.opendocument.text-master-template',
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
}
