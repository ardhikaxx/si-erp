<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->enum('type', ['in', 'out', 'transfer', 'adjustment', 'opname']);
            $table->decimal('quantity_before', 15, 2);
            $table->decimal('quantity', 15, 2);
            $table->decimal('quantity_after', 15, 2);
            $table->decimal('price', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('batch_no')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->date('opname_date');
            $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('system_quantity', 15, 2);
            $table->decimal('actual_quantity', 15, 2);
            $table->decimal('difference', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
        Schema::dropIfExists('inventory_movements');
    }
};
