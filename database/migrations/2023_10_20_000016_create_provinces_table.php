<?php

use App\Models\Region;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->string('description')->nullable();
            $table->string('prefecture_name')->nullable();
            $table->string('total_population')->nullable();
            $table->string('total_area')->nullable();
            $table->string('density')->nullable();
            $table->foreignIdFor(Region::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamp('activated_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};
