<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('tax_id')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->enum('type', ['company', 'individual'])->default('company');
            $table->enum('status', ['active', 'inactive', 'blacklist'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('tax_id')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->enum('type', ['company', 'individual'])->default('company');
            $table->enum('status', ['active', 'inactive', 'blacklist'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customers');
    }
};
