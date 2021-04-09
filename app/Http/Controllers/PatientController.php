<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\{Person, Appointment};

class PatientController extends Controller
{
    public function list(Request $request) {
        $patient = Person::JoinFullInfo()
                ->selectRaw('persons.*, patient_pic(persons.profile_pic) as profile_pic, users.email, villages.name as village, provinces.name as province, cities.name as city')
                ->patient()
                ->orderBy('pid', 'DESC');

        $name = $request->query('name');

        $patient->when($name, function($query) use ($name) {
            $query->whereRaw('LOWER(persons.full_name) LIKE LOWER(?)', ["%$name%"]);
        });

        $email = $request->query('email');

        $patient->when($email, function($query) use ($email) {
            $query->whereRaw('LOWER(users.email) LIKE LOWER(?)', ["%$email%"]);
        });

        $phone_number = $request->query('phone_number');

        $patient->when($phone_number, function($query) use ($phone_number) {
            $phone_number = format_phone($phone_number);
            $query->whereRaw('persons.phone_number LIKE ?', ["%$phone_number%"]);
        });

        if($request->query('paginate')=='true') $list = $patient->paginate($request->query('data_per_page', 10));
        else $list = $patient->get();

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }
}
