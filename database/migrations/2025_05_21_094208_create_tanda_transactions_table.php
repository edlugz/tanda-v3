<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tanda_transactions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('payment_id')->nullable();
            $table->string('payment_reference');
            $table->string('service_provider');
            $table->string('merchant_wallet')->nullable();
            $table->string('short_code')->nullable();
            $table->decimal('amount', 15, 2);

            $table->string('account_number')->nullable();
            $table->string('contact')->nullable();
            $table->string('service_provider_id')->nullable();
            $table->string('response_status')->nullable();
            $table->string('response_message')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('request_status')->nullable();
            $table->string('request_message')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('transaction_receipt')->nullable();
            $table->string('timestamp')->nullable();

            $table->string('transactable_type');
            $table->unsignedBigInteger('transactable_id');

            $table->text('json_request')->nullable();
            $table->text('json_response')->nullable();
            $table->text('json_result')->nullable();

            $table->string('registered_name')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tanda_transactions');
    }
};
