<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: TblEngine
 * Tabel: tbl_engine
 * Digunakan di: Engine Operation Summary (no.5)
 * Data: running hours, cycles, temperature, fuel per engine per bulan
 */
class TblEngine extends Model
{
    protected $table      = 'tbl_engine';
    protected $primaryKey = 'id';
    public    $timestamps = true;

    protected $fillable = [
        'MonthEval',      // DATE          — periode bulan (YYYY-MM-01)
        'ACReg',          // VARCHAR(20)   — registrasi pesawat (misal PK-GFA)
        'ACType',         // VARCHAR(50)   — tipe pesawat (misal B737-800)
        'Operator',       // VARCHAR(100)  — nama operator/airline
        'EnginePos',      // VARCHAR(10)   — posisi engine: ENG1 / ENG2
        'EngineSN',       // VARCHAR(50)   — serial number engine
        'EngineType',     // VARCHAR(50)   — tipe/model engine
        'RunHours',       // DECIMAL(10,2) — total jam engine menyala (jam)
        'Cycles',         // INT           — jumlah siklus engine (start-stop)
        'FuelFlow',       // DECIMAL(10,2) — rata-rata konsumsi bahan bakar (kg/jam)
        'EGT',            // DECIMAL(6,2)  — Exhaust Gas Temperature (°C)
        'N1',             // DECIMAL(6,2)  — kecepatan fan (%)
        'N2',             // DECIMAL(6,2)  — kecepatan core (%)
        'OilConsumption', // DECIMAL(8,4)  — konsumsi oli (liter/jam)
        'Remarks',        // TEXT          — catatan tambahan
    ];

    protected $casts = [
        'MonthEval'      => 'date',
        'RunHours'       => 'decimal:2',
        'Cycles'         => 'integer',
        'FuelFlow'       => 'decimal:2',
        'EGT'            => 'decimal:2',
        'N1'             => 'decimal:2',
        'N2'             => 'decimal:2',
        'OilConsumption' => 'decimal:4',
    ];

    // Relasi ke master aircraft
    public function masterAc()
    {
        return $this->belongsTo(TblMasterac::class, 'ACReg', 'ACReg');
    }
}