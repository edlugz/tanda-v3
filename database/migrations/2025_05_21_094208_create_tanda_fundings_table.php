<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tanda_fundings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('funding_id')->nullable();
            $table->string('fund_reference');
            $table->string('service_provider');
            $table->string('account_number');
            $table->string('merchant_wallet')->nullable();
            $table->string('short_code')->nullable();
            $table->decimal('amount', 15, 2);

            $table->string('response_status')->nullable();
            $table->string('response_message')->nullable();
            $table->string('tracking_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('request_status')->nullable();
            $table->string('request_message')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('timestamp')->nullable();
            $table->string('transaction_reference')->nullable();

            $table->text('json_result')->nullable();
            $table->text('json_response')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tanda_fundings');
    }
};
