<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogueSeeder extends Seeder
{
    public function run(): void
    {
        if (Setting::get('app_name') === null) {
            Setting::put('app_name', 'Gabon Catalogue');
        }

        if (Setting::get('whatsapp_display') === null) {
            Setting::put('whatsapp_display', '062-70-47-45');
        }

        if (DB::table('rubriques')->exists()) {
            return;
        }

        $rubriques = (int) config('catalogue.rubriques');
        $sousCount = (int) config('catalogue.sous_rubriques');
        $articleCount = (int) config('catalogue.articles');
        $linked = (int) config('catalogue.linked_position');
        $code = (string) config('catalogue.default_code');
        $now = now()->toDateTimeString();

        $rubriqueRows = [];
        for ($position = 1; $position <= $rubriques; $position++) {
            $rubriqueRows[] = [
                'position' => $position,
                'name' => "Rubrique {$position}",
                'is_linked' => $position === $linked ? 1 : 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('rubriques')->insert($rubriqueRows);

        $rubriqueIds = DB::table('rubriques')->orderBy('position')->pluck('id');
        $sousRows = [];
        $linkedId = DB::table('rubriques')->where('position', $linked)->value('id');
        foreach ($rubriqueIds as $rubriqueId) {
            for ($position = 1; $position <= $sousCount; $position++) {
                $sousRows[] = [
                    'rubrique_id' => $rubriqueId,
                    'is_linked' => $rubriqueId === $linkedId ? 1 : 0,
                    'position' => $position,
                    'name' => "Sous-rubrique {$position}",
                    'code' => $code,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        foreach (array_chunk($sousRows, 200) as $chunk) {
            DB::table('sous_rubriques')->insert($chunk);
        }

        $sousIds = DB::table('sous_rubriques')->orderBy('id')->pluck('id');
        $articleRows = [];
        foreach ($sousIds as $sousId) {
            for ($position = 1; $position <= $articleCount; $position++) {
                $articleRows[] = [
                    'sous_rubrique_id' => $sousId,
                    'position' => $position,
                    'title' => "Article {$position}",
                    'image_path' => null,
                    'link' => null,
                    'code' => $code,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($articleRows) === 400) {
                    DB::table('articles')->insert($articleRows);
                    $articleRows = [];
                }
            }
        }
        if ($articleRows !== []) {
            DB::table('articles')->insert($articleRows);
        }
    }
}
