<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\{News, Appointment, Person, User};

class HomeController extends Controller
{
    public function Dashboard(Request $request) {
        $news = News::selectRaw("nid as news_id, base_url(CONCAT('storage/image/news/thumbnail/', thumbnail)) as thumbnail, title")
                ->orderBy('nid', 'DESC')
                ->limit(1)
                ->get();

        $family = Person::getFamily()
                ->selectRaw('fam.pid as person_id, fam.full_name, patient_pic(fam.profile_pic) as profile_pic, id_age(fam.birth_date) as age')
                ->get();

        $appointment = Appointment::joinFullInfo()
                    ->selectRaw('appointments.aid as appointment_id, patient.pid as patient_id, patient.full_name as patient_name, doctor.pid as doctor_id, doctor.display_name as doctor_name, departments.deid as department_id, departments.name as department, branches.bid as branch_id, branches.name as branch, appointments.status, appointments.consul_date, ftime(appointments.consul_time) as consul_time, doctor_pic(doctor.profile_pic) as doctor_pic, patient_pic(patient.profile_pic) as patient_pic, id_date(consul_date) as id_consul_date')
                    ->family()
                    ->orderBy('appointments.aid', 'DESC')
                    ->limit(7)
                    ->get();

        return response()->json([
            'status' => true,
            'data' => [
                'news' => $news,
                'family' => $family,
                'appointment' => $appointment
            ]
        ]);
    }
}
