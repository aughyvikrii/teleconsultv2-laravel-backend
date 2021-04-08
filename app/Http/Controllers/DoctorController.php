<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \App\Models\{User, Person, Schedule, Appointment};
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
            'birth_date_y' => 'required|numeric|max:'.date('Y'),
            'birth_date_m' => 'required|numeric|min:1|max:12',
            'birth_date_d' => 'required|numeric|min:1|max:31',
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

            'birth_date_y.required' => 'Masukan tahun lahir',
            'birth_date_y.numeric' => 'Tahun lahir berupa angka',
            'birth_date_y.max' => 'Tahun lahir maksimal '. date('Y'),

            'birth_date_m.required' => 'Masukan bulan lahir',
            'birth_date_m.numeric' => 'Bulan lahir berupa angka',
            'birth_date_m.min' => 'Bulan lahir minimal 1',
            'birth_date_m.max' => 'Bulan lahir maksimal 12',

            'birth_date_d.required' => 'Masukan tanggal lahir',
            'birth_date_d.numeric' => 'Tanggal lahir berupa angka',
            'birth_date_d.min' => 'Tanggal lahir minimal 1',
            'birth_date_d.max' => 'Tanggal lahir maksimal 31',
            
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
        
        $request->birth_date = date('Y-m-d', strtotime("$request->birth_date_y-$request->birth_date_m-$request->birth_date_d"));

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

    public function List(Request $request, $for_print=false) {

        if($for_print) {
            $list = Person::JoinGender()
                ->JoinReligion()
                ->JoinMarriedStatus()
                ->JoinTitle()
                ->JoinIdentityType()
                ->selectRaw('persons.*, users.email')
                ->selectRaw("doctor_pic(persons.profile_pic) as profile_pic")
                ->isDoctor();
        } else {
            $list = Person::selectRaw('persons.pid as doctor_id, persons.display_name, users.email, persons.phone_number, persons.created_at')
                ->selectRaw("doctor_pic(persons.profile_pic) as profile_pic")
                ->isDoctor();
        }

        $name = $request->name;
        $list->when($name, function($query) use ($name){
            $query->whereRaw('LOWER(persons.display_name) LIKE ?', ["%". strtolower($name) ."%"]);
        });

        $email = $request->email;
        $list->when($email, function($query) use ($email){
            $query->whereRaw('LOWER(users.email) LIKE ?', ["%". strtolower($email) ."%"]);
        });

        $phone_number = $request->phone_number;
        $list->when($phone_number, function($query) use ($phone_number){
            $phone_number = format_phone($phone_number);
            $query->whereRaw("persons.phone_number LIKE '%$phone_number%'");
        });

        $specialist_id = $request->query('specialist_id');
        $specialist_ids = $specialist_id ? explode(",", $specialist_id) : [];

        $list->when($specialist_ids, function($query) use ($specialist_ids){
            $query->whereIn('specialists.sid', $specialist_ids);
        });

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
                , persons.birth_place, persons.sid as specialist_id, persons.gid as gender_id')
                ->selectRaw("id_age(persons.birth_date) as age, id_date(persons.birth_date) as birth_date_alt")
                ->selectRaw("doctor_pic(persons.profile_pic) as profile_pic")
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
     * Update Doctor Data ////// UNKNOWN AND UNUSED
     * 
     * @param   doctor_id int
     * @param   Request $request
     * @return  json
     */

     /*
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
    */

    /**
     * Update Doctor Information
     */

    public function Update($doctor_id, Request $request) {
        $valid = Validator::make($request->all(),[
            'birth_date_d' => 'required|numeric|min:1|max:31',
            'birth_date_m' => 'required|numeric|min:1|max:12',
            'birth_date_y' => 'required|numeric|max:'.date('Y'),
            'birth_place' => 'required',
            'display_name' => 'required',
            'email' => 'required|email',
            'gender' => 'required|in:1,2,3',
            'phone_number' => 'required',
            'specialist' => 'required|exists:specialists,sid',
        ],[
            'birth_date_d.required' => 'Masukan tanggal lahir',
            'birth_date_d.numeric' => 'Tanggal lahir berupa angka',
            'birth_date_d.min' => 'Tanggal lahir minimal 1',
            'birth_date_d.max' => 'Tanggal lahir maksimal 31',
            'birth_date_m.required' => 'Masukan bulan lahir',
            'birth_date_m.numeric' => 'Bulan lahir berupa angka',
            'birth_date_m.min' => 'Bulan lahir minimal 1',
            'birth_date_m.max' => 'Bulan lahir maksimal 12',
            'birth_date_y.required' => 'Masukan tahun lahir',
            'birth_date_y.numeric' => 'Tahun lahir berupa angka',
            'birth_date_y.max' => 'Tahun lahir maksimal '. date('Y'),
            'birth_place.required' => 'Masukan tempat lahir',
            'display_name.required' => 'Masukan nama tampilan',
            'email.required' => 'Masukan alamat email',
            'email.email' => 'Format email tidak valid',
            'gender.required' => 'Pilih jenis kelamin',
            'gender.in' => 'Pilihan jenis kelamin tidak valid',
            'phone_number.required' => 'Masukan nomor telepon',
            'specialist.required' => 'Pilih spesialis dokter',
            'specialist.exists' => 'Spesialis tidak valid',
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Input tidak valid',
                'errors' => $valid->errors()
            ]);
        }

        $doctor = Person::find($doctor_id);

        if(!$doctor_id) {
            return response()->json([
                'status' => false,
                'message'  => 'Dokter tidak ditemukan',
            ]);
        }

        $user = User::find($doctor->uid);

        $phone_number = format_phone($request->phone_number);

        $check_phone = Person::getByPhone($phone_number);
        if($check_phone) {
            if(@$doctor->pid !== @$check_phone->pid) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Nomor telepon sudah digunakan'
                ]);
            }
        }

        $check_email = User::getByEmail($request->email);

        if($check_email) {
            if($check_email->uid !== $user->uid) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Email sudah digunakan'
                ]);
            }
        }

        DB::BeginTransaction();

        $update = $doctor->update([
            'display_name' => $request->display_name,
            'birth_date' => $request->birth_date,
            'birth_place' =>  $request->birth_place,
            'sid' => $request->specialist,
            'phone_number' => $phone_number,
            'gid' => $request->gender,
        ]);

        if(!$update) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update informasi',
            ]);
        }

        $update_user = [
            'email' => $request->email,
            'phone_number' => $phone_number
        ];

        if($request->password) {
            $update_user['password'] = bcrypt($request->password);
        }

        $update = $user->update($update_user);

        if(!$update) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Gagal update informasi login'
            ]);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Berhasil update informasi',
            'data' => [
                'pid' => $doctor->pid
            ]
        ]);
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

    public function Schedule(Request $request) {

        $list = Schedule::selectRaw('persons.display_name as name, branches.name as branch,
                departments.name as department, doctor_pic(persons.profile_pic) as profile_pic')
                ->ScheduleGroup()
                ->joinFullInfo('join', false)
                ->groupBy(DB::Raw("persons.display_name, branches.name, departments.name, persons.profile_pic"));

        if($department_id = $request->input('department_id')) {
            $list->where('departments.deid', $department_id);
        }

        if($branch_id = $request->input('branch_id')) {
            $list->where('branches.bid', $branch_id);
        }

        if($doctor_name = $request->input('doctor_name')) {
            $doctor_name = strtolower($doctor_name);
            $list->where(DB::Raw('LOWER(persons.display_name)'), 'like', "%$doctor_name%");
        }

        if(!$request->input('paginate')) $list = $list->get();
        else $list = $list->paginate($request->input('data_per_page', 20));
        
        return response()->json([
            'status' => true,
            'data' =>  $list
        ]);
    }

    public function Appointments($doctor_id, Request $request)  {
        $search = $request->input('query');
        $paginate = $request->input('paginate', true);

        $list = Appointment::joinFullInfo()
                ->selectRaw("appointments.aid, patient.pid as patient_id, patient.full_name as patient, patient_pic(patient.profile_pic) as patient_pic, consul_date, ftime(consul_time) as consul_time, appointments.status, id_age(patient.birth_date) as age, id_date(consul_date) as id_consul_date")
                ->orderBy('aid','DESC')
                ->where('schedules.pid', $doctor_id);

        if($search) {
            $search = strtolower($search);
            $list->whereRaw('(appointments.aid = ? OR LOWER(patient.full_name) LIKE ?)', [ @intval($search), "%$search%"]);
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
}
