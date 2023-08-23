<?php

return [
    'directory_model' => \Cabinet\Models\Directory::class,
    'file_ref_model' => \Cabinet\Models\FileRef::class,
    'basic_file_model' => \Cabinet\Models\BasicFile::class,

    'auto_delete_references' => true,

    // Set the collection files will be uplaoded to when using spatie/medialibrary.
    // Fallback is default, which is also used by the package internally.
    'spatie_media_library' => [
        'collection_name' => 'default',
        'preserve_original' => true,
        'disk' => 'public',
        'conversions_disk' => 'public',

        'default_conversion' => '',
        'preview_conversion' => 'thumbnail'
    ]
];
