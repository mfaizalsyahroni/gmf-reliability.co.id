<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblSdr extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $table = 'tbl_sdr';
    protected $primaryKey = 'ID';
    public $incrementing = false;

    protected $fillable = [
        'ACTYPE',
        'Reg',
        'DateOccur',
        'FlightNo',
        'ATA',
        'Remark',
        'Problem',
        'Rectification',
    ];

    protected $casts = [
        'ID'=> 'integer',
        'DateOccur'=> 'date',
        'ATA'=> 'integer',
    ];

    protected $guarded = [
        'ID'
    ];
}