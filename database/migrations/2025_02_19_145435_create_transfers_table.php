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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('doc_no', 50)->unique()->index();
            $table->integer('user_id')->index();
            $table->integer('user_id_to')->index();
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending')->index();
            $table->string('remark', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
