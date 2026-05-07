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
        if (!Schema::hasTable('tblpirep_swift')) {
            Schema::create('tblpirep_swift', function (Blueprint $table) {
                $table->id('ID_new'); // Menggunakan id() untuk auto-increment
                $table->string('Notification', 255)->nullable();
                $table->string('ACTYPE', 255)->nullable();
                $table->string('REG', 255)->nullable();
                $table->string('FN', 255)->nullable();
                $table->string('STADEP', 255)->nullable();
                $table->string('STAARR', 255)->nullable();
                $table->date('DATE')->nullable();
                $table->double('SEQ')->nullable();
                $table->string('DefectCode', 4)->nullable();
                $table->string('ATA', 2)->nullable();
                $table->string('SUBATA', 2)->nullable();
                $table->string('PROBLEM', 1000)->nullable();
                $table->string('Keyword', 255)->nullable();
                $table->string('ACTION', 1000)->nullable();
                $table->string('PirepMarep', 31)->nullable();
                $table->string('Month', 7)->nullable();
                $table->string('PN_in', 25)->nullable();
                $table->string('SN_in', 25)->nullable();
                $table->string('PN_out', 25)->nullable();
                $table->string('SN_out', 25)->nullable();
                $table->date('Created_on')->nullable();
                $table->date('Changed_on')->nullable();
                $table->date('update_date')->nullable();
                $table->string('ETOPSEvent', 255)->nullable();
                $table->string('GAForm', 255)->nullable();
                $table->string('ID_mcdrnew', 255)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Karena ini hanya untuk menghubungkan ke tabel yang sudah ada,
        // kita bisa mengosongkan method down() atau memberikan opsi untuk tidak menghapus tabel
        // Schema::dropIfExists('tblpirep_swift');
    }
};