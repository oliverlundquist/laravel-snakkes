<?php

use App\Snakkes\Board;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('snakkes', ['board' => new Board]));
