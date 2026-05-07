<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblMonthlyfhfc extends Model
{
    use HasFactory;

    // Menentukan nama tabel jika tidak mengikuti konvensi penamaan Laravel
    protected $table = 'tbl_monthlyfhfc';

    // Menentukan primary key jika bukan 'id'
    protected $primaryKey = 'ID';

    // Menentukan apakah primary key auto-increment
    public $incrementing = true;

    // Menentukan kolom yang dapat diisi massal
    protected $fillable = [
        'IDReg',
        'Reg',
        'Actype',
        'RevBHHours',
        'RevBHMin',
        'RevFHHours',
        'RevFHMin',
        'RevFC',
        'NoRevBHHours',
        'NoRevBHMin',
        'NoRevFHHours',
        'NoRevFHMin',
        'NoRevFC',
        'MonthEval',
        'AvaiDays',
        'TSN',
        'TSNMin',
        'CSN',
        'Remark',
    ];

    protected $casts = [
        'IDReg' => 'integer',
        'RevBHHours' => 'integer',
        'RevBHMin' => 'integer',
        'RevFHHours' => 'integer',
        'RevFHMin' => 'integer',
        'RevFC' => 'integer',
        'NoRevBHHours' => 'integer',
        'NoRevBHMin' => 'integer',
        'NoRevFHHours' => 'integer',
        'NoRevFHMin' => 'integer',
        'NoRevFC' => 'integer',
        'MonthEval' => 'date',
        'AvaiDays' => 'integer',
        'TSN' => 'integer',
        'TSNMin' => 'integer',
        'CSN' => 'integer',
    ];
}