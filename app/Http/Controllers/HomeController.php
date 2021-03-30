<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\{News, Appointment, Person, User};
use DB;

class HomeController extends Controller
{
    public function Dashboard(Request $request) {
        if(is_admin()) return $this->AdminDashboard($request);
        else if(is_doctor() && $request->input('doctor_dashboard')) return $this->DoctorDashboard($request);
        else return $this->PatientDashboard($request);
    }

    public function PatientDashboard($request) {
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
                    ->limit(5)
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

    public function DoctorDashboard($request) {

        $appointments = Appointment::joinFullInfo()
        ->selectRaw("appointments.aid as appointment_id, ftime(appointments.consul_time) as consul_time, patient.full_name as patient_name, patient_pic(patient.profile_pic) as patient_pic, departments.deid as department_id, departments.name as department, branches.bid as branch_id, branches.name as branch, id_age(patient.birth_date) as age, id_date(appointments.consul_date) as id_consul_date, appointments.status, appointments.main_complaint, appointments.consul_date, appointments.consul_time")
        ->doctorUID(auth()->user()->uid)
        ->whereIn('appointments.status', ['waiting_consul', 'in_consul'])
        ->orderBy('appointments.consul_date', 'DESC')
        ->limit(3)
        ->get();

        $data = Appointment::selectRaw('count(aid) as amount, extract(month from consul_date) as month')
                ->whereRaw("DATE_PART('year', consul_date) = ?", [date('Y')])
                ->doctorUID()
                ->groupBY(DB::Raw('extract(month from consul_date)'))
                ->pluck('amount', 'month');
        
        if(date('m') <= 6){
            $start = 1; $end = 6;
        }else{
            $start = 7; $end = 12;
        }

        $charts[] = ['Bulan', 'Jumlah'];
        for($i = $start; $i<=$end; $i++) {
            $bulan = \Carbon\Carbon::parse("2020-".$i."-01")->translatedFormat('F');
            $charts[] = [$bulan, @intval($data[$i])];
        }

        return response()->json([
            'status' => true,
            'data' => [
                'appointments' => $appointments,
                'chart' => [
                    'title' => 'Grafik Perjanjian Telekonsultasi',
                    'subtitle' => 'Tahun '. date('Y'),
                    'data' => $charts
                ]
            ]
        ]);
    }
}
