<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rubriques', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('position')->unique();
            $table->string('name', 40);
            $table->boolean('is_linked')->default(false);
            $table->timestamps();
        });

        Schema::create('sous_rubriques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubrique_id')->constrained('rubriques')->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->string('name', 40);
            $table->string('code', 4);
            $table->timestamps();

            $table->unique(['rubrique_id', 'position']);
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sous_rubrique_id')->constrained('sous_rubriques')->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->string('title', 20);
            $table->string('image_path')->nullable();
            $table->string('link', 500)->nullable();
            $table->string('code', 4);
            $table->timestamps();

            $table->unique(['sous_rubrique_id', 'position']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('sous_rubriques');
        Schema::dropIfExists('rubriques');
    }
};
