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
        Schema::create('bvn_user', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_field_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('bvn', 11);
            $table->string('agent_location');
            $table->string('bank_name');
            $table->string('account_no');
            $table->string('account_name');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('email');
            $table->string('phone_no');
            $table->text('address');
            $table->string('state');
            $table->string('lga');
            $table->date('dob');
            $table->string('status')->default('pending');
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamp('submission_date')->nullable();
            $table->text('comment')->nullable();
            $table->text('query')->nullable();
            $table->string('performed_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bvn_user');
    }
};
