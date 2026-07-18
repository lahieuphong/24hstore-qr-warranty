<?php

use App\Enums\WarrantyStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('product_code', 100)->index();
            $table->string('name', 255)->index();
            $table->string('imei', 64)->unique();
            $table->date('warehouse_date')->index();
            $table->unsignedSmallInteger('warranty_months')->nullable();
            $table->date('warranty_expires_at')->nullable()->index();
            $table->string('warranty_status', 32)->default(WarrantyStatus::ACTIVE->value)->index();
            $table->text('internal_note')->nullable();
            $table->uuid('qr_token')->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['warranty_status', 'warranty_expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
