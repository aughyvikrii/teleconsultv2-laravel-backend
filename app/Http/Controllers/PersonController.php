<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \App\Models\{Person};

class PersonController extends Controller
{

    /**
     * Create New Person
     * 
     * @param Request $request
     * @return json
     */

    public function Create(Request $request) {
        $valid = Validator::make($request->all(),[
            'first_name' => 'required',
            'phone_number' => 'required',
            'gid'   => 'required|exists:genders',
            'birth_place' => 'required',
            'birth_date' => 'required|date',
            'address' => 'required',
            'profile_pic' => 'required',
            'rid' => 'required|exists:religions',
            'msid' => 'required|exists:married_status',
            'tid' => 'required|exists:titles',
            'identity_number' => 'required',
            'itid' => 'required|exists:identity_type',
            'lid'  => 'required|exists:level',
        ],[
            'first_name.required' => 'Masukan nama depan',
            'phone_number.required' => 'Masukan nomor telepon',
            'gid.required' => 'Pilih jenis kelamin',
            'gid.exists' => 'Jenis kelamin tidak valid',
            'birth_place.required' => 'Masukan tempat lahir',
            'birth_date.required' => 'Masukan tanggal lahir',
            'birth_date.date' => 'Format tanggal lahir tidak valid',
            'address.required' => 'Masukan alamat lengkap',
            'profile_pic.required' => 'Masukan foto terbaru',
            'rid.required' => 'Pilih agamar',
            'rid.exists' => 'Pilihan agama tidak valid',
            'msid.required' => 'Pilih status perkawinan',
            'msid.exists' => 'Status perkawinan tidak valid',
            'tid.required' => 'Pilih titel',
            'tid.exists' => 'Titel tidak valid',
            'identity_number.required' => 'Masukan nomor identitas',
            'itid.required' => 'Pilih tipe identitas',
            'itid.exists' => 'Pilihan identitas tidak valid',
            'lid.required' => 'Pilih level',
            'lid.exists' => 'Pilihan level tidak valid'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak valid',
                'errors' => $valid->errors(),
            ], 400);
        }

        $thumbnail_name = null;

        $person = Person::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'gid'   => $request->gid,
            'birth_place' => $request->birth_place,
            'birth_date' => $request->birth_date,
            'address' => $request->address,
            'profile_pic' => $request->profile_pic,
            'rid' => $request->rid,
            'msid' => $request->msid,
            'tid' => $request->tid,
            'identity_number' => $request->identity_number,
            'itid' => $request->itid,
            'lid'  => $request->lid,
        ]);

        if(!$person) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan data, silahkan coba lagi'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menambahkan data',
            ]);
        }
    }

    /**
     * Person List
     * 
     * @return json
     */

    public function List(Request $request) {
        $list = Person::joinUser()
                ->selectRaw('persons.pid, persons.full_name, users.email, persons.phone_number, persons.created_at, users.uid')
                ->selectRaw("profile_pic('".asset('storage/img/profile')."'::varchar, persons.profile_pic, users.lid, persons.gid) as profile_pic");

        if($query = $request->input('query')) {
            $query = strtolower($query);
            $list->whereRaw('LOWER(persons.full_name) LIKE ? OR persons.phone_number LIKE ? OR LOWER(users.email) LIKE ?',[
                $query, $query, $query,
            ]);
        }

        if($type = $request->input('type')) {
            if($type === 'patient') {
                $list->whereRaw('users.lid = ?', ['3']);
            }
            else if($type === 'doctor') {
                $list->whereRaw('users.lid = ?', ['2']);
            }
        }
        
        $list = $list->paginate($request->input('data_per_page', 10));

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    /**
     * Person Detail
     * 
     * @param person_id string
     * @return json
     */

    public function Detail($person_id) {
        $person = Person::joinFullInfo()
        ->selectRaw('persons.*, users.email')
        ->where('persons.pid', $person_id)
        ->first();

        if(!$person) {
            return response()->json([
                'status' => false,
                'message' => 'Data person tidak ditemukan'
            ]);
        }
        
        $person->profile_pic = profile_pic($person);

        $family = Person::selectRaw('family.full_name, family.pid')
                    ->joinFamily()
                    ->where('persons.pid', $person->pid)
                    ->get();
        
        return response()->json([
            'status' => true,
            'message' => 'Data person ditemukan',
            'data' =>  [
                'person' => $person,
                'family' => $family
            ]
        ]);

    }

    /**
     * Update Person Data
     * 
     * @param   person_id int
     * @param   Request $request
     * @return  json
     */

    public function Update($person_id, Request $request) {
        $person = Person::find($person_id);

        if(!$person) {
            return response()->json([
                'status' => false,
                'message' => 'Data person tidak ditemukan'
            ]);
        }

        $valid = Validator::make($request->all(),[
            'first_name' => 'required',
            'phone_number' => 'required',
            'gid'   => 'required|exists:genders',
            'birth_place' => 'required',
            'birth_date' => 'required|date',
            'address' => 'required',
            'profile_pic' => 'required',
            'rid' => 'required|exists:religions',
            'msid' => 'required|exists:married_status',
            'tid' => 'required|exists:titles',
            'identity_number' => 'required',
            'itid' => 'required|exists:identity_type',
            'lid'  => 'required|exists:level',
        ],[
            'first_name.required' => 'Masukan nama depan',
            'phone_number.required' => 'Masukan nomor telepon',
            'gid.required' => 'Pilih jenis kelamin',
            'gid.exists' => 'Jenis kelamin tidak valid',
            'birth_place.required' => 'Masukan tempat lahir',
            'birth_date.required' => 'Masukan tanggal lahir',
            'birth_date.date' => 'Format tanggal lahir tidak valid',
            'address.required' => 'Masukan alamat lengkap',
            'profile_pic.required' => 'Masukan foto terbaru',
            'rid.required' => 'Pilih agamar',
            'rid.exists' => 'Pilihan agama tidak valid',
            'msid.required' => 'Pilih status perkawinan',
            'msid.exists' => 'Status perkawinan tidak valid',
            'tid.required' => 'Pilih titel',
            'tid.exists' => 'Titel tidak valid',
            'identity_number.required' => 'Masukan nomor identitas',
            'itid.required' => 'Pilih tipe identitas',
            'itid.exists' => 'Pilihan identitas tidak valid',
            'lid.required' => 'Pilih level',
            'lid.exists' => 'Pilihan level tidak valid'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak valid',
                'errors' => $valid->errors(),
            ], 400);
        }

        $update = $person->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'gid'   => $request->gid,
            'birth_place' => $request->birth_place,
            'birth_date' => $request->birth_date,
            'address' => $request->address,
            'profile_pic' => $request->profile_pic,
            'rid' => $request->rid,
            'msid' => $request->msid,
            'tid' => $request->tid,
            'identity_number' => $request->identity_number,
            'itid' => $request->itid,
            'lid'  => $request->lid,
        ]);

        if(!$update) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update data, silahkan coba lagi'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil update data',
            ]);
        }
    }

    /**
     * Delete
     * 
     * @param person_id int
     * @return json
     */

    public function Delete($person_id) {
        $person = Person::find($person_id);

        if(!$person) {
            return response()->json([
                'status' => false,
                'message' => 'Data person tidak ditemukan'
            ]);
        }

        // Cek jika person ini digunakan

        $delete = $person->delete();

        if(!$delete) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal hapus person, silahkan coba lagi'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil hapus person',
            ]);
        }
    }
}
