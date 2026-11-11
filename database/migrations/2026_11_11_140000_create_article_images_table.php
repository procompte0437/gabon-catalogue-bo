<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Les photos des articles sont stockées dans la base (et non sur le disque du serveur),
| pour survivre aux redéploiements Railway. articles.image_path vaut alors « db/<key>.jpg ».
| La garde hasTable permet de créer la table au préalable avec le script SQL.
*/
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('article_images')) {
            return;
        }

        Schema::create('article_images', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('mime', 40)->default('image/jpeg');
            $table->unsignedInteger('size')->default(0);
            $table->longText('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_images');
    }
};
