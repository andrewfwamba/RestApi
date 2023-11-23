<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MusicController extends Controller
{
    //
    public function show(Request $request)
    {
        return response()->json(["success" => true, "message" => "Accessible route"]);
    }
}
