<?php

use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(base_path('routes/Api/v1.php'));

Route::prefix('v2')->group(base_path('routes/Api/v2.php'));