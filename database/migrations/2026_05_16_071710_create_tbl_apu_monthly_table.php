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
        Schema::create('tbl_apu_monthly', function (Blueprint $table) {
            $table->id();

            // Identitas pesawat & periode
            $table->date('MonthEval')->comment('Periode bulan operasi');
            $table->string('ACReg', 20)->comment('Registrasi pesawat');
            $table->string('ACType', 50)->comment('Tipe pesawat');
            $table->string('Operator', 100)->comment('Nama operator/airline');

            // Identitas APU
            $table->string('ApuSN', 50)->nullable()->comment('Serial number APU');
            $table->string('ApuType', 50)->nullable()->comment('Model APU, misal GTCP131-9A');

            // Data operasional APU
            $table->decimal('RunHours', 10, 2)->default(0)->comment('Total jam APU menyala');
            $table->integer('Cycles')->default(0)->comment('Jumlah siklus start-stop APU');
            $table->decimal('EGT', 6, 2)->nullable()->comment('Exhaust Gas Temperature APU dalam °C');
            $table->decimal('OilConsumption', 8, 4)->nullable()->comment('Konsumsi oli APU liter/jam');
            $table->integer('AvailDays')->default(0)->comment('Jumlah hari APU tersedia beroperasi');

            $table->text('Remarks')->nullable()->comment('Catatan tambahan');
            $table->timestamps();

            // Index untuk query yang sering dipakai
            $table->index(['ACType', 'MonthEval']);
            $table->index(['Operator', 'ACType']);
            $table->index('ApuSN');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_apu_monthly');
    }
};
