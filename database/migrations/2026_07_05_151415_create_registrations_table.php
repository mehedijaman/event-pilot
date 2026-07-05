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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('ticket_code')->unique();
            $table->foreignId('event_id')->constrained()->restrictOnDelete();
            $table->foreignId('package_id')->constrained()->restrictOnDelete();
            $table->string('seat_position');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('student_id_number')->nullable();
            $table->string('institution_name')->nullable();
            $table->string('payment_method');
            $table->string('transaction_id');
            $table->decimal('amount', 10, 2);
            $table->string('payment_status')->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ticket_email_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['payment_method', 'transaction_id']);
            $table->index(['event_id', 'payment_status']);
            $table->index(['event_id', 'seat_position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
