<?php

namespace Tests\Feature;

use App\Models\Bus;
use App\Models\Trajet;
use App\Support\Immatriculation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\DonneesBus;
use Tests\TestCase;

/**
 * Saisies de l'administration : plaques d'immatriculation congolaises et
 * durée des trajets en heures + minutes.
 */
class SaisiesAdministrationTest extends TestCase
{
    use RefreshDatabase, DonneesBus;

    protected function setUp(): void
    {
        parent::setUp();
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('À lancer sur MySQL/MariaDB.');
        }
    }

    private function bus(array $surcharge = []): array
    {
        return array_merge(['immatriculation' => '123 AB 4', 'modele' => 'Yutong', 'capacite_totale' => 50, 'statut' => 'Disponible'], $surcharge);
    }

    public function test_les_formats_de_plaque_congolais(): void
    {
        $this->assertSame('123 AB 4', Immatriculation::normaliser('123ab4'));
        $this->assertSame('456 CD 16', Immatriculation::normaliser('RC 456-cd-16'));
        $this->assertSame('Commune de Pointe-Noire', Immatriculation::circonscription('789 EF 6'));
        foreach (['BZ-123-AA', '12 AB 4', '1234 AB 4', '123 A 4', '123 AB 17', '123 AB 0', '123 AB 04', ''] as $faux) {
            $this->assertNull(Immatriculation::normaliser($faux), $faux);
        }
    }

    public function test_un_bus_est_enregistre_avec_sa_plaque_au_format_officiel(): void
    {
        $depart = $this->creerDepart();
        $this->actingAs($this->personnel('Admin', $depart->agence_id), 'web');

        $this->post('/admin/bus', $this->bus(['immatriculation' => 'bz-123-aa']))->assertSessionHasErrors('immatriculation');
        $this->post('/admin/bus', $this->bus(['immatriculation' => '123-ab-4']))->assertSessionHasNoErrors();

        $this->assertTrue(Bus::where('immatriculation', '123 AB 4')->exists());
    }

    public function test_une_plaque_n_appartient_qu_a_un_seul_vehicule_sur_la_plateforme(): void
    {
        $a = $this->creerDepart();
        $b = $this->creerDepart();

        $this->actingAs($this->personnel('Admin', $a->agence_id), 'web');
        $this->post('/admin/bus', $this->bus())->assertSessionHasNoErrors();

        // Même plaque écrite autrement, dans une autre agence : refusée
        $this->actingAs($this->personnel('Admin', $b->agence_id), 'web');
        $this->post('/admin/bus', $this->bus(['immatriculation' => '123AB4']))->assertSessionHasErrors('immatriculation');
        $this->assertSame(1, Bus::where('immatriculation', '123 AB 4')->count());
    }

    public function test_modifier_un_bus_garde_sa_propre_plaque(): void
    {
        $depart = $this->creerDepart();
        $this->actingAs($this->personnel('Admin', $depart->agence_id), 'web');
        $this->post('/admin/bus', $this->bus())->assertSessionHasNoErrors();
        $bus = Bus::where('immatriculation', '123 AB 4')->firstOrFail();

        $this->put("/admin/bus/{$bus->id}", $this->bus(['immatriculation' => '123 ab 4', 'modele' => 'Higer']))->assertSessionHasNoErrors();
        $this->assertSame('Higer', $bus->fresh()->modele);
    }

    public function test_la_duree_d_un_trajet_se_saisit_en_heures_et_minutes(): void
    {
        $depart = $this->creerDepart();
        $this->actingAs($this->personnel('Admin', $depart->agence_id), 'web');
        $trajet = Trajet::findOrFail($depart->trajet_id);
        $donnees = [
            'gare_depart_id' => $trajet->gare_arrivee_id, 'gare_arrivee_id' => $trajet->gare_depart_id,
            'bus_id' => $trajet->bus_id, 'horaires' => ['06:30'],
        ];

        // Minutes au-delà de 59 : refusées
        $this->post('/admin/trajets', $donnees + ['duree_heures' => 5, 'duree_mins' => 65])->assertSessionHasErrors('duree_mins');

        $this->post('/admin/trajets', $donnees + ['duree_heures' => 5, 'duree_mins' => 30])->assertSessionHasNoErrors();
        $this->assertSame(330, (int) Trajet::where('gare_depart_id', $trajet->gare_arrivee_id)->value('duree_minutes'));
    }
}
