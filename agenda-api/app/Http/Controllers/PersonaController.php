<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;

class PersonaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Persona::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
   

        $persona = Persona::create($request->all());

        return response()->json($persona, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Persona $persona)
    {
        return response()->json($persona);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Persona $persona)
    {
        $persona->update($request->all());

        return response()->json($persona);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Persona $persona)
    {
        
        $persona->delete();

        return response()->json($persona, 204);
    }
}
