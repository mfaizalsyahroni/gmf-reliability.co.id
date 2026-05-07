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
        if (!Schema::hasTable('tbl_monthlyfhfc')) {
            Schema::create('tbl_monthlyfhfc', function (Blueprint $table) {
                $table->bigIncrements('ID'); // Menggunakan bigIncrements untuk ID
                $table->integer('IDReg')->nullable();
                $table->string('Reg', 255)->nullable();
                $table->string('Actype', 255)->nullable();
                $table->integer('RevBHHours')->nullable();
                $table->integer('RevBHMin')->nullable();
                $table->integer('RevFHHours')->nullable();
                $table->integer('RevFHMin')->nullable();
                $table->integer('RevFC')->nullable();
                $table->integer('NoRevBHHours')->nullable();
                $table->integer('NoRevBHMin')->nullable();
                $table->integer('NoRevFHHours')->nullable();
                $table->integer('NoRevFHMin')->nullable();
                $table->integer('NoRevFC')->nullable();
                $table->date('MonthEval')->nullable();
                $table->integer('AvaiDays')->nullable();
                $table->integer('TSN')->nullable();
                $table->integer('TSNMin')->nullable();
                $table->integer('CSN')->nullable();
                $table->string('Remark', 255)->nullable();

                // PostgreSQL tidak menggunakan charset dan collation yang sama seperti MySQL
                // $table->charset = 'latin1'; // Tidak diperlukan
                // $table->collation = 'latin1_swedish_ci'; // Tidak diperlukan
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_monthlyfhfc'); // Menambahkan drop table di method down
    }
};