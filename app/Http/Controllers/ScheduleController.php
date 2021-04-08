<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth, DB;
use \Carbon\Carbon;
use \App\Models\{Person, Schedule};
use \App\Libraries\Schedule as ScheduleLib;

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

        $doctor_id = $request->query('doctor_id');
        $doctor_ids = $doctor_id ? explode(",", $doctor_id) : [];

        $list->when($doctor_ids, function($query) use ($doctor_ids){
            $query->whereIn('persons.pid', $doctor_ids);
        });

        $branch_id = $request->query('branch_id');
        $branch_ids = $branch_id ? explode(",", $branch_id) : [];

        $list->when($branch_ids, function($query) use ($branch_ids){
            $query->whereIn('branches.bid', $branch_ids);
        });

        $department_id = $request->query('department_id');
        $department_ids = $department_id ? explode(",", $department_id) : [];

        $list->when($department_ids, function($query) use ($department_ids){
            $query->whereIn('departments.deid', $department_ids);
        });

        $specialist_id = $request->query('specialist_id');
        $specialist_ids = $specialist_id ? explode(",", $specialist_id) : [];

        $list->when($specialist_ids, function($query) use ($specialist_ids){
            $query->whereIn('specialists.sid', $specialist_ids);
        });


        $weekday_id = $request->query('weekday_id');
        $weekday_ids = $weekday_id ? explode(",", $weekday_id) : [];

        $list->when($weekday_ids, function($query) use ($weekday_ids){
            $query->whereIn('schedules.weekday', $weekday_ids);
        });

        // if($doctor_name = $request->input('filters.doctor')) {
        //     $doctor_name = strtolower($doctor_name);
        //     $list->whereRaw('LOWER(persons.display_name) LIKE ?', ["%$doctor_name%"]);
        // }

        // if($specialist_id = $request->input('filters.specialist')) {
        //     if(!is_array($specialist_id)) $specialist_id = [$specialist_id];
        //     $list->whereIn('specialists.sid',$specialist_id);
        // }

        // if($branch_id = $request->input('filters.branch')) {
        //     if(!is_array($branch_id)) $branch_id = [$branch_id];
        //     $list->whereIn('branches.bid', $branch_id);
        // }

        // if($department_id = $request->input('filters.department')) {
        //     if(!is_array($department_id)) $department_id = [$department_id];
        //     $list->whereIn('departments.deid', $department_id);
        // }

        // if($weekday = $request->input('filters.weekday')) {
        //     if(!is_array($weekday)) $weekday = [$weekday];
        //     $list->whereIn('schedules.weekday', $weekday);
        // }

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

    public function Update($schedule_id, Request $request) {
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

        $schedule = Schedule::find($schedule_id);
        if(!$schedule) {
            return response()->json([
                'status' => false,
                'message' => 'Jadwal praktek tidak ditemukan'
            ]);
        };

        DB::BeginTransaction();

        $update = $schedule->update([
            'bid' => $request->branch,
            'deid' => $request->department,
            'weekday' =>  $request->weekday,
            'fee' =>  $request->fee,
            'start_hour' => $request->start_hour,
            'end_hour' => $request->end_hour,
            'duration' => $request->duration,
            'last_update' => date('Y-m-d H:i:s')
        ]);

        if(!$update) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Gagal update jadwal dokter, silahkan coba lagi!'
            ]);
        }

        DB::commit();
        return response()->json([
            'status' => true,
            'message' => 'Berhasil update data',
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

    public function ScheduleDate($scid) {
        $schedule = Schedule::apiScheduleDetailByScid($scid);
        
        if(!$schedule) {
            return response()->json([
                'status' => false,
                'message' => 'Jadwal Telekonsultasi tidak ditemukan',
            ]);
        }

        $schedule_lib = new ScheduleLib($schedule);

        $schedule->date = $schedule_lib->getDateTeleconsult();

        return response()->json([
            'status' => true,
            'data' => $schedule
        ]);
    }

    public function ScheduleTime($scid, Request $request) {

        if(!$date = $request->input('date')) {
            return response()->json([
                'status' => false,
                'message' => 'Pilih tanggal'
            ]);
        }

        $schedule = Schedule::apiScheduleDetailByScid($scid);
        
        if(!$schedule) {
            return response()->json([
                'status' => false,
                'message' => 'Jadwal Telekonsultasi tidak ditemukan',
            ]);
        }

        $schedule_lib = new ScheduleLib($schedule);

        $list_date = $schedule_lib->getDateTeleconsult(true);

        if(!isset($list_date[$date])) {
            return response()->json([
                'status' => false,
                'message' => 'Tanggal tidak sesuai jadwal'
            ]);
        }

        $list_time = $schedule_lib->getTimeDetail(false, false, $date);

        return response()->json([
            'status' => true,
            'data' => $list_time
        ]);
    }
}
