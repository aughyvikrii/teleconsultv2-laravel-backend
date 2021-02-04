<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth, DB;
use \App\Models\{Person, Schedule};

class ScheduleController extends Controller
{
    /**
     * List Schedule
     * 
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */

    public function List(Request $request) {
        $list = Schedule::selectRaw('schedules.scid as schedule_id, persons.pid as doctor_id, persons.display_name as doctor
                , branches.bid as branch_id, branches.name as branch, departments.deid as department_id, departments.name as department
                , schedules.weekday as weekday_id, id_weekday(schedules.weekday) as weekday, schedules.fee, schedules.start_hour,  schedules.end_hour
                , schedules.duration, schedules.is_active
                , specialists.sid as specialist_id, specialists.title as specialist_title, specialists.alt_name as specialist')
                ->selectRaw("doctor_pic(persons.profile_pic) as profile_pic")
                ->joinFullInfo();

        if($doctor_name = $request->input('filters.doctor')) {
            $doctor_name = strtolower($doctor_name);
            $list->whereRaw('LOWER(persons.display_name) LIKE ?', ["%$doctor_name%"]);
        }

        if($specialist_id = $request->input('filters.specialist')) {
            if(!is_array($specialist_id)) $specialist_id = [$specialist_id];
            $list->whereIn('specialists.sid',$specialist_id);
        }

        if($branch_id = $request->input('filters.branch')) {
            if(!is_array($branch_id)) $branch_id = [$branch_id];
            $list->whereIn('branches.bid', $branch_id);
        }

        if($department_id = $request->input('filters.department')) {
            if(!is_array($department_id)) $department_id = [$department_id];
            $list->whereIn('departments.deid', $department_id);
        }

        if($weekday = $request->input('filters.weekday')) {
            if(!is_array($weekday)) $weekday = [$weekday];
            $list->whereIn('schedules.weekday', $weekday);
        }

        if(!$request->paginate) $list = $list->get();
        else {
            $list = $list->paginate($request->input('data_per_page', 10));
        }

        return response()->json([
            'status' => true,
            'message' => 'Data ditemukan',
            'data' => $list
        ]);
    }

    public function DoctorSchedule($person_id, Request $request) {
        $schedule = Schedule::selectRaw('schedules.scid as schedule_id, branches.bid as branch_id,branches.name as branch
                    , departments.deid as department_id, departments.name as department, schedules.weekday, id_weekday(schedules.weekday) as weekday_alt
                    , schedules.start_hour, schedules.end_hour, schedules.duration, schedules.fee, schedules.is_active')
                    ->joinPerson()->joinBranch()->joinDepartment()->joinCreator()
                    ->whereRaw('persons.pid = ?', [$person_id])
                    ->get();

        return response()->json([
            'status' => true,
            'data' => $schedule
        ]);
    }

    public function DoctorScheduleAdd($person_id, Request $request) {
        $valid = Validator::make($request->all(), [
            'branch' => 'required|exists:branches,bid',
            'department' => 'required|exists:departments,deid',
            'weekday' => 'required|digits_between:1,7',
            'fee' => 'required|numeric',
            'start_hour' => 'required|date_format:H:i',
            'end_hour' => 'required|date_format:H:i',
            'duration' => 'required|numeric'
        ],[
            'branch.required' => 'Pilih cabang',
            'branch.exists' => 'Cabang tidak valid',
            'department.required' => 'Pilih departemen',
            'department.exists' => 'Departemen tidak valid',
            'weekday.required' => 'Pilih hari praktek',
            'weekday.digits_between' => 'Pilihan hari tidak valid',
            'fee.required' => 'Masukan tarif konsultasi',
            'fee.numeric' => 'Format tarif hanya berupa angka',
            'start_hour.required' => 'Masukan jam mulai praktek',
            'start_hour.date_format' => 'Format jam tidak valid',
            'end_hour.required' => 'Masukan jam selesai praktek',
            'end_hour.date_format' => 'Format jam tidak valid',
            'duration.required' => 'Masukan durasi praktek',
            'duration.numeric' => 'Format durasi hanya berupa angka'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak valid',
                'errors' => $valid->errors(),
            ]);
        }

        $person = Person::selectRaw('persons.*')
                    ->IsDoctor()
                    ->where('persons.pid', $person_id)
                    ->first();

        if(!$person) {
            return response()->json([
                'status' => false,
                'message' => 'Dokter tidak valid!'
            ]);
        }

        DB::BeginTransaction();

        $schedule = Schedule::create([
            'pid' => $person->pid,
            'bid' => $request->branch,
            'deid' => $request->department,
            'weekday' =>  $request->weekday,
            'fee' =>  $request->fee,
            'start_hour' => $request->start_hour,
            'end_hour' => $request->end_hour,
            'duration' => $request->duration,
            'create_id' => Auth::user()->uid,
        ]);

        if(!$schedule) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambah jadwal dokter, silahkan coba lagi!'
            ]);
        }

        DB::commit();
        return response()->json([
            'status' => true,
            'message' => 'Berhasil menambah data',
            'data' => [
                'schedule_id' => $schedule->scid
            ]
        ]);
    }
}
