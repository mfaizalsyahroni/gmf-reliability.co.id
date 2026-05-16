<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_engine', function (Blueprint $table) {
            $table->id();

            // Identitas pesawat & periode
            $table->date('MonthEval')->comment('Periode bulan operasi');
            $table->string('ACReg', 20)->comment('Registrasi pesawat, misal PK-GFA');
            $table->string('ACType', 50)->comment('Tipe pesawat, misal B737-800');
            $table->string('Operator', 100)->comment('Nama operator/airline');

            // Identitas engine
            $table->string('EnginePos', 10)->comment('Posisi: ENG1 / ENG2');
            $table->string('EngineSN', 50)->comment('Serial number engine');
            $table->string('EngineType', 50)->nullable()->comment('Model engine, misal CFM56-7B');

            // Data operasional engine
            $table->decimal('RunHours', 10, 2)->default(0)->comment('Total jam engine menyala');
            $table->integer('Cycles')->default(0)->comment('Jumlah siklus start-stop');
            $table->decimal('FuelFlow', 10, 2)->nullable()->comment('Rata-rata konsumsi bahan bakar kg/jam');
            $table->decimal('EGT', 6, 2)->nullable()->comment('Exhaust Gas Temperature dalam °C');
            $table->decimal('N1', 6, 2)->nullable()->comment('Kecepatan fan dalam persen');
            $table->decimal('N2', 6, 2)->nullable()->comment('Kecepatan core dalam persen');
            $table->decimal('OilConsumption', 8, 4)->nullable()->comment('Konsumsi oli liter/jam');

            $table->text('Remarks')->nullable()->comment('Catatan tambahan');
            $table->timestamps();

            // Index untuk query yang sering dipakai
            $table->index(['ACType', 'MonthEval']);
            $table->index(['Operator', 'ACType']);
            $table->index('EngineSN');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_engine');
    }
};
