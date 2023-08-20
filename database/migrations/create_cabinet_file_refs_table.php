<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cabinet:file_refs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->nullableUuidMorphs('attached_to');
            $table->string('attached_as')
                ->nullable();
            $table->unsignedInteger('attached_order')
                ->nullable();

            $table->string('source');

            $table->nullableUuidMorphs('model');

            $table->string('disk')
                ->nullable();
            $table->string('path', 1024)
                ->nullable();

            $table->index(['attached_to_type', 'attached_to_id']);
            $table->index(['source', 'disk', 'path']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cabinet:file_refs');
    }
};
