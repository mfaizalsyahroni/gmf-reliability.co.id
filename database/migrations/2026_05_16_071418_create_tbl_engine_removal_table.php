<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_engine_removal', function (Blueprint $table) {
            $table->id();

            // Identitas pesawat
            $table->date('RemovalDate')->comment('Tanggal engine dilepas');
            $table->string('ACReg', 20)->comment('Registrasi pesawat');
            $table->string('ACType', 50)->comment('Tipe pesawat');
            $table->string('Operator', 100)->comment('Nama operator/airline');

            // Identitas engine yang dilepas
            $table->string('EnginePos', 10)->comment('Posisi: ENG1 / ENG2');
            $table->string('EngineSN', 50)->comment('Serial number engine yang dilepas');
            $table->string('EngineType', 50)->nullable()->comment('Model engine');

            // Detail removal
            $table->string('RemovalReason', 255)->comment('Alasan pelepasan engine');
            $table->string('RemovalType', 50)->comment('Scheduled / Unscheduled');
            $table->string('ShutdownType', 50)->nullable()->comment('IFSD / Ground / None');
            $table->text('ShutdownReason')->nullable()->comment('Detail penyebab shutdown');

            // Life data engine saat dilepas
            $table->decimal('TSN', 10, 2)->nullable()->comment('Time Since New dalam jam');
            $table->integer('CSN')->nullable()->comment('Cycles Since New');
            $table->decimal('TSO', 10, 2)->nullable()->comment('Time Since Overhaul dalam jam');
            $table->integer('CSO')->nullable()->comment('Cycles Since Overhaul');

            // Engine pengganti
            $table->date('InstallDate')->nullable()->comment('Tanggal engine pengganti dipasang');
            $table->string('ReplacementSN', 50)->nullable()->comment('Serial number engine pengganti');

            $table->text('CorrectiveAction')->nullable()->comment('Tindakan perbaikan');
            $table->string('Station', 10)->nullable()->comment('Lokasi bandara kode IATA');
            $table->text('Remarks')->nullable()->comment('Catatan tambahan');
            $table->timestamps();

            // Index untuk filter yang sering dipakai
            $table->index(['ACType', 'RemovalDate']);
            $table->index(['Operator', 'ACType']);
            $table->index('EngineSN');
            $table->index('RemovalType');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_engine_removal');
    }
};
