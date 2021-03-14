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
        $list = Appointment::joinFullInfo()
                ->selectRaw("appointments.*,ftime(appointments.consul_time) as consul_time, patient.full_name as patient_name, doctor.display_name as doctor_name, doctor.pid as doctor_id, doctor_pic(doctor.profile_pic) as doctor_pic, patient_pic(patient.profile_pic) as patient_pic, departments.deid as department_id, departments.name as department, branches.bid as branch_id, branches.name as branch, id_age(patient.birth_date) as age, bills.expired_at as payment_expired_at")
                ->orderBy('aid','DESC');

        if(!$request->input('paginate')) $list = $list->get();
        else {
            $list = $list->paginate($request->input('data_per_page', 10));
        }

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
                ->selectRaw("appointments.*,ftime(appointments.consul_time) as consul_time, patient.full_name as patient_name, doctor.display_name as doctor_name, doctor.pid as doctor_id, doctor_pic(doctor.profile_pic) as doctor_pic, patient_pic(patient.profile_pic) as patient_pic, departments.deid as department_id, departments.name as department, branches.bid as branch_id, branches.name as branch, id_age(patient.birth_date) as age, bills.expired_at as payment_expired_at, schedules.duration, bills.amount as fee, id_date(appointments.consul_date) as id_consul_date, bills.midtrans_snaptoken as snaptoken, branches.midtrans_client_key as payment_key")
                ->whereRaw('appointments.aid = ?', [$appointment_id])
                ->first();

        if(!$data) {
            return response()->json([
                'status' => false,
                'message' => 'Perjanjian tidak ditemukan'
            ]);
        }

        $data->makeHidden([
            'create_id', 'delete_id', 'deleted_at', 'is_active'
        ]);

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
}
