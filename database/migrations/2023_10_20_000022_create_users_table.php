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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('profile')->default('CUSTOMER');
            $table->string('email');
            $table->string('password');
            $table->string('name')->nullable();
            $table->string('user_type')->nullable()->default('PHYSICAL-PERSON');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('user_name')->nullable();
            $table->uuid('registration_number')->unique()->nullable();
            $table->string('phone_number')->nullable();
            $table->string('whatsapp_phone_number')->nullable();
            $table->bigInteger('ifu')->nullable();
            $table->string('avatar')->nullable();
            $table->string('gender')->nullable();
            $table->date('birthday')->nullable();
            $table->string('birthplace')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->text('website')->nullable();
            $table->boolean('has_default_password')->nullable()->default(0);
            $table->foreignIdFor(Country::class)->nullable()->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamp('activated_at')->nullable()->useCurrent();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_number_verified_at')->nullable();
            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeignIdFor(Country::class);
        });
        Schema::dropIfExists('users');
    }
};
