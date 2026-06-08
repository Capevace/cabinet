<?php

return [
    'directory_model' => \Cabinet\Models\Directory::class,
    'file_ref_model' => \Cabinet\Models\FileRef::class,
    'basic_file_model' => \Cabinet\Models\BasicFile::class,

    'auto_delete_references' => true,

    'max_file_size_kb' => 1024 * 10, // 10MB

    'file' => [
        'enabled' => true,
        'expires_after_minutes' => 60 * 24 * 2, // 2 days
        'route_prefix' => '/files',
        'middleware' => ['web'],
    ],

    'download' => [
        'route' => '/cabinet/download',
        'middleware' => ['web', 'signed'],
        'expires_after_minutes' => 5,
    ],

    // Date format used when displaying file creation dates.
    // Defaults to European style (day month year).
    'date_format' => 'd. F Y',
 
    // Set the collection files will be uplaoded to when using spatie/medialibrary.
    // Fallback is default, which is also used by the package internally.
    'spatie_media_library' => [
        'collection_name' => 'default',
        'preserve_original' => true,
        'disk' => 'public',
        'conversions_disk' => 'public',

        'default_conversion' => '',
        'preview_conversion' => 'thumbnail',
        'tiny_preview_conversion' => 'tiny-thumbnail',
		'default_expiration_minutes' => 60,
    ]
];
