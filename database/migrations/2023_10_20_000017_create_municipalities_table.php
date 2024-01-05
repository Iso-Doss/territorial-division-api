<?php

use App\Models\Province;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('municipalities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->string('description')->nullable();
            $table->string('mayor_name')->nullable();
            $table->string('mayor_mandate_year')->nullable();
            $table->string('total_population')->nullable();
            $table->string('density')->nullable();
            $table->foreignIdFor(Province::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamp('activated_at')->nullable()->useCurrent();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('municipalities', function (Blueprint $table) {
            $table->dropForeignIdFor(Province::class);
        });
        Schema::dropIfExists('municipalities');
    }
};
