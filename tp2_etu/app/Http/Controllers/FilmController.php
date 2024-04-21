<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\FilmResource;
use App\Models\Film;


class FilmController extends Controller
{   
    public function index()
    {
        return FilmResource::collection(Film::all());
    }
    
}

