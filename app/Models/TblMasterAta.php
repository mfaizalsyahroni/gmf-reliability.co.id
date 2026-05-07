<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblMasterAta extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'tbl_master_ata';
    protected $primaryKey = 'ATA';
    public $incrementing = false ;
    protected $fillable = [
        'ATA_DESC'
    ];
}
