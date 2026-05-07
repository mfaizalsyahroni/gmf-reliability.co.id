<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblPirepSwift extends Model
{
    use HasFactory;

    protected $table = 'tblpirep_swift';
    protected $primaryKey = 'ID_new';
    public $incrementing = true;

    protected $fillable = [
        'Notification',
        'ACTYPE',
        'REG',
        'FN',
        'STADEP',
        'STAARR',
        'DATE',
        'SEQ',
        'DefectCode',
        'ATA',
        'SUBATA',
        'PROBLEM',
        'Keyword',
        'ACTION',
        'PirepMarep',
        'Month',
        'PN_in',
        'SN_in',
        'PN_out',
        'SN_out',
        'Created_on',
        'Changed_on',
        'update_date',
        'ETOPSEvent',
        'GAForm',
        'ID_mcdrnew',
    ];

    protected $casts = [
        'DATE' => 'date',
        'SEQ' => 'double',
        'Created_on' => 'date',
        'Changed_on' => 'date',
        'update_date' => 'date',
    ];
}
