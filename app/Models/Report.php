<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $table = 'reports'; // Pastikan sesuai dengan nama tabel di database
    protected $fillable = ['aircraft_type', 'period']; // Sesuaikan dengan kolom di tabel
}


