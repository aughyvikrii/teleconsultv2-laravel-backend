<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\{Person, Appointment};

class PatientController extends Controller
{
    public function list(Request $request) {
        $patient = Person::patient();

        if($request->query('paginate')=='true') $list = $patient->paginate($request->query('data_per_page', 10));
        else $list = $patient->get();

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }
}
