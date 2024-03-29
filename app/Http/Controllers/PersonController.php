<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \App\Models\{Person, Village, User, Appointment};
use Auth, DB;

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
                ->selectRaw("patient_pic(persons.profile_pic, persons.gid::int) as profile_pic");

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
        ->selectRaw('persons.*, users.email, patient_pic(persons.profile_pic) as profile_pic')
        ->where('persons.pid', $person_id)
        ->first();

        if(!$person) {
            return response()->json([
                'status' => false,
                'message' => 'Data person tidak ditemukan'
            ]);
        }

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

    public function living_area(Request $request) {
        $list = Village::fullJoin()
                ->selectRaw('villages.vid as village_id, initcap(villages.name) as village,
                districts.did as distric_id, initcap(districts.name) as distric,
                cities.cid as city_id, initcap(cities.name) as city,
                provinces.pvid as province_id, initcap(provinces.name) as province')
                ->orderBy('provinces.name','ASC');
                // ->orderBy([
                //     'a.name' => 'ASC',
                //     'b.name' => 'ASC',
                //     'c.name' => 'ASC',
                //     'd.name' => 'ASC'
                // ]);
        
        if($query = $request->input('query')) {
            $query = strtolower($query);
            $likeQuery = "%$query%";
            $list->whereRaw('LOWER(villages.name) LIKE ? OR LOWER(districts.name) LIKE ? OR LOWER(cities.name) LIKE ? OR LOWER(provinces.name) LIKE ?', [
                $likeQuery, $likeQuery, $likeQuery, $likeQuery
            ]);
        }

        if($village_id = $request->input('village_id')) {
            $list->whereRaw('villages.vid = ?', [$village_id]);
        }

        if(!empty($request->input('paginate'))) {
            $list = $list->paginate(25);
        } else {
            $list = $list->paginate(25);
        }

        return response()->json([
            'status' => true,
            'message' => 'Berhasil mendapatkan data',
            'data' => $list
        ]);
    }

    public function FamilyAdd(Request $request) {
        $valid = Validator::make($request->all(), [
            'address' => 'required',
            'birth_date_d' => 'required|numeric',
            'birth_date_m' => 'required|numeric',
            'birth_date_y' => 'required|numeric',
            'birth_place' => 'required',
            'first_name' =>  'required',
            'gender_id' => 'required|exists:genders,gid',
            'identity_number' => 'required',
            'identity_type_id' => 'required|exists:identity_type,itid',
            'married_status_id' => 'required|exists:married_status,msid',
            'profile_pic' => 'required',
            'religion_id' => 'required|exists:religions,rid',
            'title_id' => 'required|exists:titles,tid',
            'village_id' => 'required|exists:villages,vid',
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Input tidak valid',
                'errors' => $valid->errors()
            ]);
        }

        DB::BeginTransaction();

        $person = Person::addFamily([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'gid' => $request->input('gender_id'),
            'birth_place' => $request->input('birth_place'),
            'address' => $request->input('address'),
            'rid' => $request->input('religion_id'),
            'msid' => $request->input('married_status_id'),
            'tid' => $request->input('title_id'),
            'identity_number' => $request->input('identity_number'),
            'itid' => $request->input('identity_type_id'),
            'vid' => $request->input('village_id'),
            'last_update' => date('Y-m-d H:i:s'),
        ]);

        if(!$person) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambah anggota keluarga! periksa kembali inputan'
            ]);
        }

        DB::commit();

        return $this->FamilyUpdate($person->pid, $request);
    }

    public function FamilyUpdate($pid = null, Request $request) {
        $valid = Validator::make($request->all(), [
            'address' => 'required',
            'birth_date_d' => 'required|numeric',
            'birth_date_m' => 'required|numeric',
            'birth_date_y' => 'required|numeric',
            'birth_place' => 'required',
            'first_name' =>  'required',
            'gender_id' => 'required|exists:genders,gid',
            'identity_number' => 'required',
            'identity_type_id' => 'required|exists:identity_type,itid',
            'married_status_id' => 'required|exists:married_status,msid',
            'profile_pic' => 'required',
            'religion_id' => 'required|exists:religions,rid',
            'title_id' => 'required|exists:titles,tid',
            'village_id' => 'required|exists:villages,vid',
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Input tidak valid',
                'errors' => $valid->errors()
            ]);
        }

        $user = Auth::user();
        $user_id = $user->uid;

        if($pid) {
            $person = Person::FamilyMember($pid, $user_id);
        } else {
            $person = Person::where('uid', $user_id)->first();
        }

        if(!$person) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak ditemukan'
            ]);
        }

        $phone_number = format_phone($request->input('phone_number'));
        $validEmail = $validPhone = true;
        $emailUsed = User::emailIUsed($request->email);
        $phoneUsed = Person::getByPhone($phone_number);

        if(!$pid) { // User utama
            if( $emailUsed && @$emailUsed->uid != $user_id) $validEmail = false;
            if( $phoneUsed && @$phoneUsed->pid != $person->pid) $validPhone = false;
        } else if ($phoneUsed) {
            if(@$phoneUsed->pid != $user->uid) $validPhone = false;
        }

        if(!$validEmail || !$validPhone) {
            $errors = [];
            if(!$validPhone) $errors['phone_number'] = ['Nomor telepon sudah digunakan'];
            if(!$validEmail) $errors['email'] = ['Alamat email sudah digunakan'];

            return response()->json([
                'status' => false,
                'message' => 'Input tidak valid',
                'errors' => $errors
            ]);
        }

        $birth_date = $request->input('birth_date_y') . "-" . $request->input('birth_date_m') . "-" . $request->input('birth_date_d');

        DB::BeginTransaction();
        
        $update = $person->update([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'phone_number' => $phone_number,
            'gid' => $request->input('gender_id'),
            'birth_place' => $request->input('birth_place'),
            'birth_date' => $birth_date,
            'address' => $request->input('address'),
            'rid' => $request->input('religion_id'),
            'msid' => $request->input('married_status_id'),
            'tid' => $request->input('title_id'),
            'identity_number' => $request->input('identity_number'),
            'itid' => $request->input('identity_type_id'),
            'vid' => $request->input('village_id'),
            'last_update' => date('Y-m-d H:i:s')
        ]);

        if(!$update) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update informasi, silahkan coba lagi',
            ]);
        }
        
        if(!$pid) {
            $user_update = [ 'phone_number' => $phone_number ];
            if($request->email) $user_update['email'] = $request->email;
            $user->update($user_update);
        }

        if(preg_match('/data:image/', $request->input('profile_pic'))) {
            $old_pic = @storage_path('app/public/image/profile/'. @$person->profile_pic);
            $upload_pic = parent::saveProfilePicture($request->input('profile_pic'), $person->fmid."-".uniqid() );
    
            if(!$profile_pic = @$upload_pic['basename']) {
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal upload foto, silahkan coba foto lain'
                ]);
            }
    
            $update = $person->update([
                'profile_pic' => $profile_pic,
                'last_update' => date('Y-m-d H:i:s')
            ]);
    
            if(!$update) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal update foto profil, silahkan coba lagi',
                ]);
            }
            @unlink($old_pic);
        }

        DB::commit();
        return response()->json([
            'status' => true,
            'person_id' => $person->pid,
            'message' => 'Berhasil update informasi'
        ]);
    }

    public function FamilyList(Request $request) {
        $user = Auth::user();
        $person = Person::where('uid', $user->uid)->first();

        $list = Person::selectRaw('persons.full_name, id_date(persons.birth_date) as birth_date, id_age(persons.birth_date) as age
                , patient_pic(persons.profile_pic) as profile_pic, persons.pid as person_id
                , persons.phone_number, (CASE WHEN persons.uid IS NOT NULL THEN TRUE ELSE FALSE END) AS primary')
            ->where('fmid', $person->fmid)
            ->orderBy('full_name', 'ASC');

        if(!$request->input('paginate')) $list = $list->get();
        else {
            $list = $list->paginate($request->input('data_per_page', 10));
        }

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function FamilyDetail($person_id) {
        $user = Auth::user();
        $person_id = preg_replace("/[^0-9]/", "", $person_id);
        if(!$person_id) $person_id = 0;
        $person = Person::where('uid', $user->uid)->first();

        if(!$person) {
            return response()->json([
                'status' => false,
                'message' => 'Data akun tidak ditemukan'
            ]);
        }

        $person = Person::joinFullInfo()
        ->selectRaw('persons.first_name, persons.last_name, persons.full_name, persons.display_name, persons.phone_number
        , persons.created_at as join_at
        , genders.gid as gender_id, genders.name as gender
        , persons.identity_number, identity_type.name as identity_type, identity_type.itid as identity_type_id
        , married_status.msid as married_status_id, married_status.name as married_status
        , persons.phone_number, patient_pic(persons.profile_pic) as profile_pic
        , persons.birth_date, id_date(persons.birth_date) as birth_date_alt, id_age(persons.birth_date) as age, persons.birth_place
        , religions.name as religion, religions.rid as religion_id
        , titles.tid as title_id, titles.short as title_short, titles.name as title_long
        , persons.pid as person_id
        , persons.vid as village_id, persons.address')
        ->selectRaw("CONCAT(villages.name, '/ ', districts.name, '/ ', cities.name, '/ ', provinces.name) as living_area")
        ->whereRaw('persons.pid = ? AND persons.fmid = ?', [$person_id, $person->fmid])
        ->first();

        if(!$person) {
            return response()->json([
                'status' => false,
                'message' => 'Data pasien tidak ditemukan'
            ]);
        }
        
        return response()->json([
            'status' => true,
            'message' => 'Data person ditemukan',
            'data' =>  $person
        ]);
    }

    public function FamilyMemberDelete($person_id) {
        $user = Auth::user();
        $primary_user = Person::where('uid', $user->uid)->first();

        $person = Person::find($person_id);

        if(!$person || $person->fmid != $primary_user->fmid) {
            return response()->json([
                'status' => false,
                'message' => 'Data pasien tidak ditemukan'
            ]);
        }

        $appointment = Appointment::where('patient_id', $person->pid)
                        ->whereIn('status', ['done', 'waiting-consult', 'waiting-payment'])
                        ->get();

        if($appointment->count()) {
            return response()->json([
                'status' => false,
                'message' => 'Pasien ini memiliki perjanjian yang belum dibayar/ menunggu konsultasi/ sudah selesai, data tidak dapat dihapus'
            ]);
        }

        $delete = $person->delete();

        if(!$delete) {
            return response()->json([
                'status' =>  false,
                'message' => 'Gagal menghapus data'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menghapus data'
            ]);
        }
    }
}
