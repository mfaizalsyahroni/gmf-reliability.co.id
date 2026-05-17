<?php

use App\Http\Controllers\AOSController;
use App\Http\Controllers\PRTDController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\ExcelController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

// Route Test Database Connection
Route::get('/test-db', function () {
    try {
        // Cek apakah tabel ada
        if (Schema::hasTable('tbl_master_ata')) {
            // Hitung jumlah record
            $count = DB::table('tbl_master_ata')->count();

            if ($count > 0) {
                $results = DB::table('tbl_master_ata')->first();
                return "Koneksi database berhasil! Jumlah data: " . $count . " Sample data: " . json_encode($results);
            } else {
                return "Tabel exists tapi tidak ada data (kosong)";
            }
        } else {
            return "Tabel 'tbl_master_ata' tidak ditemukan";
        }
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [function () {
    return view('dashboard');
}])->middleware(['auth', 'verified'])->name('dashboard');



/* Route User Setting for Admin */
Route::get('/user-setting', [UserController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('user-setting');

Route::get('/users/create', [UserController::class, 'create'])
    ->middleware(['auth', 'verified']);

Route::post('/users', [UserController::class, 'store'])
    ->middleware(['auth', 'verified']);

Route::get('/users/{user:id}', [UserController::class, 'show'])
    ->middleware(['auth', 'verified']);

Route::get('/users/{user:id}/edit', [UserController::class, 'edit'])
    ->middleware(['auth', 'verified']);

Route::put('/users/{user:id}', [UserController::class, 'update'])
    ->middleware(['auth', 'verified']);

Route::delete('/users/{user:id}', [UserController::class, 'destroy'])
    ->middleware(['auth', 'verified']);



/* Route Authentication User */
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';



/* Routes Modul pada Report */
// Route::get('/report', function () {
//     return view('report');
// })
//     ->middleware(['auth', 'verified'])->name('report');

// Navbar report
Route::get('/report', function () {
    return view('report.report');
})->middleware(['auth', 'verified'])->name('report');

Route::get('/get-aircraft-types', [AOSController::class, 'getAircraftTypes'])->name('get.aircraft.types');

Route::get('/report/aos', [AOSController::class, 'aosIndex']) 
    ->name('report.aos');

Route::post('/report/aos', [AOSController::class, 'aosStore']) 
    ->name('report.aos.store');
    //btn display report
    
    
    Route::post('/report/aos/excel', [ExcelController::class, 'aosExcel'])
    ->name('report.aos.excel');
    //btn excel
    
    
    Route::get('/get-aos/generate.pdf', [PDFController::class, 'aosPdf'])
        ->name('report.aos.pdf');
        //btn pdf 



Route::get('/report/pilot', [ReportController::class, 'pilotIndex'])
    ->name('report.prtd.index')->middleware(['auth', 'verified']);

Route::post('/report/pilot', [PRTDController::class, 'prtdStore'])
    ->name('report.prtd.store')->middleware(['auth', 'verified']);

Route::get('/report/cumulative', [ReportController::class, 'cumulativeContent'])
    ->name('report.cumulative')
    ->middleware(['auth', 'verified']);
