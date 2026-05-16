<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblEngineRemoval extends Model
{
    protected $table = 'tbl_engine_removal';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'RemovalDate',      // DATE         — tanggal engine dilepas
        'ACReg',            // VARCHAR(20)  — registrasi pesawat
        'ACType',           // VARCHAR(50)  — tipe pesawat
        'Operator',         // VARCHAR(100) — nama operator/airline
        'EnginePos',        // VARCHAR(10)  — posisi engine: ENG1 / ENG2
        'EngineSN',         // VARCHAR(50)  — serial number engine yang dilepas
        'EngineType',       // VARCHAR(50)  — tipe/model engine
        'RemovalReason',    // VARCHAR(255) — alasan pelepasan (Scheduled/Unscheduled/IFSD)
        'RemovalType',      // VARCHAR(50)  — tipe: Scheduled / Unscheduled
        'ShutdownType',     // VARCHAR(50)  — In-Flight Shutdown (IFSD) / Ground / None
        'ShutdownReason',   // TEXT         — detail penyebab shutdown
        'TSN',              // DECIMAL(10,2)— Time Since New (jam engine sejak baru)
        'CSN',              // INT          — Cycles Since New
        'TSO',              // DECIMAL(10,2)— Time Since Overhaul (jam sejak overhaul)
        'CSO',              // INT          — Cycles Since Overhaul
        'InstallDate',      // DATE         — tanggal engine pengganti dipasang
        'ReplacementSN',    // VARCHAR(50)  — serial number engine pengganti
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
