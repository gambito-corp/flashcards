<?php

use Illuminate\Http\Request;
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

Route::get('/', function () {
    return view('welcome');
});
Route::redirect('/home', '/dashboard');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::post('/carga-csv', function(Request $request){
        $request->validate([
            'instrucciones' => 'file|mimes:csv,txt|max:2048',
            'preguntas' => 'required|file|mimes:csv,txt|max:2048',
            'respuestas' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('instrucciones');

        $sample = file_get_contents($file->getPathname(), false, null, 0, 1000);

        $commaCount = substr_count($sample, ',');
        $semicolonCount = substr_count($sample, ';');

        $delimiter = ($semicolonCount > $commaCount) ? ';' : ',';

        $csv = League\Csv\Reader::createFromPath($file->getPathname(), 'r');
        $csv->setDelimiter($delimiter);
        $csv->setHeaderOffset(0);

        $rows = [];
        foreach ($csv as $record) {
            $rows[] = $record;
        }

        dd($rows, $request);
    })->name('carga-csv');
});
