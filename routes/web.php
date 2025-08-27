<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Web (browser) routes. API endpoints moved to routes/api.php.
*/

Route::get('/', function () {
	return response()->json(['message' => 'Bloomingtec TODO App']);
});
