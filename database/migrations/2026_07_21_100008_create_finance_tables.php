<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->enum('category', ['current_asset', 'fixed_asset', 'current_liability', 'long_term_liability', 'equity', 'operating_revenue', 'other_revenue', 'operating_expense', 'other_expense'])->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->decimal('balance', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['incoming', 'outgoing']);
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('reference')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->date('revenue_date');
            $table->text('description')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('revenues');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('chart_of_accounts');
    }
};
