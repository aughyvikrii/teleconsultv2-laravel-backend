<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \App\Models\{News, Appointment, Person, User, Bill, ZoomAccount};
use App\Libraries\{Zoom as ZoomLib};
use DB;

class HomeController extends Controller
{
    public function Dashboard(Request $request) {
        if(is_admin()) return $this->AdminDashboard($request);
        else if(is_doctor() && $request->input('doctor_dashboard')) return $this->DoctorDashboard($request);
        else return $this->PatientDashboard($request);
    }

    public function PatientDashboard(Request $request) {
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

    public function DoctorDashboard(Request $request) {

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

    public function AdminDashboard(Request $request) {
        $start_year = @intval($request->query('start_year')) ? $request->query('start_year') : date('Y');
        $start_month = @intval($request->query('start_month', date('m'))) ? $request->query('start_month', date('m')) : date('m');

        $start_date = $start_year . '-' . $start_month;
            $start_date = date('Y-m', strtotime($start_date));

        $end_year = @intval($request->query('end_year')) ? $request->query('end_year') : date('Y');
        $end_month = @intval($request->query('end_month')) ? $request->query('end_month') : date('m');

        $end_date = $end_year . '-' . $end_month;
            $end_date = date('Y-m', strtotime($end_date));

        $appointments = Appointment::joinFullInfo()
            ->selectRaw("appointments.aid as appointment_id, ftime(appointments.consul_time) as consul_time, patient.full_name as patient_name, patient_pic(patient.profile_pic) as patient_pic, departments.deid as department_id, departments.name as department, branches.bid as branch_id, branches.name as branch, id_age(patient.birth_date) as age, id_date(appointments.consul_date) as id_consul_date, appointments.status, appointments.main_complaint, appointments.consul_date, appointments.consul_time")
            ->whereIn('appointments.status', ['waiting_consul', 'in_consul'])
            ->orderBy('appointments.consul_date', 'DESC')
            ->limit(3)
            ->get();

        $data = Appointment::selectRaw('count(aid) as amount'. ", to_char(consul_date, 'YYYY-MM') as consul_date")
                ->whereRaw("to_char(consul_date, 'YYYY-MM') BETWEEN ? AND ?", [$start_date, $end_date])
                ->groupBY(DB::Raw("to_char(consul_date, 'YYYY-MM')"))
                ->pluck('amount', 'consul_date');

        $charts[] = ['Tanggal', 'Jumlah'];

        $currentDate = \Carbon\Carbon::parse($start_date)->startOfMonth();

        //Number of weeks helps with the for loop
        $numberOfMonths = $currentDate->diffInMonths($end_date);
        for ($x = 0; $x <= $numberOfMonths+1; $x++)
        {
            $tanggal_trans = $currentDate->translatedFormat('F Y');
            $tanggal = $currentDate->format('Y-m');
            $charts[] = [$tanggal_trans, @intval($data[$tanggal])];
            //something here
            $currentDate = $currentDate->addMonths(1);
        }

        $list = Appointment::JoinFullInfo()
                ->selectRaw('branches.bid, branches.name as branch, departments.deid, departments.name as department, specialists.sid, specialists.alt_name as specialist, doctor.pid as doctor_id, doctor.display_name as doctor'. ", to_char(consul_date, 'YYYY-MM') as consul_date")
                ->whereRaw("to_char(consul_date, 'YYYY-MM') BETWEEN ? AND ?", [$start_date, $end_date])
                ->get();
        
        $branches = $departments = $specialists = $doctors = [];

        foreach($list as $item) {

            $branch_total = @intval($branches[$item->bid]['total']) + 1;
            $branches[$item->bid] = [
                'name' => $item->branch . " [$branch_total]",
                'total' => $branch_total
            ];

            $department_total = @intval($departments[$item->deid]['total']) + 1;
            $departments[$item->deid] = [
                'name' => $item->department . " [$department_total]",
                'total' => $department_total
            ];

            $specialist_total = @intval($specialists[$item->sid]['total']) + 1;
            $specialists[$item->sid] = [
                'name' => $item->specialist . " [$specialist_total]",
                'total' => $specialist_total
            ];

            $doctor_total = @intval($doctors[$item->doctor_id]['total']) + 1;
            $doctors[$item->doctor_id] = [
                'name' => $item->doctor . " [$doctor_total]",
                'total' => $doctor_total
            ];
        }

        array_multisort(array_column($branches, 'total'), SORT_DESC, $branches);
        array_multisort(array_column($departments, 'total'), SORT_DESC, $departments);
        array_multisort(array_column($specialists, 'total'), SORT_DESC, $specialists);
        array_multisort(array_column($doctors, 'total'), SORT_DESC, $doctors);

        $branch_chart = $department_chart = $specialist_chart = $doctor_chart = [
            ['Nama', 'Jumlah']
        ];
        
        foreach($branches as $id => $item) {
            $branch_chart[] = [
                $item['name'], $item['total']
            ];
        }

        foreach($departments as $id => $item) {
            $department_chart[] = [
                $item['name'], $item['total']
            ];
        }

        foreach($specialists as $id => $item) {
            $specialist_chart[] = [
                $item['name'], $item['total']
            ];
        }

        foreach($doctors as $id => $item) {
            $doctor_chart[] = [
                $item['name'], $item['total']
            ];
        }

        $total_patient = Person::selectRaw('COUNT(pid) as total')
            ->whereRaw("TO_CHAR(persons.created_at, 'YYYY-MM') BETWEEN ? AND ?", [$start_date, $end_date])
            ->patient()
            ->first();

        $total_appointment = Appointment::selectRaw('COUNT(aid) as total')
            ->whereRaw("TO_CHAR(appointments.created_at, 'YYYY-MM') BETWEEN ? AND ?", [$start_date, $end_date])
            ->first();

        $total_income = Bill::selectRaw('SUM(amount) as total')
            ->whereRaw("status = ? AND TO_CHAR(bills.created_at, 'YYYY-MM') BETWEEN ? AND ?", ['paid', $start_date, $end_date])
            ->first();

        return response()->json([
            'status' => true,
            'data' => [
                'appointments' => $appointments,
                'charts' => [
                    'appointment' => [
                        'title' => 'Grafik Perjanjian Telekonsultasi',
                        'subtitle' => $start_date . ' s/d '. $end_date,
                        'data' => $charts
                    ],
                    'branch' => [
                        'title' => 'Cart Cabang',
                        'subtitle' => $start_date . ' s/d '. $end_date,
                        'data' => $branch_chart
                    ],
                    'department' => [
                        'title' => 'Cart Departemen',
                        'subtitle' => $start_date . ' s/d '. $end_date,
                        'data' => $department_chart
                    ],
                    'specialist' => [
                        'title' => 'Cart Spesialis',
                        'subtitle' => $start_date . ' s/d '. $end_date,
                        'data' => $specialist_chart
                    ],
                    'doctor' => [
                        'title' => 'Cart Dokter',
                        'subtitle' => $start_date . ' s/d '. $end_date,
                        'data' => $doctor_chart
                    ],
                ],
                'cards' => [
                    'user' => $total_patient->total,
                    'appointment' => $total_appointment->total,
                    'income' => $total_income->total
                ],
            ]
        ]);
    }

    public function zoom_verification(Request $request) {
        $valid = Validator::make($request->all(), [
            'zoom_api_key' => 'required',
            'zoom_api_secret'  => 'required',
            'zoom_jwt_token' => 'required',
        ], [
            'zoom_api_key.required' => 'Masukan zoom api key',
            'zoom_api_secret.required' => 'Masukan zoom api secret',
            'zoom_jwt_token.required' => 'Masukan zoom jwt token'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Kesalahan data',
                'errors' => $valid->errors()
            ]);
        }

        $zoomAccount = ZoomAccount::where('api_key', $request->zoom_api_key)->first();

        if($zoomAccount) {
            return response()->json([
                'status' => true,
                'data' =>  $zoomAccount
            ]);
        }

        $zoomLib = new ZoomLib($request->all());

        $userInfo = $zoomLib->userInfo($request->email);

        if(!$userInfo) {
            return response()->json([
                'status' => false,
                'message' => 'Token tidak valid'
            ]);
        }

        $zoomAccount = ZoomAccount::create([
            'account_id' => @$userInfo['account']['id'],
            'email' =>  @$userInfo['account']['email'],
            'api_key' => $request->zoom_api_key,
            'api_secret' => $request->zoom_api_secret,
            'jwt_token' => $request->zoom_jwt_token,
            'exp_int' => @$userInfo['token']['exp'],
            'expire_token' => @$userInfo['token']['exp_date'],
            'create_id' => auth()->user()->uid
        ]);

        if(!$zoomAccount) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mendaftarkan token, silahkan coba lagi!',
            ]);
        }

        return response()->json([
            'status' => true,
            'data' =>  $zoomAccount
        ]);
    }
}
