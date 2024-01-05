<?php

use App\Models\Country;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('country_international_organisation', function (Blueprint $table) {
            $table->foreignIdFor(Country::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->bigInteger('international_organisation_id')->unsigned();
            $table->foreign('international_organisation_id', 'country_international_organisation_id')->references('id')->on('international_organisations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->primary(['country_id', 'international_organisation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_international_organisations');
    }
};
