<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['passport', 'national_id', 'driving_license', 'visa', 'other']);
            $table->string('document_number');
            $table->char('issuing_country', 2)->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('scan_path')->nullable();
            $table->timestamps();

            $table->index('guest_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_documents');
    }
};
