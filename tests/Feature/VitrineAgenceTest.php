<?php

namespace Tests\Feature;

use App\Models\Agence;
use App\Models\Media;
use App\Models\Ticket;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Support\DonneesBus;
use Tests\TestCase;

/**
 * Vitrine des agences (logo, photos, slogan, couleur) et page publique du
 * billet ouverte en scannant son QR code.
 */
class VitrineAgenceTest extends TestCase
{
    use RefreshDatabase, DonneesBus;

    protected function setUp(): void
    {
        parent::setUp();
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('À lancer sur MySQL/MariaDB.');
        }
        config(['momo.simulation' => true]);
    }

    public function test_la_vitrine_est_reservee_a_l_admin_de_l_agence(): void
    {
        $depart = $this->creerDepart();
        $this->get('/admin/vitrine')->assertRedirect();

        $this->actingAs($this->personnel('Agent', $depart->agence_id), 'web');
        $this->get('/admin/vitrine')->assertForbidden();

        $this->actingAs($this->personnel('Admin', $depart->agence_id), 'web');
        $this->get('/admin/vitrine')->assertOk()->assertSee('Vitrine');
    }

    public function test_l_admin_configure_sa_vitrine_et_l_api_la_publie(): void
    {
        $depart = $this->creerDepart();
        $this->actingAs($this->personnel('Admin', $depart->agence_id), 'web');

        $this->put('/admin/vitrine', ['slogan' => 'Voyagez serein', 'couleur' => '#f7a21b'])->assertSessionHasNoErrors();
        $this->post('/admin/vitrine/logo', ['image' => UploadedFile::fake()->image('logo.png', 300, 300)])->assertSessionHasNoErrors();
        $this->post('/admin/vitrine/logo', ['image' => UploadedFile::fake()->image('logo2.png', 300, 300)])->assertSessionHasNoErrors();
        $this->post('/admin/vitrine/photos', ['image' => UploadedFile::fake()->image('bus.jpg', 800, 500)])->assertSessionHasNoErrors();

        $agence = Agence::findOrFail($depart->agence_id);
        $this->assertSame('#F7A21B', $agence->couleur);
        $this->assertSame(1, Media::where('type', 'logo')->count(), 'L\'ancien logo est remplacé.');

        $vitrine = collect($this->getJson('/api/agences')->assertOk()->json('agences'))->firstWhere('id', $agence->id)['vitrine'];
        $this->assertSame('Voyagez serein', $vitrine['slogan']);
        $this->assertSame('#F7A21B', $vitrine['couleur']);
        $this->assertCount(1, $vitrine['photos']);
        $this->assertNotNull($vitrine['logo_url']);

        // L'image est servie avec un cache longue durée
        $chemin = parse_url($vitrine['logo_url'], PHP_URL_PATH);
        $reponse = $this->get($chemin)->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->assertStringContainsString('max-age=31536000', $reponse->headers->get('Cache-Control'));
        $this->get($chemin, ['If-None-Match' => $reponse->headers->get('ETag')])->assertStatus(304);
    }

    public function test_une_agence_ne_supprime_pas_les_photos_d_une_autre(): void
    {
        $depart = $this->creerDepart();
        $autre = Agence::create(['nom_agence' => 'Autre Agence', 'statut' => 'Actif', 'date_partenariat' => now(), 'superadmin_id' => Agence::findOrFail($depart->agence_id)->superadmin_id]);
        $photo = Media::create(['agence_id' => $autre->id, 'type' => 'photo', 'mime' => 'image/png', 'taille' => 3, 'contenu' => 'abc']);

        $this->actingAs($this->personnel('Admin', $depart->agence_id), 'web');
        $this->delete("/admin/vitrine/photos/{$photo->id}")->assertNotFound();
        $this->assertTrue(Media::whereKey($photo->id)->exists());
    }

    public function test_un_fichier_qui_n_est_pas_une_image_est_refuse(): void
    {
        $depart = $this->creerDepart();
        $this->actingAs($this->personnel('Admin', $depart->agence_id), 'web');

        $this->post('/admin/vitrine/logo', ['image' => UploadedFile::fake()->create('virus.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('image');
        $this->assertSame(0, Media::count());
    }

    public function test_le_qr_du_billet_ouvre_sa_page_avec_le_logo_de_l_agence(): void
    {
        $depart = $this->creerDepart();
        $ticket = app(ReservationService::class)
            ->reserver($depart->id, [['nom' => 'OKOMBI', 'prenom' => 'Junior']], 'MTN', 'R-QR', payeImmediatement: true)
            ->first();
        $agence = Agence::findOrFail($depart->agence_id);

        $this->get('/billet/' . $ticket->qr_code_token)
            ->assertOk()
            ->assertSee($agence->nom_agence)
            ->assertSee($ticket->reference_ticket)
            ->assertSee('Junior O.')
            ->assertDontSee('OKOMBI')                 // nom masqué
            ->assertSee($agence->logoPublicUrl(), false);

        $this->get('/billet/' . str_repeat('a', 64))->assertNotFound();
    }

    public function test_l_agent_scanne_l_url_du_billet(): void
    {
        $depart = $this->creerDepart();
        $ticket = app(ReservationService::class)
            ->reserver($depart->id, [['nom' => 'A']], 'MTN', 'R-SCAN', payeImmediatement: true)
            ->first();

        $this->assertSame($ticket->qr_code_token, Ticket::jetonDepuisCode($ticket->urlPublique()));
        $this->assertSame($ticket->qr_code_token, Ticket::jetonDepuisCode('MBL:' . $ticket->qr_code_token));
        $this->assertNull(Ticket::jetonDepuisCode($ticket->reference_ticket));

        Sanctum::actingAs($this->personnel('Agent', $depart->agence_id));
        $this->getJson('/api/agent/ticket/' . rawurlencode('MBL:' . $ticket->qr_code_token))->assertOk();
    }
}
