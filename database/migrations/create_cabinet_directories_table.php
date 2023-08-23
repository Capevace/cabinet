<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cabinet:directories', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name');

            $table->string('translation_key')
                ->nullable();

            $table->boolean('is_protected')
                ->default(false);

            $table->timestamps();
        });

		Schema::table('cabinet:directories', function (Blueprint $table) {
			$table->foreignUuid('parent_directory_id')
                ->nullable()
                ->constrained('cabinet:directories')
                ->cascadeOnDelete();
		});
    }

    public function down(): void
    {
		Schema::table('cabinet:directories', function (Blueprint $table) {
			$table->dropForeign(['parent_directory_id']);
		});

        Schema::dropIfExists('cabinet:directories');
    }
};
