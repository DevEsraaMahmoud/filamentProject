<?php

use App\Mail\ExportedFileEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     $fileName = 'employee_export_2024-05-30_12-46-22.csv.xlsx';
//     $filePath = public_path('exports/filament_exports/22/' . $fileName);
//     dd($filePath);
//     Mail::to('esraa.dev@gmail.com')->send(new ExportedFileEmail($fileName, $filePath));
// });
