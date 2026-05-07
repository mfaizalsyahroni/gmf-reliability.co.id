<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mcdrnew extends Model
{
    use HasFactory;

    protected $table = 'mcdrnew';

    protected $primaryKey = 'ID';

    public $incrementing = true;

    protected $fillable = [
        'DateEvent', 'ACtype',
        'Reg', 'FlightNo',
        'DepSta', 'ArivSta',
        'DCP', 'Aog',
        'HoursTot', 'MinTot',
        'FDD', 'RtABO',
        'lata', 'ATAtdm',
        'SubATAtdm', 'HoursTek',
        'MinTek', 'Problem',
        'Rectification', 'LastRectification',
        'KeyProblem', 'Chargeability',
        'RootCause', 'Maintenance_Action',
        'EventID', 'SDR',
        'Avoidable_Unavoidable', 'ATAdelay',
        'DateEvent1', 'TimeCode',
        'WorkshopReliability', 'UpdateDateTER',
        'CreateDateSwift', 'UpdateDateTO',
        'DateInsertTO', 'status_review',
        'user_review', 'Remark',
        'Contributing_Factor', 'Category',
    ];

    protected $casts = [
        'DATE' => 'date',
        'SEQ' => 'double',
        'DELAY_TIME' => 'integer',
        'Created_on' => 'date',
        'Changed_on' => 'date',
        'update_date' => 'date',
        'Month' => 'integer',
        'ATA' => 'integer'
    ];

    // Jika Anda ingin menambahkan relasi dengan tabel pirep_swift
    public function pirepSwift()
    {
        return $this->hasMany(TblPirepSwift::class, 'ID_mcdrnew', 'ID');
    }
}
