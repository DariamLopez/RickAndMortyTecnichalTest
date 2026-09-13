<?php

use Illuminate\Support\Facades\Route;

Route::get('docs/api', function () {
    return response()->view('api-docs');
});
// Any route other than `/api/*` returns the SPA shell;
// Actual routing is handled by Vue Router in the browser.
Route::view('/{any}', 'app')->where('any', '^(?!api).*$');
