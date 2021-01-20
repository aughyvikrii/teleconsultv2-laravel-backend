<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \App\Models\{Doctor, User, Person};
use Illuminate\Support\Facades\DB;

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
            'uid' => 'required_without:email|exists:users',
            'email' => 'required_without:uid|email',
            'password' => 'required_with:email',
            'phone_number' => 'required_with:email',
            'first_name' => 'required_with:email',
            'gid' => 'required_with:email',
            'birth_place' => 'required_with:email',
            'birth_date' => 'required_with:email',
            'address' => 'required_with:email',
            'profile_pic' => 'required_with:email',
            'rid' => 'required_with:email',
            'msid' => 'required_with:email',
            'tid' => 'required_with:email',
            'identity_number' => 'required_with:email',
            'itid' => 'required_with:email',
            'bid' => 'required|exists:branches',
            'deid' => 'required|exists:dokterts',
            'sid' => 'required|exists:specialists',
            'fee_consultation' => 'required'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak valid',
                'errors' => $valid->errors(),
            ], 400);
        }

        DB::beginTransaction();

        $profile_pic = null;

        if(!$uid = $request->uid) {
            // Create new account

            $user = User::create([
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'phone_number' => format_phone($request->phone_number),
                'lid' => 2, // Level doctor
                'verified_at' => now(),
                'create_id' => 1,
            ]);

            if(!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Proses mendaftar akun gagal, silahkan coba lagi!'
                ]);
            }
            $user = User::find($user->uid);

            $person = Person::create([
                'uid'  => $user->uid,
                'first_name' =>  $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $user->phone_number,
                'gid' => $request->gid,
                'birth_place' => $request->birth_place,
                'birth_date' => $request->birth_date,
                'address' => $request->address,
                'profile_pic' =>  $profile_pic,
                'rid' => $request->rid,
                'msid' => $request->msid,
                'tid' => $request->tid,
                'identity_number' => $request->identity_number,
                'itid' => $request->itid,
                'lid' => 2,
                'create_id' => 1,
            ]);

            if(!$person) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Proses menambah data diri gagal, silahkan coba lagi!'
                ]);
            }
            $person = Person::find($person->pid);

        } else {
            $user = User::find($request->uid);
            $person = Person::where('uid', $user->uid)->first();
        }

        if($user->lid != '2') { // Tidak sama dengan level dokter
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambah dokter, level user tidak sesuai!'
            ]);
        }

        $doctor = Doctor::create([
            'pid' => $person->pid,
            'bid' => $request->bid,
            'deid' => $request->deid,
            'sid' => $request->sid,
            'fee_consultation' => $request->fee_consultation,
            'create_id' => 1
        ]);

        if(!$doctor) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambah dokter, silahkan coba lagi!'
            ]);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Berhasil menambahkan dokter',
        ]);
    }

    /**
     * Doctor List
     * 
     * @return json
     */

    public function List(Request $request) {
        $list = Doctor::getFullInfo()
                ->paginate(25);

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
        $doctor = Doctor::GetFullInfoByDoid($doctor_id);

        if(!$doctor) {
            return response()->json([
                'status' => false,
                'message' => 'Data dokter tidak ditemukan'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data dokter ditemukan',
            'data' =>  $doctor
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
            'deid' => 'required|exists:departements',
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
}
