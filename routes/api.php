<?php 
use illuminate\Support\facades\Route;

Route::get("/test", function () {
    return response()->json(["message" => "Hola!, Bloomingtec API!"]);
});