<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblApuMonthly extends Model
{
    protected $table = 'tbl_apu_monthly';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'MonthEval',      // DATE          — periode bulan (YYYY-MM-01)
        'ACReg',          // VARCHAR(20)   — registrasi pesawat
        'ACType',         // VARCHAR(50)   — tipe pesawat
        'Operator',       // VARCHAR(100)  — nama operator/airline
        'ApuSN',          // VARCHAR(50)   — serial number APU
        'ApuType',        // VARCHAR(50)   — tipe/model APU
        'RunHours',       // DECIMAL(10,2) — total jam APU menyala (jam)
        'Cycles',         // INT           — jumlah siklus APU (start-stop)
        'EGT',            // DECIMAL(6,2)  — Exhaust Gas Temperature APU (°C)
        'OilConsumption', // DECIMAL(8,4)  — konsumsi oli APU (liter/jam)
        'AvailDays',      // INT           — jumlah hari APU tersedia/beroperasi
        'Remarks',        // TEXT          — catatan tambahan
    ];

    protected $casts = [
        'MonthEval' => 'date',
        'RunHours' => 'decimal:2',
        'Cycles' => 'integer',
        'EGT' => 'decimal:2',
        'OilConsumption' => 'decimal:4',
        'AvailDays' => 'integer',
    ];

    // Relasi ke master aircraft
    public function masterAc()
    {
        return $this->belongsTo(TblMasterac::class, 'ACReg', 'ACReg');
    }
}
