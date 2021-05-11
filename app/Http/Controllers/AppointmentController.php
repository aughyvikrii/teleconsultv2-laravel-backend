<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth, DB, URL;
use \App\Models\{Schedule, Person, Appointment, Bill};
use \App\Libraries\Schedule as LSchedule;
use \App\Libraries\Midtrans;
use App\Libraries\Whatsapp;

class AppointmentController extends Controller
{
    public function Create(Request $request) {

        // $midtrans = new Midtrans(1);
        // $params = array(
        //     'transaction_details' => array(
        //         'order_id' => rand(),
        //         'gross_amount' => 10000,
        //     ),
        //     'customer_details' => array(
        //         'first_name' => 'budi',
        //         'last_name' => 'pratama',
        //         'email' => 'budi.pra@example.com',
        //         'phone' => '08111222333',
        //     ),
        //     "expiry" => array(
        //         'start_time' => date("Y-m-d H:i:s", strtotime("+3 minutes")) . " +0700",
        //         'unit' => 'minutes',
        //         'duration' => 3
        //     )
        // );
        // list($snapToken, $error) = $midtrans->getSnapToken($params);

        // dd($snapToken, $error, $params);
        
        $valid = Validator::make($request->all(), [
            'schedule_id' => 'required|exists:schedules,scid',
            'consul_date' => 'required|date_format:Y-m-d',
            'consul_time' => 'required|date_format:H:i',
            'person_id' => 'required|exists:persons,pid',
            'main_complaint' => 'required'
        ], [
            'schedule_id.required' => 'Pilih jadwal konsultasi',
            'schedule_id.exists' => 'Jadwal tidak valid',
            'consul_date.required' => 'Pilih tanggal konsultasi',
            'consul_date.date_format' => 'Format tanggal tidak valid',
            'consul_time.required' => 'Pilih jam konsultasi',
            'consul_time.date_format' => 'Format jam tidak valid',
            'person_id.required' => 'Pilih pasien',
            'person_id.exists' => 'Pasien tidak ditemukan',
            'main_complaint.required' => 'Masukan keluhan utama'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Kesalahan inputan',
                'errors' => $valid->errors()
            ]);
        }

        DB::BeginTransaction();

        $schedule = Schedule::JoinFullInfo('join', false)
                    ->selectRaw("schedules.scid as schedule_id, schedules.weekday, schedules.fee,  schedules.start_hour, schedules.end_hour, schedules.duration
                    , persons.pid as doctor_id, persons.display_name as doctor_name
                    , departments.deid as department_id, departments.name as department
                    , branches.bid as branch_id, branches.name as branch")
                    ->allActive()
                    ->where('scid', $request->schedule_id)
                    ->first();

        if(!$schedule) {
            return response()->json([
                'status' => false,
                'message' => 'Jadwal tidak ditemukan/ sudah tidak aktif'
            ]);
        }

        $patient = Person::familyMember($request->person_id);

        if(!$patient) {
            return response()->json([
                'status' => false,
                'message' => 'Pasien tidak ditemukan'
            ]);
        }

        $schedule_lib = new LSchedule($schedule->schedule_id);
        list($valid, $error) = $schedule_lib->validConsulDate($request->consul_date, $request->consul_time);
        if($error) {
            return response()->json([
                'status' => false,
                'message' => $error
            ]);
        }

        $appointment_data = [
            'patient_id' => $patient->pid,
            'scid' => $schedule->schedule_id,
            'consul_date' => $request->consul_date,
            'consul_time' => $request->consul_time,
            'main_complaint' => $request->main_complaint,
            'disease_history' => $request->disease_history,
            'allergy' => $request->allergy,
            'body_temperature' => $request->body_temperature,
            'blood_pressure' => $request->blood_pressure,
            'weight' => $request->weight,
            'height' => $request->height,
            'create_id' => Auth::user()->uid,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $appointment = Appointment::create($appointment_data);

        if(!$appointment) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat perjanjian, pastikan inputan anda benar dan silahkan coba lagi',
            ]);
        }

        $bill_uniq = Bill::createUniq();
        $bill_desc = "Telekonsultasi $schedule->doctor_name pada ". $request->consul_date . " " . $request->consul_time;
        $bill_expired = \Carbon\Carbon::now()->addMinutes("60");

        $bill_data = [
            'uniq' => $bill_uniq,
            'aid' => $appointment->aid,
            'description' => $bill_desc,
            'amount' => $schedule->fee,
            'expired_at' => $bill_expired->format('Y-m-d H:i:s'),
            'create_id' => Auth::user()->uid
        ];

        $bill = Bill::create($bill_data);

        if(!$bill) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat perjanjian, silahkan coba lagi',
            ]);
        }

        DB::commit();


        $midtrans = new Midtrans($schedule->branch_id);

        list($snapToken, $error) = $midtrans->getSnapToken(array(
            'transaction_details' => [
                'order_id' => $bill->uniq,
                'gross_amount' => $bill->amount,
            ],
            'customer_details' => [
                'first_name' => @$patient->first_name, 'last_name' => @$patient->last_name,
                'email' => Auth::user()->email, 'phone' => @$patient->phone_number,
            ],
            "expiry" => [
                'start_time' => \Carbon\Carbon::now()->format('Y-m-d H:i:s') . " +0700",
                'unit' => 'minutes', 'duration' => 60
            ],
            'callbacks' => [
                'finish' => URL::to("appointment_history/{$appointment->id}?status=finish"),
                'unfinish' => URL::to("appointment_history/{$appointment->id}?status=unfinish"),
                'error' => URL::to("appointment_history/{$appointment->id}?status=error"),
            ]
        ));
        
        if($snapToken) {
            $bill->update([
                'midtrans_snaptoken' => $snapToken
            ]);
        }

        try {
            $message = "Hallo {$patient->first_name},";
            $message .= "\n\nAnda telah membuat perjanjian telekonsultasi pada:";
            $message .= "\n\n*------------ INFORMASI ------------*\n";
            $message .= "\n*Tgl Konsultasi*\n{$appointment->consul_date} {$appointment->consul_time}\n";
            $message .= "\n*Dokter*\n{$schedule->doctor_name}\n";
            $message .= "\n*Poli*\n{$schedule->department}\n";
            // $message .= "\n*Cabang*\n{$schedule->branch}\n";
            $message .= "\n*Pasien*\n{$patient->full_name}\n";
            $message .= "\n*Tgl Daftar*\n{$appointment->created_at}\n";
            $message .= "\n\n*------------ PEMBAYARAN ------------*\n";
            $message .= "\n*Total*\nRp " . number_format($schedule->fee, 0) . "\n";
            $message .= "\n*Status*\nMenunggu Pembayaran\n";
            $message .= "\n*Batas waktu*\n{$bill->expired_at}\n";
            // $message .= "\n*Link Pembayaran*\n". short_link(URL::to('asdasd')) ."\n";
            $message .= "\n\n_*Segera selesaikan pembayaran anda sebelum waktu yang ditentukan.*_";
            $message .= "\n\nJika anda tidak melakukan pendaftaran, abaikan pesan ini.";
            $message .= "\n\nTerimakasih.";
            
            $send = Whatsapp::send($patient->phone_number,$message);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        return response()->json([
            'status' => true,
            'message' => 'Berhasil membuat perjanjian, segera lunasi tagihan sebelum batas waktu habis',
            'data' => [
                'appointment' => [
                    'id' => $appointment->aid,
                    'patient_id' => $patient->pid,
                    'schedule_id' => $schedule->schedule_id,
                    'consul_date' => $request->consul_date,
                    'consul_time' => $request->consul_time,
                    'main_complaint' => $request->main_complaint,
                    'disease_history' => $request->disease_history,
                    'allergy' => $request->allergy,
                    'body_temperature' => $request->body_temperature,
                    'blood_pressure' => $request->blood_pressure,
                    'weight' => $request->weight,
                    'height' => $request->height,
                ],
                'bill' => [
                    'id' => $bill->uniq,
                    'description' => $bill->description,
                    'amount' => $bill->amount,
                    'created_at' => $bill->created_at,
                    'expired_at' => $bill->expired_at,
                    'status' => $bill->status,
                ]
            ]
        ]);
    }

    public function List(Request $request) {
        $patient = Appointment::joinFullInfo()
                ->selectRaw("appointments.*,ftime(appointments.consul_time) as consul_time, patient.full_name as patient_name, doctor.display_name as doctor_name, doctor.pid as doctor_id, doctor_pic(doctor.profile_pic) as doctor_pic, patient_pic(patient.profile_pic) as patient_pic, departments.deid as department_id, departments.name as department, branches.bid as branch_id, branches.name as branch, id_age(patient.birth_date) as age, bills.expired_at as payment_expired_at, id_date(appointments.consul_date) as id_consul_date")
                ->orderBy('aid','DESC');

        $start_date = $request->query('start_date');
        $patient->when($start_date, function($query) use ($start_date) {
            $query->whereRaw("appointments.consul_date >= ?", [$start_date]);
        });

        $end_date = $request->query('end_date');
        $patient->when($end_date, function($query) use ($end_date) {
            $query->whereRaw("appointments.consul_date <= ?", [$end_date]);
        });

        $branch_id = $request->query('branch_id');
        $branch_ids = $branch_id ? explode(",", $branch_id) : [];

        $patient->when($branch_ids, function($query) use ($branch_ids){
            $query->whereIn('branches.bid', $branch_ids);
        });

        $department_id = $request->query('department_id');
        $department_ids = $department_id ? explode(",", $department_id) : [];

        $patient->when($department_ids, function($query) use ($department_ids){
            $query->whereIn('departments.deid', $department_ids);
        });

        $specialist_id = $request->query('specialist_id');
        $specialist_ids = $specialist_id ? explode(",", $specialist_id) : [];

        $patient->when($specialist_ids, function($query) use ($specialist_ids){
            $query->whereIn('specialists.sid', $specialist_ids);
        });

        $doctor_id = $request->query('doctor_id');
        $doctor_ids = $doctor_id ? explode(",", $doctor_id) : [];

        $patient->when($doctor_ids, function($query) use ($doctor_ids){
            $query->whereIn('doctor.pid', $doctor_ids);
        });

        $patient_id = $request->query('patient_id');
        $patient_ids = $patient_id ? explode(",", $patient_id) : [];

        $patient->when($patient_ids, function($query) use ($patient_ids){
            $query->whereIn('patient.pid', $patient_ids);
        });

        $appointment_id = $request->query('appointment_id');
        $appointment_ids = $appointment_id ? explode(",", $appointment_id) : [];

        $patient->when($appointment_ids, function($query) use ($appointment_ids){
            $query->whereIn('appointments.aid', $appointment_ids);
        });

        $status = $request->query('status');
        $statuses = $status ? explode(",", $status) : [];

        $patient->when($statuses, function($query) use ($statuses){
            $query->whereIn('appointments.status', $statuses);
        });
        
        if($request->query('paginate')=='true') $list = $patient->paginate($request->query('data_per_page', 10));
        else $list = $patient->get();

        $list->makeHidden([
            'create_id', 'delete_id', 'deleted_at', 'is_active'
        ]);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function Detail($appointment_id, Request $request) {
        $data = Appointment::joinFullInfo()
                ->selectRaw("appointments.*,ftime(appointments.consul_time) as consul_time, patient.full_name as patient_name, doctor.display_name as doctor_name, doctor.pid as doctor_id, doctor_pic(doctor.profile_pic) as doctor_pic, patient_pic(patient.profile_pic) as patient_pic, departments.deid as department_id, departments.name as department, branches.bid as branch_id, branches.name as branch, id_age(patient.birth_date) as age, bills.expired_at as payment_expired_at, schedules.duration, bills.amount as fee, id_date(appointments.consul_date) as id_consul_date, bills.midtrans_snaptoken as snaptoken, branches.midtrans_client_key as payment_key, appointments.start_consul, appointments.end_consul, false as can_re_register, zoom_meetings.join_url")
                ->JoinZoomMeeting()
                ->where('appointments.aid', $appointment_id);

        if(is_patient()) {
            $data->myFamily();
        } else if (is_doctor()) {
            $data->JoinSoap('left')
            ->JoinLaboratory('left')
            ->JoinRadiology('left')
            ->JoinPharmacy('left')
            ->selectRaw('soaps.subjective, soaps.objective, soaps.assesment, soaps.plan, laboratories.recommendation as lab_recom, laboratories.diagnosis as lab_diagnosis, laboratories.allergy as lab_allergy, radiologies.recommendation as rad_recom, radiologies.diagnosis as rad_diagnosis, radiologies.allergy as rad_allergy, pharmacies.recommendation as phar_recom, pharmacies.diagnosis as phar_diagnosis, pharmacies.allergy as phar_allergy, zoom_meetings.start_url')
            ->doctorUID(auth()->user()->uid);
        }
        else if (is_admin()) {
            $data->selectRaw('midtrans_paid_log');
        }

        $data = $data->first();

        if(!$data) {
            return response()->json([
                'status' => false,
                'message' => 'Perjanjian tidak ditemukan'
            ]);
        }

        $data->makeHidden([
            'create_id', 'delete_id', 'deleted_at', 'is_active'
        ]);

        if(is_doctor() && $request->input('start') && !$data->start_consul) {
            $data->update([
                'start_consul' => date('Y-m-d H:i:s'),
            ]);
        }

        $consul_finish = \Carbon\Carbon::parse($data->consul_date.' '.$data->consul_time)->addMinutes($data->duration);
        $data->consul_finish_date = $consul_finish->format('Y-m-d');
        $data->consul_finish_id_date = $consul_finish->translatedFormat('l, d F Y H:i');
        $data->consul_finish_time = $consul_finish->format('H:i');

        if(in_array($data->status,['payment_cancel', 'payment_expire']) && is_patient()) {
            $LSchedule = new LSchedule($data->scid);
            list($still_valid, $error) = $LSchedule->validConsulDate($data->consul_date, $data->consul_time);
            if($still_valid) {
                $data->can_re_register = true;
            }
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function Worklist(Request $request) {
        $list = Appointment::joinFullInfo()
                ->selectRaw('appointments.aid as appointment_id, patient.pid as patient_id, patient.full_name as patient_name, patient_pic(patient.profile_pic) as patient_pic, appointments.main_complaint, id_date(appointments.consul_date) as id_consul_date, appointments.consul_date, ftime(appointments.consul_time) as consul_time, appointments.status')
                ->worklist();

        if($query = $request->input('query')) {
            $list->whereRaw("(LOWER(patient.full_name) LIKE LOWER(?) OR appointments.aid = ?)", ["%$query%", intval($query)]);
        }

        $list = $list->paginate($request->input('data_per_page', 25));
        
        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function History(Request $request){
        
        $list = Appointment::joinFullInfo()
        ->selectRaw("appointments.*,appointments.aid as appointment_id, ftime(appointments.consul_time) as consul_time, patient.full_name as patient_name, doctor.display_name as doctor_name, doctor.pid as doctor_id, doctor_pic(doctor.profile_pic) as doctor_pic, patient_pic(patient.profile_pic) as patient_pic, departments.deid as department_id, departments.name as department, branches.bid as branch_id, branches.name as branch, id_age(patient.birth_date) as age, bills.expired_at as payment_expired_at, schedules.duration, bills.amount as fee, id_date(appointments.consul_date) as id_consul_date, bills.midtrans_snaptoken as snaptoken, branches.midtrans_client_key as payment_key")
        ->doctorUID(auth()->user()->uid)
        ->where('appointments.status', 'done')
        ->orderBy('appointments.consul_date', 'DESC');

        if($query = $request->input('query')) {
            $list->whereRaw("(LOWER(patient.full_name) LIKE LOWER(?) OR appointments.aid = ?)", ["%$query%", intval($query)]);
        }

        $list = $list->paginate($request->input('data_per_page', 25));

        return response()->json([
            'status' => true,
            'data'  => $list
        ]);
    }

    public function Incoming(Request $request) {
        $list = Appointment::joinFullInfo()
        ->selectRaw("appointments.*,appointments.aid as appointment_id, ftime(appointments.consul_time) as consul_time, patient.full_name as patient_name, doctor.display_name as doctor_name, doctor.pid as doctor_id, doctor_pic(doctor.profile_pic) as doctor_pic, patient_pic(patient.profile_pic) as patient_pic, departments.deid as department_id, departments.name as department, branches.bid as branch_id, branches.name as branch, id_age(patient.birth_date) as age, bills.expired_at as payment_expired_at, schedules.duration, bills.amount as fee, id_date(appointments.consul_date) as id_consul_date, bills.midtrans_snaptoken as snaptoken, branches.midtrans_client_key as payment_key")
        ->doctorUID(auth()->user()->uid)
        ->where('appointments.consul_date', '>', date('Y-m-d'))
        ->whereIn('appointments.status', ['waiting_consul'])
        ->orderBy('appointments.consul_date', 'DESC');

        if($query = $request->input('query')) {
            $list->whereRaw("(LOWER(patient.full_name) LIKE LOWER(?) OR appointments.aid = ?)", ["%$query%", intval($query)]);
        }

        $list = $list->paginate($request->input('data_per_page', 25));

        return response()->json([
            'status' => true,
            'data'  => $list
        ]);
    }

    public function ReRegister(Request $request) {
        if(!$appointment_id = $request->appointment_id) {
            return response()->json([
                'status' => false,
                'message'  => 'ID Perjanjian tidak ditemukan'
            ]);
        }

        $appointment = Appointment::joinFullInfo()
                ->selectRaw("appointments.*")
                ->where('appointments.aid', $appointment_id)
                ->myFamily()
                ->first();

        if(!$appointment) {
            return response()->json([
                'status' => false,
                'message'  => 'Perjanjian yang akan didaftarkan tidak ditemukan'
            ]);
        }
        
        $LSchedule = new LSchedule($appointment->scid);
        list($still_valid, $error) = $LSchedule->validConsulDate($appointment->consul_date, $appointment->consul_time);
        if(!$still_valid) {
            return response()->json([
                'status' => false,
                'message'  => $error
            ]);
        }

        $schedule = Schedule::JoinFullInfo()
                    ->selectRaw("persons.display_name as doctor_name, schedules.fee, branches.bid as branch_id, departments.name as department")
                    ->where('schedules.scid', $appointment->scid)
                    ->first();

        $patient = Person::find($appointment->patient_id);

        $bill = Bill::where('aid', $appointment->aid)->first();

        if(!$bill) {
            return response()->json([
                'status' => false,
                'message'  => 'Perjanjian yang akan didaftarkan tidak ditemukan'
            ]);
        }

        $new_appointment = array_remove($appointment->toArray(),['aid', 'status']);

        $new_appointment = array_merge($new_appointment, [
            'created_at' => date('Y-m-d H:i:s'),
            'create_id' => auth()->user()->uid,
            'last_update' => date('Y-m-d H:i:s'),
        ]);

        DB::BeginTransaction();

        $new_appointment = Appointment::create($new_appointment);


        if(!$new_appointment) {
            return response()->json([
                'status' => false,
                'message'  => 'Gagal melakukan pendaftaran'
            ]);
        }

        $bill_uniq = Bill::createUniq();
        $bill_desc = "Telekonsultasi $schedule->doctor_name pada ". $new_appointment->consul_date . " " . date("H:i", strtotime("2020-10-10 ". $new_appointment->consul_time));
        $bill_expired = \Carbon\Carbon::now()->addMinutes("60");

        $bill_data = [
            'uniq' => $bill_uniq,
            'aid' => $new_appointment->aid,
            'description' => $bill_desc,
            'amount' => $schedule->fee,
            'expired_at' => $bill_expired->format('Y-m-d H:i:s'),
            'create_id' => auth()->user()->uid
        ];

        $bill = Bill::create($bill_data);

        if(!$bill) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat perjanjian, silahkan coba lagi',
            ]);
        }

        DB::commit();

        $midtrans = new Midtrans($schedule->branch_id);

        list($snapToken, $error) = $midtrans->getSnapToken(array(
            'transaction_details' => [
                'order_id' => $bill->uniq,
                'gross_amount' => $bill->amount,
            ],
            'customer_details' => [
                'first_name' => @$patient->first_name, 'last_name' => @$patient->last_name,
                'email' => auth()->user()->email, 'phone' => @$patient->phone_number,
            ],
            "expiry" => [
                'start_time' => \Carbon\Carbon::now()->format('Y-m-d H:i:s') . " +0700",
                'unit' => 'minutes', 'duration' => 60
            ],
            'callbacks' => [
                'finish' => URL::to("appointment_history/{$new_appointment->aid}?status=finish"),
                'unfinish' => URL::to("appointment_history/{$new_appointment->aid}?status=unfinish"),
                'error' => URL::to("appointment_history/{$new_appointment->aid}?status=error"),
            ]
        ));
        
        if($snapToken) {
            $bill->update([
                'midtrans_snaptoken' => $snapToken
            ]);
        }

        try {
            $message = "Hallo {$patient->first_name},";
            $message .= "\n\nAnda telah membuat perjanjian telekonsultasi pada:";
            $message .= "\n\n*------------ INFORMASI ------------*\n";
            $message .= "\n*Tgl Konsultasi*\n{$new_appointment->consul_date} ".ftime($new_appointment->consul_time)."\n";
            $message .= "\n*Dokter*\n{$schedule->doctor_name}\n";
            $message .= "\n*Poli*\n{$schedule->department}\n";
            // $message .= "\n*Cabang*\n{$schedule->branch}\n";
            $message .= "\n*Pasien*\n{$patient->full_name}\n";
            $message .= "\n*Tgl Daftar*\n{$appointment->created_at}\n";
            $message .= "\n\n*------------ PEMBAYARAN ------------*\n";
            $message .= "\n*Total*\nRp " . number_format($schedule->fee, 0) . "\n";
            $message .= "\n*Status*\nMenunggu Pembayaran\n";
            $message .= "\n*Batas waktu*\n{$bill->expired_at}\n";
            // $message .= "\n*Link Pembayaran*\n". short_link(URL::to('asdasd')) ."\n";
            $message .= "\n\n_*Segera selesaikan pembayaran anda sebelum waktu yang ditentukan.*_";
            $message .= "\n\nJika anda tidak melakukan pendaftaran, abaikan pesan ini.";
            $message .= "\n\nTerimakasih.";
            
            $send = Whatsapp::send($patient->phone_number,$message);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        return response()->json([
            'status' => true,
            'message' => 'Berhasil membuat perjanjian, segera lunasi tagihan sebelum batas waktu habis',
            'data' => [
                'appointment' => [
                    'id' => $new_appointment->aid,
                    'patient_id' => $patient->pid,
                    'schedule_id' => $new_appointment->scid,
                    'consul_date' => $new_appointment->consul_date,
                    'consul_time' => ftime($new_appointment->consul_time),
                    'main_complaint' => $new_appointment->main_complaint,
                    'disease_history' => $new_appointment->disease_history,
                    'allergy' => $new_appointment->allergy,
                    'body_temperature' => $new_appointment->body_temperature,
                    'blood_pressure' => $new_appointment->blood_pressure,
                    'weight' => $new_appointment->weight,
                    'height' => $new_appointment->height,
                ],
                'bill' => [
                    'id' => $bill->uniq,
                    'description' => $bill->description,
                    'amount' => $bill->amount,
                    'created_at' => $bill->created_at,
                    'expired_at' => $bill->expired_at,
                    'status' => $bill->status,
                ]
            ]
        ]);
    }
}
