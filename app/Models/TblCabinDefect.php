<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblCabinDefect extends Model
{
    protected $table = 'tbl_cabin_defect';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'DateOccur',       // DATE         — tanggal defect ditemukan/dilaporkan
        'ACReg',           // VARCHAR(20)  — registrasi pesawat
        'ACType',          // VARCHAR(50)  — tipe pesawat
        'Operator',        // VARCHAR(100) — nama operator/airline
        'FlightNo',        // VARCHAR(20)  — nomor penerbangan
        'Station',         // VARCHAR(10)  — lokasi/bandara ditemukan (IATA code)
        'DefectCategory',  // VARCHAR(50)  — kategori: Seat/Lavatory/Lighting/IFE/Galley/Other
        'ATACode',         // VARCHAR(10)  — kode ATA sistem kabin
        'DefectDesc',      // TEXT         — deskripsi kerusakan
        'SeatNo',          // VARCHAR(10)  — nomor kursi (jika berlaku, misal 12A)
        'LavatoryNo',      // VARCHAR(10)  — nomor lavatory (jika berlaku)
        'Deferral',        // TINYINT(1)   — 1=di-defer/ditunda, 0=langsung diperbaiki
        'DeferralRef',     // VARCHAR(50)  — nomor referensi deferral (MEL/CDL)
        'CorrectiveAction',// TEXT         — tindakan perbaikan
        'ClosedDate',      // DATE         — tanggal defect selesai diperbaiki
        'Status',          // VARCHAR(20)  — Open / Closed / Deferred
        'Remarks',         // TEXT         — catatan tambahan
    ];

    protected $casts = [
        'DateOccur' => 'date',
        'ClosedDate' => 'date',
        'Deferral' => 'boolean',
    ];

    // Relasi ke master aircraft
    public function masterAc()
    {
        return $this->belongsTo(TblMasterac::class, 'ACReg', 'ACReg');
    }

    // Scope filter by kategori
    // https://laravel.com/docs/eloquent#local-scopes
    public function scopeCategory($query, string $category)
    {
        return $query->where('DefectCategory', $category);
    }

    // Scope filter hanya yang masih Open
    public function scopeOpen($query)
    {
        return $query->where('Status', 'Open');
    }

    // Scope filter yang di-defer
    public function scopeDeferred($query)
    {
        return $query->where('Deferral', true);
    }
}
