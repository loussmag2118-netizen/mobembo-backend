<?php

namespace Tests\Feature;

use App\Exceptions\ReservationImpossibleException;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\DonneesBus;
use Tests\TestCase;

/**
 * Bébé (0 à 3 ans) sur les genoux d'un adulte : sans siège, assurance de
 * 5 000 FCFA, informations sur le billet de l'adulte. À partir de 4 ans,
 * l'enfant paie son billet.
 */
class BebeBilletTest extends TestCase
{
    use RefreshDatabase, DonneesBus;

    protected function setUp(): void
    {
        parent::setUp();
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('À lancer sur MySQL/MariaDB.');
        }
        config(['paiement.assurance_bebe' => 5000]);
    }

    public function test_le_bebe_paie_l_assurance_et_figure_sur_le_billet_sans_prendre_de_place(): void
    {
        $depart = $this->creerDepart(['nombre_tickets_total' => 1]);   // une seule place
        $service = app(ReservationService::class);
        $passagers = [['nom' => 'MOUKALA', 'prenom' => 'Grâce', 'bebe' => ['nom' => 'MOUKALA', 'prenom' => 'Eli', 'age' => 2]]];

        $this->assertSame(12000.0, $service->montantAttendu($depart, $passagers));   // 7 000 + 5 000

        $ticket = $service->reserver($depart->id, $passagers, 'MTN', 'REF-BEBE', payeImmediatement: true)->first();
        $this->assertSame('MOUKALA', $ticket->bebe_nom);
        $this->assertSame(2, (int) $ticket->bebe_age);
        $this->assertSame(5000.0, (float) $ticket->assurance_bebe);
        $this->assertSame(12000.0, (float) $ticket->montant_total);
        $this->assertSame('Bébé : Eli MOUKALA, 2 ans', $ticket->libelleBebe());
        $this->assertSame(0, (int) $depart->fresh()->places_disponibles);   // seul l'adulte occupe une place

        $presente = $service->presenterTickets(collect([$ticket]))[0];
        $this->assertSame('Eli', $presente['bebe']['prenom']);

        $this->get('/billet/' . $ticket->qr_code_token)->assertOk()->assertSee('Eli MOUKALA, 2 ans');
    }

    public function test_un_enfant_de_3_ans_ou_moins_ne_prend_pas_de_billet_enfant(): void
    {
        $depart = $this->creerDepart();
        $this->expectException(ReservationImpossibleException::class);
        $this->expectExceptionMessage('déclarez-le comme bébé');
        app(ReservationService::class)->montantAttendu($depart, [['nom' => 'E', 'categorie' => 'Enfant', 'age' => 3]]);
    }

    public function test_un_bebe_de_4_ans_est_refuse_et_doit_payer_son_billet(): void
    {
        $depart = $this->creerDepart();
        $this->expectException(ReservationImpossibleException::class);
        $this->expectExceptionMessage('paie son billet');
        app(ReservationService::class)->montantAttendu($depart, [['nom' => 'P', 'bebe' => ['nom' => 'B', 'age' => 4]]]);
    }

    public function test_seul_un_adulte_porte_un_bebe(): void
    {
        $depart = $this->creerDepart();
        $this->expectException(ReservationImpossibleException::class);
        app(ReservationService::class)->montantAttendu($depart, [['nom' => 'E', 'categorie' => 'Enfant', 'age' => 8, 'bebe' => ['nom' => 'B', 'age' => 1]]]);
    }

    public function test_l_application_recoit_le_montant_de_l_assurance(): void
    {
        $this->creerDepart();
        $depart = collect($this->getJson('/api/planifications')->json())->first();
        $this->assertSame(5000, $depart['assurance_bebe'] ?? null);
    }
}
