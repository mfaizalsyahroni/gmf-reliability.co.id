<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblApuRemoval extends Model
{
    protected $table = 'tbl_apu_removal';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'RemovalDate',      // DATE         — tanggal APU dilepas
        'ACReg',            // VARCHAR(20)  — registrasi pesawat
        'ACType',           // VARCHAR(50)  — tipe pesawat
        'Operator',         // VARCHAR(100) — nama operator/airline
        'ApuSN',            // VARCHAR(50)  — serial number APU yang dilepas
        'ApuType',          // VARCHAR(50)  — tipe/model APU
        'RemovalReason',    // VARCHAR(255) — alasan pelepasan
        'RemovalType',      // VARCHAR(50)  — Scheduled / Unscheduled
        'TSN',              // DECIMAL(10,2)— Time Since New (jam APU sejak baru)
        'CSN',              // INT          — Cycles Since New
        'TSO',              // DECIMAL(10,2)— Time Since Overhaul
        'CSO',              // INT          — Cycles Since Overhaul
        'InstallDate',      // DATE         — tanggal APU pengganti dipasang
        'ReplacementSN',    // VARCHAR(50)  — serial number APU pengganti
        'CorrectiveAction', // TEXT         — tindakan perbaikan yang dilakukan
        'Station',          // VARCHAR(10)  — lokasi/bandara kejadian (IATA code)
        'Remarks',          // TEXT         — catatan tambahan
    ];

    protected $casts = [
        'RemovalDate' => 'date',
        'InstallDate' => 'date',
        'TSN' => 'decimal:2',
        'CSN' => 'integer',
        'TSO' => 'decimal:2',
        'CSO' => 'integer',
    ];

    // Relasi ke master aircraft
    public function masterAc()
    {
        return $this->belongsTo(TblMasterac::class, 'ACReg', 'ACReg');
    }
}
