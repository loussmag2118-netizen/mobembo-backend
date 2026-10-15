<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Vitrine des agences, gérée par chaque agence depuis son administration :
 * logo, photos, slogan et couleur principale. L'application mobile les
 * charge depuis l'API : une nouvelle agence a sa page d'accueil sans
 * republier l'application.
 *
 * Les images sont stockées en base (table medias) et servies par
 * GET /medias/{id} avec un cache longue durée.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agence_id')->nullable()->constrained('agences')->cascadeOnDelete();
            $table->string('type', 20)->index();            // logo, photo
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->string('mime', 50);
            $table->unsignedInteger('taille');
            $table->timestamps();
        });
        // Contenu binaire (jusqu'à 16 Mo)
        DB::statement('ALTER TABLE medias ADD contenu MEDIUMBLOB NOT NULL');

        Schema::table('agences', function (Blueprint $table) {
            $table->string('slogan', 160)->nullable()->after('nom_agence');
            $table->string('couleur', 7)->nullable()->after('slogan');      // ex. #F7A21B
            $table->foreignId('logo_media_id')->nullable()->after('couleur')->constrained('medias')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agences', function (Blueprint $table) {
            $table->dropConstrainedForeignId('logo_media_id');
            $table->dropColumn(['slogan', 'couleur']);
        });
        Schema::dropIfExists('medias');
    }
};
