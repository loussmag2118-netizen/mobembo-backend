<?php

namespace Tests\Feature;

use App\Models\Agence;
use App\Models\CanadianUser;
use App\Models\Flotte;
use App\Models\Paiement;
use App\Models\Reservation;
use App\Models\SuperAdmin;
use App\Models\Utilisateur;
use App\Models\Ville;
use App\Models\Vol;
use App\Services\ReservationVolService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Compagnies aériennes : partenaires de type « aerien » gérés comme les
 * compagnies de bus, espace d'administration cloisonné par compagnie,
 * vente et paiement des vols dans l'application.
 */
class EspaceAerienTest extends TestCase
{
    use RefreshDatabase;

    private SuperAdmin $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('À lancer sur MySQL/MariaDB.');
        }
        $this->superAdmin = new SuperAdmin(['nom' => 'National', 'email' => uniqid('sa') . '@mobembo.cg', 'mot_de_passe' => Hash::make('x')]);
        $this->superAdmin->save();
    }

    // ------------------------------------------------------------------

    private function compagnie(string $nom = 'Canadian Airways', string $type = Agence::AERIEN): Agence
    {
        return Agence::create([
            'nom_agence' => $nom, 'type_transport' => $type, 'statut' => 'Actif',
            'date_partenariat' => now()->toDateString(), 'superadmin_id' => $this->superAdmin->id,
        ]);
    }

    private function admin(Agence $agence, string $telephone): Utilisateur
    {
        return Utilisateur::create([
            'agence_id' => $agence->id, 'nom' => 'ADMIN', 'prenom' => 'Paul', 'telephone' => $telephone,
            'role' => 'Admin', 'mot_de_passe' => Hash::make('Secret2026'), 'statut' => 'actif', 'compte_confirme' => true,
        ]);
    }

    private function vol(Agence $agence, array $surcharge = []): Vol
    {
        return Vol::create(array_merge([
            'agence_id' => $agence->id, 'numero_vol' => 'CA' . random_int(100, 999), 'compagnie' => $agence->nom_agence,
            'origine' => 'Aéroport de Brazzaville', 'destination' => 'Aéroport de Pointe-Noire',
            'ville_depart' => 'Brazzaville', 'ville_arrivee' => 'Pointe-Noire',
            'ville_depart_iata' => 'BZV', 'ville_arrivee_iata' => 'PNR',
            'date_depart' => now()->addDays(5)->setTime(8, 0), 'date_arrivee' => now()->addDays(5)->setTime(9, 0),
            'duree_minutes' => 60, 'prix' => 45000, 'places_totales' => 3, 'places_disponibles' => 3,
            'statut' => 'programme', 'type_vol' => 'direct',
        ], $surcharge));
    }

    private function client(string $telephone = '061112233'): CanadianUser
    {
        return CanadianUser::create([
            'name' => 'Grâce MOUKALA', 'nom' => 'MOUKALA', 'prenom' => 'Grâce', 'email' => $telephone . '@canadian.local',
            'telephone' => $telephone, 'password' => Hash::make('Secret2026'),
        ]);
    }

    private function enClient(CanadianUser $client): static
    {
        return $this->withToken($client->createToken('t', ['canadian-api'])->plainTextToken);
    }

    private string $statutMtn = 'PENDING';
    private bool $fauxMtn = false;

    /** MTN en mode réel, réponses simulées (le statut renvoyé suit $statutMtn). */
    private function mtnReel(string $statut): void
    {
        $this->statutMtn = $statut;
        config(['momo.simulation' => false, 'momo.collection.api_user' => 'u', 'momo.collection.api_key' => 'k', 'momo.collection.primary_key' => 'p']);
        if ($this->fauxMtn) {
            return;
        }
        $this->fauxMtn = true;
        Http::fake(function ($requete) {
            $url = $requete->url();

            return match (true) {
                str_contains($url, 'collection/token')           => Http::response(['access_token' => 'jeton'], 200),
                (bool) preg_match('#requesttopay/[^/]+$#', $url) => Http::response(['status' => $this->statutMtn, 'financialTransactionId' => 'FT-9'], 200),
                str_ends_with($url, 'requesttopay')              => Http::response(null, 202),
                default                                          => Http::response(null, 404),
            };
        });
    }

    // ------------------------------------------------------------------
    // Super admin et accès
    // ------------------------------------------------------------------

    public function test_le_super_admin_inscrit_une_compagnie_aerienne_qui_arrive_dans_son_espace(): void
    {
        $this->actingAs($this->superAdmin, 'superadmin')->post('/superadmin/agences', [
            'nom_agence' => 'Trans Air Congo', 'type_transport' => 'aerien', 'date_partenariat' => now()->toDateString(),
            'admin_telephone' => '061234599', 'admin_nom' => 'NGOMA', 'admin_prenom' => 'Paul', 'admin_password' => 'Secret2026!',
            'couleur' => '#0d47a1',
        ])->assertSessionHasNoErrors();

        $agence = Agence::where('nom_agence', 'Trans Air Congo')->firstOrFail();
        $this->assertTrue($agence->estAerien());
        $this->get('/superadmin/agences?type=aerien')->assertOk()->assertSee('Trans Air Congo');
        $this->get('/superadmin/agences/' . $agence->id)->assertOk()->assertSee('Vols')->assertDontSee('Âge max. enfant');

        auth('superadmin')->logout();
        $this->app['auth']->shouldUse('web');   // actingAs(superadmin) avait changé la garde par défaut
        $this->post('/login', ['identifiant' => '061234599', 'password' => 'Secret2026!'])
            ->assertRedirect(route('canadian.admin'));
        $this->get('/aerien/admin')->assertOk()->assertSee('Trans Air Congo');
        // L'espace bus lui est fermé
        $this->get('/admin/dashboard')->assertRedirect(route('canadian.admin'));
    }

    public function test_un_admin_de_bus_n_entre_pas_dans_l_espace_aerien_et_inversement(): void
    {
        $bus = $this->admin($this->compagnie('Océan Test', Agence::BUS), '061234501');
        $this->actingAs($bus)->get('/aerien/admin')->assertRedirect(route('admin.dashboard'));

        $aerien = $this->admin($this->compagnie(), '061234502');
        $this->actingAs($aerien)->get('/admin/bus')->assertRedirect(route('canadian.admin'));
    }

    public function test_un_client_de_l_application_ne_se_connecte_pas_a_l_administration(): void
    {
        $this->client('061112244');
        $this->post('/login', ['identifiant' => '061112244', 'password' => 'Secret2026'])->assertSessionHasErrors('identifiant');
        $this->assertGuest();
    }

    public function test_une_compagnie_ne_voit_pas_les_donnees_d_une_autre(): void
    {
        $a = $this->compagnie('Compagnie A');
        $b = $this->compagnie('Compagnie B');
        $volA = $this->vol($a, ['numero_vol' => 'AA101']);
        $this->vol($b, ['numero_vol' => 'BB202']);

        $this->actingAs($this->admin($b, '061234503'));
        $this->get('/aerien/vols')->assertOk()->assertSee('BB202')->assertDontSee('AA101');
        $this->get("/aerien/vols/{$volA->id}")->assertNotFound();
        $this->put("/aerien/vols/{$volA->id}", [])->assertNotFound();
        $this->delete("/aerien/vols/{$volA->id}")->assertNotFound();
    }

    public function test_tous_les_ecrans_de_l_espace_aerien_s_affichent(): void
    {
        $agence = $this->compagnie();
        $vol = $this->vol($agence);
        $client = $this->client();
        $resa = app(ReservationVolService::class)->reserver($client, $vol->id, 1, [['nom' => 'MOUKALA', 'prenom' => 'Grâce']]);
        app(ReservationVolService::class)->confirmer($resa, 'MTN', 'FT-1');

        $this->actingAs($this->admin($agence, '061234504'));
        foreach ([
            '/aerien/admin', '/aerien/vols', '/aerien/vols/create', "/aerien/vols/{$vol->id}", "/aerien/vols/{$vol->id}/edit",
            '/aerien/flotte', '/aerien/flotte/create', '/aerien/villes', '/aerien/villes/create',
            '/aerien/reservations', "/aerien/reservations/{$resa->id}", '/aerien/passagers', '/aerien/paiements',
            '/aerien/promos', '/aerien/promos/create', '/aerien/classes', '/aerien/classes/create',
            '/aerien/extras', '/aerien/extras/create', '/aerien/notifications', '/aerien/rapports', '/aerien/parametres',
        ] as $page) {
            $this->get($page)->assertOk();
        }
        $this->get('/aerien/paiements/export/csv')->assertOk();
        $this->get('/aerien/rapports/export')->assertOk();
    }

    public function test_l_admin_programme_un_vol_complet(): void
    {
        $agence = $this->compagnie();
        $this->actingAs($this->admin($agence, '061234505'));

        $this->post('/aerien/villes', ['nom' => 'Brazzaville', 'code_iata' => 'bzv', 'pays' => 'Congo', 'actif' => 1])->assertSessionHasNoErrors();
        $this->post('/aerien/villes', ['nom' => 'Pointe-Noire', 'code_iata' => 'PNR', 'pays' => 'Congo', 'actif' => 1])->assertSessionHasNoErrors();
        $this->post('/aerien/flotte', ['modele' => 'Boeing 737-800', 'immatriculation' => 'tn-ajb', 'capacite_places' => 150, 'statut' => 'Actif'])->assertSessionHasNoErrors();

        $donnees = [
            'numero_vol' => 'ca 101', 'aeroport_depart' => Ville::where('code_iata', 'BZV')->value('id'),
            'aeroport_arrivee' => Ville::where('code_iata', 'PNR')->value('id'), 'flotte_id' => Flotte::value('id'),
            'date_depart' => now()->addDays(3)->toDateString(), 'heure_depart' => '08:00',
            'date_arrivee' => now()->addDays(3)->toDateString(), 'heure_arrivee' => '09:10',
            'prix' => 45000, 'places_totales' => 140, 'statut' => 'programme', 'type_vol' => 'direct',
        ];
        $this->post('/aerien/vols', $donnees)->assertSessionHasNoErrors();

        $vol = Vol::firstOrFail();
        $this->assertSame('CA101', $vol->numero_vol);
        $this->assertSame($agence->id, $vol->agence_id);
        $this->assertSame('BZV', $vol->ville_depart_iata);
        $this->assertSame(140, $vol->places_disponibles);
        $this->assertSame(70, $vol->duree_minutes);

        // Capacité au-delà des sièges de l'avion : refusée
        $this->post('/aerien/vols', array_merge($donnees, ['numero_vol' => 'CA102', 'places_totales' => 200]))->assertSessionHasErrors('places_totales');
        // Même numéro dans la même compagnie : refusé
        $this->post('/aerien/vols', $donnees)->assertSessionHasErrors('numero_vol');
        // Arrivée avant le départ : refusée
        $this->post('/aerien/vols', array_merge($donnees, ['numero_vol' => 'CA103', 'heure_arrivee' => '07:00']))->assertSessionHasErrors('date_arrivee');
    }

    public function test_le_super_admin_voit_les_inscrits_bus_et_aerien(): void
    {
        Utilisateur::create([
            'nom' => 'BOUKAKA', 'prenom' => 'Jean', 'telephone' => '051234567', 'role' => 'Client',
            'mot_de_passe' => Hash::make('Secret2026'), 'compte_confirme' => true,
        ]);
        $this->client('061112277');

        $this->actingAs($this->superAdmin, 'superadmin');
        $this->get('/superadmin/utilisateurs')->assertOk()->assertSee('BOUKAKA')->assertSee('Airtel')->assertDontSee('MOUKALA');
        $this->get('/superadmin/utilisateurs?espace=aerien')->assertOk()->assertSee('MOUKALA')->assertDontSee('BOUKAKA');
    }

    // ------------------------------------------------------------------
    // Application mobile
    // ------------------------------------------------------------------

    public function test_un_client_s_inscrit_sur_l_application_aerienne(): void
    {
        $this->postJson('/api/canadian/register', [
            'nom' => 'MOUKALA', 'prenom' => 'Grâce', 'telephone' => '061112233', 'mot_de_passe' => 'Secret2026',
        ])->assertCreated()->assertJsonStructure(['token', 'user']);

        $this->assertSame('MOUKALA', CanadianUser::where('telephone', '061112233')->value('nom'));
    }

    public function test_l_application_ne_propose_que_les_vols_en_vente_des_compagnies_actives(): void
    {
        $active = $this->compagnie('Air Actif');
        $bloquee = $this->compagnie('Air Bloque');
        $bloquee->update(['statut' => 'Bloqué']);
        $this->vol($active, ['numero_vol' => 'OK101']);
        $this->vol($active, ['numero_vol' => 'AN102', 'statut' => 'annule']);
        $this->vol($active, ['numero_vol' => 'PA103', 'date_depart' => now()->subHour(), 'date_arrivee' => now()]);
        $this->vol($bloquee, ['numero_vol' => 'BL104']);

        $vols = collect($this->getJson('/api/vols/search?ville_depart=Brazzaville&ville_arrivee=Pointe-Noire')->assertOk()->json('vols'));
        $this->assertSame(['OK101'], $vols->pluck('numero_vol')->all());
        $this->assertSame('Air Actif', $vols->first()['compagnie_info']['nom']);

        // Les compagnies aériennes n'apparaissent pas dans la liste des compagnies de bus
        $this->compagnie('Bus Express', Agence::BUS);
        $noms = collect($this->getJson('/api/agences')->json('agences'))->pluck('nom')->all();
        $this->assertSame(['Bus Express'], $noms);
    }

    public function test_reservation_puis_paiement_mtn_confirme_par_l_operateur(): void
    {
        $agence = $this->compagnie();
        $vol = $this->vol($agence);
        $client = $this->client();
        $this->mtnReel('PENDING');

        $resa = $this->enClient($client)->postJson('/api/canadian/reservations', [
            'vol_id' => $vol->id, 'nombre_places' => 2,
            'passagers' => [['nom' => 'MOUKALA', 'prenom' => 'Grâce', 'type' => 'Adulte'], ['nom' => 'MOUKALA', 'prenom' => 'Eli', 'type' => 'enfant']],
        ])->assertCreated()->json('reservation');
        $this->assertSame(1, $vol->fresh()->places_disponibles);
        $this->assertNull($resa['code_pnr']);

        // Mauvais opérateur pour le numéro : refusé
        $this->enClient($client)->postJson('/api/canadian/paiements/initier', ['reservation_id' => $resa['id'], 'methode' => 'mtn_momo', 'telephone' => '051112233'])
            ->assertStatus(422);

        $paiement = $this->enClient($client)->postJson('/api/canadian/paiements/initier', ['reservation_id' => $resa['id'], 'methode' => 'mtn_momo'])
            ->assertOk()->assertJson(['statut' => 'EN_ATTENTE', 'simulation' => false])->json();
        $this->assertSame('En_Attente', Reservation::find($resa['id'])->statut);

        // Le client valide sur son téléphone
        $this->mtnReel('SUCCESSFUL');
        $this->enClient($client)->getJson('/api/canadian/paiements/' . $paiement['reference'] . '/statut')
            ->assertOk()->assertJson(['statut' => 'PAYE']);

        $reservation = Reservation::find($resa['id']);
        $this->assertSame('Paye', $reservation->statut);
        $this->assertNotNull($reservation->code_pnr);
        $this->assertSame(2, $reservation->passagers()->count());
        $this->assertSame(90000.0, (float) Paiement::where('reservation_id', $reservation->id)->value('montant'));
        $this->assertSame($agence->id, Paiement::where('reservation_id', $reservation->id)->value('agence_id'));
        $this->assertSame(1, $vol->fresh()->places_disponibles);

        // Le billet n'est donné qu'une fois payé
        $this->enClient($client)->getJson("/api/canadian/reservations/{$reservation->id}/billet")
            ->assertOk()->assertJsonPath('billet.code_pnr', $reservation->code_pnr);
    }

    public function test_un_paiement_refuse_rend_les_places(): void
    {
        $vol = $this->vol($this->compagnie());
        $client = $this->client();
        $this->mtnReel('PENDING');

        $resa = $this->enClient($client)->postJson('/api/canadian/reservations', ['vol_id' => $vol->id, 'nombre_places' => 3])->json('reservation');
        $this->assertSame(0, $vol->fresh()->places_disponibles);
        $this->enClient($client)->postJson('/api/canadian/reservations', ['vol_id' => $vol->id, 'nombre_places' => 1])->assertStatus(409);

        $ref = $this->enClient($client)->postJson('/api/canadian/paiements/initier', ['reservation_id' => $resa['id'], 'methode' => 'mtn_momo'])->json('reference');
        $this->mtnReel('FAILED');
        $this->enClient($client)->getJson("/api/canadian/paiements/{$ref}/statut")->assertJson(['statut' => 'ECHOUE']);

        $this->assertSame('Echouee', Reservation::find($resa['id'])->statut);
        $this->assertSame(3, $vol->fresh()->places_disponibles);
    }

    public function test_une_reservation_non_payee_expire_et_libere_ses_places(): void
    {
        $vol = $this->vol($this->compagnie());
        $resa = app(ReservationVolService::class)->reserver($this->client(), $vol->id, 2);
        $this->assertSame(1, $vol->fresh()->places_disponibles);

        $resa->update(['expire_le' => now()->subMinute()]);
        app(ReservationVolService::class)->expirer();

        $this->assertSame('Expiree', $resa->fresh()->statut);
        $this->assertSame(3, $vol->fresh()->places_disponibles);
    }

    public function test_paiement_de_test_hors_production(): void
    {
        config(['momo.simulation' => true]);
        $vol = $this->vol($this->compagnie());
        $client = $this->client();
        $resa = $this->enClient($client)->postJson('/api/canadian/reservations', ['vol_id' => $vol->id, 'nombre_places' => 1])->json('reservation');

        $this->enClient($client)->postJson('/api/canadian/paiements/initier', ['reservation_id' => $resa['id'], 'methode' => 'mtn_momo'])
            ->assertOk()->assertJson(['statut' => 'PAYE', 'simulation' => true]);
    }

    public function test_l_admin_annule_une_reservation_payee_qui_passe_a_rembourser(): void
    {
        $agence = $this->compagnie();
        $vol = $this->vol($agence);
        $client = $this->client();
        $service = app(ReservationVolService::class);
        $resa = $service->reserver($client, $vol->id, 1);
        $service->confirmer($resa, 'MTN', 'FT-1');

        $this->actingAs($this->admin($agence, '061234506'));
        $this->post("/aerien/reservations/{$resa->id}/annuler")->assertSessionHas('success');
        $this->assertSame('A_Rembourser', $resa->fresh()->statut);
        $this->assertSame(3, $vol->fresh()->places_disponibles);

        $this->post("/aerien/reservations/{$resa->id}/rembourser")->assertSessionHas('success');
        $this->assertSame('Remboursee', $resa->fresh()->statut);
        $this->assertSame(2, $client->notifications()->count() >= 2 ? 2 : $client->notifications()->count());
    }
}
