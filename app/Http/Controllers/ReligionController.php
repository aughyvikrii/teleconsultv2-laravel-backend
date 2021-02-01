<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \App\Models\{Religion};

class ReligionController extends Controller
{

    /**
     * Create New Religion
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

        $religion = Religion::create([
            'name' => $request->name,
        ]);

        if(!$religion) {
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
     * Religion List
     * 
     * @return json
     */

    public function List(Request $request) {
        $list = Religion::selectRaw('rid, name')
            ->orderBy('name','ASC')
            ->get(25);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    /**
     * Religion Detail
     * 
     * @param religion_id string
     * @return json
     */

    public function Detail($religion_id) {
        $religion = Religion::find($religion_id);

        if(!$religion) {
            return response()->json([
                'status' => false,
                'message' => 'Data tipe identitas tidak ditemukan'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data tipe identitas ditemukan',
            'data' =>  $religion
        ]);

    }

    /**
     * Update Religion Data
     * 
     * @param   religion_id int
     * @param   Request $request
     * @return  json
     */

    public function Update($religion_id, Request $request) {
        $religion = Religion::find($religion_id);

        if(!$religion) {
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

        $update = $religion->update([
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
     * @param religion_id int
     * @return json
     */

    public function Delete($religion_id) {
        $religion = Religion::find($religion_id);

        if(!$religion) {
            return response()->json([
                'status' => false,
                'message' => 'Data tipe identitas tidak ditemukan'
            ]);
        }

        // Cek jika religion ini digunakan

        $delete = $religion->delete();

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
