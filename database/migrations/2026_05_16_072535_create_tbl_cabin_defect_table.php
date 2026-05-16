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
        Schema::create('tbl_cabin_defect', function (Blueprint $table) {
            $table->id();

            // Identitas pesawat & kejadian
            $table->date('DateOccur')->comment('Tanggal defect ditemukan atau dilaporkan');
            $table->string('ACReg', 20)->comment('Registrasi pesawat');
            $table->string('ACType', 50)->comment('Tipe pesawat');
            $table->string('Operator', 100)->comment('Nama operator/airline');
            $table->string('FlightNo', 20)->nullable()->comment('Nomor penerbangan');
            $table->string('Station', 10)->nullable()->comment('Lokasi bandara kode IATA');

            // Klasifikasi defect
            $table->string('DefectCategory', 50)
                ->comment('Seat / Lavatory / Lighting / IFE / Galley / Other');
            $table->string('ATACode', 10)->nullable()->comment('Kode ATA sistem kabin');
            $table->text('DefectDesc')->comment('Deskripsi detail kerusakan');

            // Lokasi spesifik di kabin
            $table->string('SeatNo', 10)->nullable()->comment('Nomor kursi jika berlaku misal 12A');
            $table->string('LavatoryNo', 10)->nullable()->comment('Nomor lavatory jika berlaku');

            // Status deferral (MEL/CDL)
            $table->boolean('Deferral')->default(false)
                ->comment('true jika di-defer atau ditunda, false jika langsung diperbaiki');
            $table->string('DeferralRef', 50)->nullable()
                ->comment('Nomor referensi MEL atau CDL');

            // Penyelesaian
            $table->text('CorrectiveAction')->nullable()->comment('Tindakan perbaikan yang dilakukan');
            $table->date('ClosedDate')->nullable()->comment('Tanggal defect selesai diperbaiki');
            $table->string('Status', 20)->default('Open')
                ->comment('Open / Closed / Deferred');

            $table->text('Remarks')->nullable()->comment('Catatan tambahan');
            $table->timestamps();

            // Index untuk filter yang sering dipakai di Cabin Reliability Report
            $table->index(['ACType', 'DateOccur']);
            $table->index(['Operator', 'ACType']);
            $table->index('DefectCategory');    // filter per kategori cabin
            $table->index('ATACode');           // filter per ATA
            $table->index('Status');            // filter Open/Closed/Deferred
            $table->index('Deferral');          // filter yang di-defer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_cabin_defect');
    }
};
