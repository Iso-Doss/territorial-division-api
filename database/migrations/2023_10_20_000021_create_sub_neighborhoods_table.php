<?php

use App\Models\Neighborhood;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sub_neighborhoods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->string('description')->nullable();
            $table->string('total_population')->nullable();
            $table->foreignIdFor(Neighborhood::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
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
        Schema::table('sub_neighborhoods', function (Blueprint $table) {
            $table->dropForeignIdFor(Neighborhood::class);
        });
        Schema::dropIfExists('sub_neighborhoods');
    }
};
