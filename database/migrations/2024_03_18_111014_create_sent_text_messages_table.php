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
        Schema::create('sent_text_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('text_message');

            $table->string('senderid_string');

            $table->string('phone_number');
            $table->string('status')->default('draft');
            $table->string('message_id')->nullable();
            $table->string('response_code')->nullable();
            $table->string('response_description')->nullable();
            $table->string('network_id')->nullable();
            $table->string('delivery_status')->nullable();
            $table->string('delivery_description')->nullable();
            $table->string('delivery_tat')->nullable();
            $table->string('delivery_networkid')->nullable();
            $table->string('delivery_time')->nullable();
            $table->string('delivery_code')->nullable();
            $table->string('delivery_network_id')->nullable();
            $table->string('delivery_response_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sent_text_messages');
    }
};
