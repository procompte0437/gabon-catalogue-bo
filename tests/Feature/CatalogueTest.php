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

    public function test_login_form_posts_over_https_behind_the_proxy(): void
    {
        $this->withServerVariables([
            'HTTPS' => 'off',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '443',
        ])->get('/connexion')
            ->assertOk()
            ->assertSee('method="POST" action="https://', false)
            ->assertDontSee('method="POST" action="http://', false);
    }

    public function test_admin_can_rename_a_rubrique(): void
    {
        $data = $this->seedMini();
        $admin = User::factory()->create();

        $this->actingAs($admin)->put('/admin/rubriques/'.$data['rubrique']->id, [
            'name' => 'Tissus',
        ])->assertRedirect();

        $this->assertSame('Tissus', $data['rubrique']->fresh()->name);
    }

    public function test_admin_sets_links_and_visibility_per_sous_rubrique(): void
    {
        $data = $this->seedMini();
        $admin = User::factory()->create();

        $this->actingAs($admin)->put('/admin/sous-rubriques/'.$data['sous']->id, [
            'name' => 'Chambres',
            'code' => '1234',
            'is_linked' => '1',
            'is_visible' => '1',
        ])->assertRedirect();

        $this->assertTrue($data['sous']->fresh()->is_linked);
        $this->assertTrue($data['rubrique']->fresh()->is_linked);
        $this->getJson('/api/sous-rubriques/'.$data['sous']->id.'/articles')
            ->assertJsonPath('rubrique.is_linked', true);

        $this->actingAs($admin)->put('/admin/sous-rubriques/'.$data['sous']->id, [
            'name' => 'Chambres',
            'code' => '1234',
            'is_linked' => '0',
            'is_visible' => '0',
        ])->assertRedirect();

        $this->assertFalse($data['rubrique']->fresh()->is_linked);
        $this->getJson('/api/rubriques/'.$data['rubrique']->id.'/sous-rubriques')
            ->assertJsonCount(0, 'sous_rubriques');
    }

    public function test_admin_pages_show_link_and_visible_boxes(): void
    {
        $data = $this->seedMini();
        $admin = User::factory()->create();

        $this->actingAs($admin)->get('/admin/rubriques/'.$data['linked']->id)
            ->assertOk()->assertSee('name="is_visible"', false)->assertSee('name="is_linked" value="1" checked', false);
        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/articles/'.$data['linkedArticle']->id.'/modifier')->assertOk()->assertSee('Lien du site');
    }

    public function test_admin_code_adds_and_deletes_a_rubrique(): void
    {
        $data = $this->seedMini();
        $master = \App\Models\Setting::masterCode();

        $this->postJson('/api/admin/rubriques', ['code' => '9999', 'name' => 'Pharmacies'])->assertStatus(422);

        $id = $this->postJson('/api/admin/rubriques', ['code' => $master, 'name' => 'Pharmacies', 'sous_count' => 3])
            ->assertCreated()->assertJsonPath('rubrique.name', 'Pharmacies')->json('rubrique.id');

        $this->getJson('/api/rubriques/'.$id.'/sous-rubriques')->assertJsonCount(3, 'sous_rubriques');
        $sousIds = SousRubrique::query()->where('rubrique_id', $id)->pluck('id');
        $this->assertCount((int) config('catalogue.sous_rubriques'), $sousIds);
        $this->assertSame($sousIds->count() * (int) config('catalogue.articles'), Article::query()->whereIn('sous_rubrique_id', $sousIds)->count());

        $before = $data['linked']->fresh()->position;
        $this->deleteJson('/api/admin/rubriques/'.$data['rubrique']->id, ['code' => $master])->assertOk();

        $this->assertNull(Rubrique::query()->find($data['rubrique']->id));
        $this->assertNull(Article::query()->find($data['article']->id));
        $this->assertSame($before - 1, $data['linked']->fresh()->position);
    }

    public function test_long_links_are_accepted(): void
    {
        $data = $this->seedMini();
        $link = 'https://www.google.com/search?q='.str_repeat('hotel+libreville+', 60);

        $this->postJson('/api/articles/'.$data['linkedArticle']->id, [
            'code' => '0000',
            'title' => 'Hôtel',
            'link' => $link,
        ])->assertOk();

        $this->assertSame($link, $data['linkedArticle']->fresh()->link);
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
            'is_linked' => true,
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
