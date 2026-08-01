<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/docs/api');
});

// Sin autenticación, a propósito — para balanceadores/monitoreo (Railway,
// uptime checks, etc). A diferencia del /up que trae Laravel por default,
// este sí confirma que la base de datos responde.
Route::get('/health', function () {
    try {
        DB::select('SELECT 1');

        return response()->json([
            'estado' => 'ok',
            'entorno' => app()->environment(),
            'base_datos' => 'conectada',
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'estado' => 'error',
            'entorno' => app()->environment(),
            'base_datos' => 'desconectada',
        ], 500);
    }
});
