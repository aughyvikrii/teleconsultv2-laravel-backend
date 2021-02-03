<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \App\Models\{User, Person, Schedule};
use Auth, DB;
use Carbon\Carbon;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class DoctorController extends Controller
{

    /**
     * Create New Doctor
     * 
     * @param Request $request
     * @return json
     */

    public function Create(Request $request) {
        
        $valid = Validator::make($request->all(),[
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'display_name' => 'required',
            'specialist' => 'required|exists:specialists,sid',
            'phone_number' => 'required',
            'gender' => 'required|in:1,2,3',
            'birth_place' => 'required',
            'birth_date' => 'required|date|date_format:Y-m-d',
            'branch' => 'required|exists:branches,bid',
            'department' => 'required|exists:departments,deid',
            'weekday' => 'required|digits_between:1,7',
            'fee' => 'required|numeric',
            'start_hour' => 'required|date_format:H:i',
            'end_hour' => 'required|date_format:H:i',
            'duration' => 'required|numeric'
        ],[
            'email.required' => 'Masukan alamat email',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'password.required' => 'Masukan password',
            'display.required' => 'Masukan nama tampilan',
            'specialist.required' => 'Pilih spesialis dokter',
            'specialist.exists' => 'Spesialis tidak valid',
            'phone_number.required' => 'Masukan nomor telepon',
            'gender.required' => 'Pilih jenis kelamin',
            'gender.in' => 'Pilihan jenis kelamin tidak valid',
            'birth_place.required' => 'Masukan tempat lahir',
            'birth_date.required' => 'Masukan tanggal lahir',
            'birth_date.date' =>  'Format tanggal tidak valid',
            'birth_date.date_format' => 'Format tanggal tidak valid',
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
        
        $phone_number = format_phone($request->phone_number);
        if($result = Person::PhoneUsed($phone_number)) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak valid',
                'errors' => [
                    'phone_number' => [
                        'nomor telepon sudah digunakan'
                    ]
                ],
            ]);
        }
        
        DB::BeginTransaction();

        $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->input('password','1234')),
            'phone_number' => $phone_number,
            'lid' => 2,
            'verified_at' => now()->format('Y-m-d H:i:s'),
            'is_active' => true,
            'create_id' => Auth::user()->uid,
            'created_at' => now()->format('Y-m-d H:i:s')
        ]);

        if(!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat akun dokter, silahkan coba lagi!',
            ]);
        }

        $person = Person::create([
            'uid' => $user->uid,
            'first_name' => $request->display_name,
            'display_name' => $request->display_name,
            'phone_number' => $phone_number,
            'gid' => $request->gender == 'male' ? '1' : '2',
            'birth_place' => $request->birth_place,
            'birth_date' => $request->birth_date,
            'address'  => '-',
            'rid' => 9,
            'msid' => 1,
            'tid' => 1,
            'identity_number' => '-',
            'itid'  => 1,
            'sid' => $request->specialist
        ]);

        if(!$person) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambah profil dokter, silahkan coba lagi!'
            ]);
        }

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

        if($thumbnail = $request->thumbnail) {
            $image = parent::saveProfilePicture($thumbnail, $person->pid.'-');
            if($image_name = @$image['basename']) {
                $person->update([
                    'profile_pic' => $image_name
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Berhasil menambah data dokter',
            'data' => [
                'pid' => $person->pid,
            ]
        ]);
    }

    /**
     * Doctor List
     * 
     * @return json
     */

    public function List(Request $request) {
        $list = Person::selectRaw('persons.pid as doctor_id, persons.display_name, users.email, persons.phone_number, persons.created_at')
                ->selectRaw("profile_pic('".asset('storage/img/profile')."', persons.profile_pic, users.lid, persons.gid) as profile_pic")
                ->isDoctor();

        if($query = $request->input('query')) {
            $query = strtolower($query);
            $list->whereRaw('(persons.pid = ? OR LOWER(persons.display_name) LIKE ? OR LOWER(users.email) LIKE ? OR persons.phone_number LIKE ?)', [
                @intval($query), "%{$query}%", "%{$query}%", "%{$query}%"
            ]);
        }

        if(!$request->input('paginate')) $list = $list->get();
        else {
            $list = $list->paginate($request->input('data_per_page', 10));
        }

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    /**
     * Doctor Detail
     * 
     * @param doctor_id string
     * @return json
     */

    public function Detail($doctor_id) {
        $doctor = Person::joinGender()
                ->selectRaw('persons.pid, persons.display_name, users.email, persons.phone_number, persons.created_at, genders.name as gender, persons.birth_date
                , persons.birth_place')
                ->selectRaw("age_trans(persons.birth_date) as age, date_translate(persons.birth_date) as birth_date_alt")
                ->selectRaw("profile_pic('".asset('storage/img/profile')."', persons.profile_pic, users.lid, persons.gid) as profile_pic")
                ->isDoctor()
                ->whereRaw('persons.pid = ?', [$doctor_id])
                ->first();
                
        if(!$doctor) {
            return response()->json([
                'status' => false,
                'message' => 'Data dokter tidak ditemukan'
            ]);
        }

        // $doctor->birth_date_alt = Carbon::parse($doctor->birth_date)->translatedFormat('l, d F Y');

        return response()->json([
            'status' => true,
            'message' => 'Data dokter ditemukan',
            'data' =>  [
                'person' => $doctor,
                'family' => []
            ]
        ]);

    }

    /**
     * Update Doctor Data
     * 
     * @param   doctor_id int
     * @param   Request $request
     * @return  json
     */

    public function Update($doctor_id, Request $request) {
        $doctor = Doctor::find($doctor_id);

        if(!$doctor) {
            return response()->json([
                'status' => false,
                'message' => 'Data dokter tidak ditemukan'
            ]);
        }

        $valid = Validator::make($request->all(),[
            'bid' => 'required|exists:branches',
            'deid' => 'required|exists:departments',
            'sid' => 'required|exists:specialists',
            'fee_consultation' => 'required',
            'is_active' => 'required|boolean',
        ],[
            'bid.required' => 'Pilih cabang',
            'bid.exists' => 'Pilihan cabang tidak tersedia',
            'deid.required' => 'Pilih departemen',
            'deid.exists' => 'Pilihan departemen tidak tersedia',
            'fee_consultation.required' => 'Masukan tarif konsultasi',
            'is_active.required' => 'Pilih status dokter',
            'is_active.boolean' => 'Pilihan status dokter tidak valid',
            'sid.required' => 'Pilih spesialis',
            'sid.exists' => 'Pilihan spesialis tidak tersedia'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak valid',
                'errors' => $valid->errors(),
            ], 400);
        }

        $update = $doctor->update([
            'bid' => $request->bid,
            'deid' => $request->deid,
            'sid' => $request->sid,
            'fee_consultation' => $request->fee_consultation,
            'is_active' => $request->is_active,
        ]);

        if(!$update) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update dokter, silahkan coba lagi'
            ], 403);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil update dokter',
            ]);
        }
    }

    /**
     * Delete
     * 
     * @param doctor_id int
     * @return json
     */

    public function Delete($doctor_id) {
        $doctor = Doctor::find($doctor_id);

        if(!$doctor) {
            return response()->json([
                'status' => false,
                'message' => 'Data dokter tidak ditemukan'
            ]);
        }

        // Cek jika doctor ini digunakan

        $delete = $doctor->delete();

        if(!$delete) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal hapus dokter, silahkan coba lagi'
            ], 403);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil hapus dokter',
            ]);
        }
    }

    public function Schedules($person_id, Request $request) {
        $schedule = Schedule::selectRaw('schedules.scid as schedule_id, branches.bid as branch_id,branches.name as branch
                    , departments.deid as department_id, departments.name as department, schedules.weekday, weekday_translate(schedules.weekday) as weekday_alt
                    , schedules.start_hour, schedules.end_hour, schedules.duration, schedules.fee, schedules.is_active')
                    ->joinPerson()->joinBranch()->joinDepartment()->joinCreator()
                    ->whereRaw('persons.pid = ?', [$person_id])
                    ->get();

        return response()->json([
            'status' => true,
            'data' => $schedule
        ]);
    }

    public function ScheduleAdd($person_id, Request $request) {
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
