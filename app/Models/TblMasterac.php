<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblMasterac extends Model
{
    use HasFactory;

    // Menentukan nama tabel jika tidak mengikuti konvensi penamaan Laravel
    protected $table = 'tbl_masterac';

    // Menentukan primary key jika bukan 'id'
    protected $primaryKey = 'IDreg'; // Ganti dengan nama kolom primary key yang sesuai

    // Menentukan apakah primary key auto-increment
    public $incrementing = false ;

    // Menentukan kolom yang dapat diisi massal
    protected $fillable = [
        'IDType',
        'ACType',
        'ACReg',
        'Operator',
        'SerialModule',
        'VariableNumber',
        'SerialNumber',
        'ManufYear',
        'DEliveryDate',
        'EngineType',
        'Lessor',
        'Active',
        // Tambahkan kolom lain jika ada
    ];

    protected $casts = [
        'IDType' => 'integer',
        'SerialNumber' => 'integer',
        'ManufYear' => 'datetime',
        'DEliveryDate' => 'datetime',
        'Active' => 'integer',
    ];
    
}