<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Rubrique;
use App\Models\Setting;
use App\Models\SousRubrique;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalogue_does_not_expose_client_codes(): void
    {
        $data = $this->seedMini();

        $this->getJson('/api/catalogue')
            ->assertOk()
            ->assertJsonPath('app_name', 'Gabon Catalogue')
            ->assertJsonPath('whatsapp_display', '062-70-47-45')
            ->assertJsonPath('whatsapp_link', 'https://wa.me/24162704745')
            ->assertJsonPath('rubriques.0.name', 'Rubrique 1')
            ->assertJsonMissing(['code' => '0000']);

        $articles = $this->getJson('/api/sous-rubriques/'.$data['sous']->id.'/articles')
            ->assertOk()
            ->assertJsonPath('articles.0.title', 'Article 1')
            ->json('articles.0');

        $this->assertArrayNotHasKey('code', $articles);
    }

    public function test_client_can_rename_a_subcategory_with_its_code(): void
    {
        $data = $this->seedMini();

        $this->putJson('/api/sous-rubriques/'.$data['sous']->id, [
            'code' => '9999',
            'name' => 'Piraté',
        ])->assertStatus(422);

        $this->assertSame('Sous-rubrique 1', $data['sous']->fresh()->name);

        $this->putJson('/api/sous-rubriques/'.$data['sous']->id, [
            'code' => '0000',
            'name' => 'Mode',
        ])->assertOk()->assertJsonPath('sous_rubrique.name', 'Mode');

        $this->assertSame('Mode', $data['sous']->fresh()->name);
        $this->assertSame('0000', $data['sous']->fresh()->code);
    }

    public function test_client_updates_an_article_without_changing_its_code(): void
    {
        $data = $this->seedMini();

        $this->postJson('/api/articles/'.$data['article']->id, [
            'code' => '1234',
            'title' => 'Pagne',
            'link' => 'https://exemple.com',
        ])->assertOk()->assertJsonPath('article.title', 'Pagne');

        $article = $data['article']->fresh();
        $this->assertSame('Pagne', $article->title);
        $this->assertSame('1234', $article->code);
        $this->assertNull($article->link);
    }

    public function test_linked_rubrique_accepts_a_website(): void
    {
        $data = $this->seedMini();

        $this->postJson('/api/articles/'.$data['linkedArticle']->id, [
            'code' => '0000',
            'title' => 'Boutique',
            'link' => 'exemple.com',
        ])->assertOk()->assertJsonPath('article.link', 'https://exemple.com');

        $this->assertSame('https://exemple.com', $data['linkedArticle']->fresh()->link);
    }

    public function test_guest_is_sent_to_the_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/connexion');
        $this->get('/connexion')->assertOk()->assertSee('Connexion administrateur');
    }

    public function test_admin_can_rename_a_rubrique_and_mark_it_as_linked(): void
    {
        $data = $this->seedMini();
        $admin = User::factory()->create();

        $this->actingAs($admin)->put('/admin/rubriques/'.$data['rubrique']->id, [
            'name' => 'Tissus',
            'is_linked' => '1',
        ])->assertRedirect();

        $this->assertSame('Tissus', $data['rubrique']->fresh()->name);
        $this->assertTrue($data['rubrique']->fresh()->is_linked);
        $this->assertFalse($data['linked']->fresh()->is_linked);
    }

    public function test_admin_can_sign_in(): void
    {
        User::factory()->create([
            'email' => 'admin@gabon-catalogue.local',
            'password' => '0110',
        ]);

        $this->post('/connexion', [
            'email' => 'admin@gabon-catalogue.local',
            'password' => '0110',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get('/admin')->assertOk()->assertSee('Tableau de bord');
    }

    /**
     * @return array{rubrique: Rubrique, sous: SousRubrique, article: Article, linked: Rubrique, linkedArticle: Article}
     */
    private function seedMini(): array
    {
        Setting::put('app_name', 'Gabon Catalogue');
        Setting::put('whatsapp_display', '062-70-47-45');

        $rubrique = Rubrique::query()->create([
            'position' => 1,
            'name' => 'Rubrique 1',
            'is_linked' => false,
        ]);
        $linked = Rubrique::query()->create([
            'position' => 9,
            'name' => 'Rubrique 9',
            'is_linked' => true,
        ]);
        $sous = SousRubrique::query()->create([
            'rubrique_id' => $rubrique->id,
            'position' => 1,
            'name' => 'Sous-rubrique 1',
            'code' => '0000',
        ]);
        $article = Article::query()->create([
            'sous_rubrique_id' => $sous->id,
            'position' => 1,
            'title' => 'Article 1',
            'code' => '1234',
        ]);
        $linkedSous = SousRubrique::query()->create([
            'rubrique_id' => $linked->id,
            'position' => 1,
            'name' => 'Sites',
            'code' => '0000',
        ]);
        $linkedArticle = Article::query()->create([
            'sous_rubrique_id' => $linkedSous->id,
            'position' => 1,
            'title' => 'Site 1',
            'code' => '0000',
        ]);

        return compact('rubrique', 'sous', 'article', 'linked', 'linkedArticle');
    }
}
