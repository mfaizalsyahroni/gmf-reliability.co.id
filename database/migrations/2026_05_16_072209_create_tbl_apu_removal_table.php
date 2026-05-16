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
        Schema::create('tbl_apu_removal', function (Blueprint $table) {
            $table->id();

            // Identitas pesawat
            $table->date('RemovalDate')->comment('Tanggal APU dilepas');
            $table->string('ACReg', 20)->comment('Registrasi pesawat');
            $table->string('ACType', 50)->comment('Tipe pesawat');
            $table->string('Operator', 100)->comment('Nama operator/airline');

            // Identitas APU yang dilepas
            $table->string('ApuSN', 50)->comment('Serial number APU yang dilepas');
            $table->string('ApuType', 50)->nullable()->comment('Model APU');

            // Detail removal
            $table->string('RemovalReason', 255)->comment('Alasan pelepasan APU');
            $table->string('RemovalType', 50)->comment('Scheduled / Unscheduled');

            // Life data APU saat dilepas
            $table->decimal('TSN', 10, 2)->nullable()->comment('Time Since New dalam jam');
            $table->integer('CSN')->nullable()->comment('Cycles Since New');
            $table->decimal('TSO', 10, 2)->nullable()->comment('Time Since Overhaul dalam jam');
            $table->integer('CSO')->nullable()->comment('Cycles Since Overhaul');

            // APU pengganti
            $table->date('InstallDate')->nullable()->comment('Tanggal APU pengganti dipasang');
            $table->string('ReplacementSN', 50)->nullable()->comment('Serial number APU pengganti');

            $table->text('CorrectiveAction')->nullable()->comment('Tindakan perbaikan');
            $table->string('Station', 10)->nullable()->comment('Lokasi bandara kode IATA');
            $table->text('Remarks')->nullable()->comment('Catatan tambahan');
            $table->timestamps();

            // Index untuk filter yang sering dipakai
            $table->index(['ACType', 'RemovalDate']);
            $table->index(['Operator', 'ACType']);
            $table->index('ApuSN');
            $table->index('RemovalType');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_apu_removal');
    }
};
