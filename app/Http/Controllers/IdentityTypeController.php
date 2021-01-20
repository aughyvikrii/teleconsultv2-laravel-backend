<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \App\Models\{IdentityType};

class IdentityTypeController extends Controller
{

    /**
     * Create New IdentityType
     * 
     * @param Request $request
     * @return json
     */

    public function Create(Request $request) {
        $valid = Validator::make($request->all(),[
            'name' => 'required',
        ],[
            'name.required' => 'Masukan Nama Tipe Identitas',
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak valid',
                'errors' => $valid->errors(),
            ], 400);
        }

        $thumbnail_name = null;

        $identity_type = IdentityType::create([
            'name' => $request->name,
        ]);

        if(!$identity_type) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan tipe identitas, silahkan coba lagi'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menambahkan tipe identitas',
            ]);
        }
    }

    /**
     * IdentityType List
     * 
     * @return json
     */

    public function List(Request $request) {
        $list = IdentityType::orderBy('name','ASC')
            ->paginate(25);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    /**
     * IdentityType Detail
     * 
     * @param identity_type_id string
     * @return json
     */

    public function Detail($identity_type_id) {
        $identity_type = IdentityType::find($identity_type_id);

        if(!$identity_type) {
            return response()->json([
                'status' => false,
                'message' => 'Data tipe identitas tidak ditemukan'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data tipe identitas ditemukan',
            'data' =>  $identity_type
        ]);

    }

    /**
     * Update IdentityType Data
     * 
     * @param   identity_type_id int
     * @param   Request $request
     * @return  json
     */

    public function Update($identity_type_id, Request $request) {
        $identity_type = IdentityType::find($identity_type_id);

        if(!$identity_type) {
            return response()->json([
                'status' => false,
                'message' => 'Data tipe identitas tidak ditemukan'
            ]);
        }

        $valid = Validator::make($request->all(),[
            'name' => 'required',
        ],[
            'name.required' => 'Masukan Nama Tipe Identitas',
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak valid',
                'errors' => $valid->errors(),
            ], 400);
        }

        $thumbnail_name = null;

        $update = $identity_type->update([
            'name' => $request->name,
        ]);

        if(!$update) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update tipe identitas, silahkan coba lagi'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil update tipe identitas',
            ]);
        }
    }

    /**
     * Delete
     * 
     * @param identity_type_id int
     * @return json
     */

    public function Delete($identity_type_id) {
        $identity_type = IdentityType::find($identity_type_id);

        if(!$identity_type) {
            return response()->json([
                'status' => false,
                'message' => 'Data tipe identitas tidak ditemukan'
            ]);
        }

        // Cek jika identity_type ini digunakan

        $delete = $identity_type->delete();

        if(!$delete) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal hapus tipe identitas, silahkan coba lagi'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil hapus tipe identitas',
            ]);
        }
    }
}
