<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TblAlertLevel extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'tbl_alertlevel';
    protected $fillable = [
        'id',
        'actype',
        'ata',
        'type',
        'startmonth',
        'endmonth',
        'alertlevel',
        'NOTE'
    ];
}