<?php

return [
    'directory_model' => \Cabinet\Models\Directory::class,
    'file_ref_model' => \Cabinet\Models\FileRef::class,
    'basic_file_model' => \Cabinet\Models\BasicFile::class,


    // Set the collection files will be uplaoded to when using spatie/medialibrary.
    // Fallback is default, which is also used by the package internally.
    'spatie_media_library' => [
        'collection_name' => 'default',
        'preserve_original' => true,
        'disk' => 'public',
        'conversions_disk' => 'public',
        'preview_conversion' => 'thumbnail'
    ]
];
