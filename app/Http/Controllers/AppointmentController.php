<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth, DB;
use \App\Models\{Schedule, Person, Appointment, Bill};
use \App\Libraries\Schedule as LSchedule;

class AppointmentController extends Controller
{
    public function Create(Request $request) {
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
                ->selectRaw("appointments.*,ftime(appointments.consul_time) as consul_time, patient.full_name as patient_name, doctor.display_name as doctor_name, doctor.pid as doctor_id, doctor_pic(doctor.profile_pic) as doctor_pic, patient_pic(patient.profile_pic) as patient_pic, departments.deid as department_id, departments.name as department, branches.bid as branch_id, branches.name as branch, id_age(patient.birth_date) as age");

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
}
