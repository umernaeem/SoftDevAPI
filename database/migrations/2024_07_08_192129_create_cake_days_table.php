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
        Schema::create('cake_days', function (Blueprint $table) {
            $table->id();
            $table->date("cake_date");
            $table->integer("no_of_cakes");
            $table->enum("cake_type",["small","large"]);
            $table->text("developer_names");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cake_days');
    }
};
