<?php

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
        Schema::create('user_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('organization_office')->nullable();
            $table->string('address')->nullable();
            $table->string('c_number')->nullable();
            $table->boolean('has_account')->default(false);
            $table->string('office_address')->nullable();
            $table->timestamps();
            // $table->string('role')->nullable();
            // $table->foreignId('users_id')->constrained('users')->cascadeOnDelete();
            // $table->integer('age');
            // $table->date('birthdate');
        });

        // Sample
        // Schema::create('users', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name');
        //     $table->string('email')->unique();
        //     $table->timestamp('email_verified_at')->nullable();
        //     $table->string('password');
        //     $table->rememberToken();
        //     $table->timestamps();
        // });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_credentials');
    }
};
