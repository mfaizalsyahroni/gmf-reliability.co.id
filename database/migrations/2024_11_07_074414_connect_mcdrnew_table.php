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
        if (!Schema::hasTable('mcdrnew')) {
            Schema::create('mcdrnew', function (Blueprint $table) {
                $table->id(); // Menggunakan id() untuk auto-increment primary key
                $table->date('DateEvent')->nullable();
                $table->string('ACtype', 15)->nullable();
                $table->string('Reg', 15)->nullable();
                $table->string('FlightNo', 8)->nullable();
                $table->string('DepSta', 3)->nullable();
                $table->string('ArivSta', 3)->nullable();
                $table->string('DCP', 1)->nullable();
                $table->string('Aog', 3)->nullable();
                $table->integer('HoursTot')->nullable();
                $table->integer('MinTot')->nullable();
                $table->string('FDD', 5)->nullable();
                $table->string('RtABO', 3)->nullable();
                $table->string('Iata', 8)->nullable();
                $table->integer('ATAtdm')->nullable();
                $table->string('SubATAtdm', 2)->nullable();
                $table->integer('HoursTek')->nullable();
                $table->integer('MinTek')->nullable();
                $table->longText('Problem')->nullable();
                $table->longText('Rectification')->nullable();
                $table->longText('LastRectification')->nullable();
                $table->string('KeyProblem', 100)->nullable();
                $table->string('Chargeability', 50)->nullable();
                $table->string('RootCause', 100)->nullable();
                $table->longText('Maintenance_Action')->nullable();
                $table->integer('EventID')->nullable()->index();
                $table->string('SDR', 20)->nullable();
                $table->string('Avoidable_Unavoidable', 100)->nullable();
                $table->string('ATAdelay', 50)->nullable();
                $table->string('DateEvent1', 50)->nullable();
                $table->string('TimeCode', 50)->nullable();
                $table->string('WorkshopReliability', 100)->nullable();
                $table->date('UpdateDateTER')->nullable();
                $table->date('CreateDateSwift')->nullable();
                $table->dateTime('UpdateDateTO')->nullable();
                $table->dateTime('DateInsertTO')->nullable();
                $table->string('status_review', 10)->nullable();
                $table->string('user_review', 100)->nullable();
                $table->longText('Remark')->nullable();
                $table->longText('Contributing_Factor')->nullable();
                $table->longText('Category')->nullable();

                // PostgreSQL tidak menggunakan charset dan collation pada level tabel
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcdrnew');
    }
};