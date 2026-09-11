<?php

use Illuminate\Support\Facades\Route;

// Cualquier ruta que no sea /api/* devuelve el shell de la SPA;
// el enrutado real lo gestiona Vue Router en el navegador.
Route::view('/{any}', 'app')->where('any', '^(?!api).*$');
