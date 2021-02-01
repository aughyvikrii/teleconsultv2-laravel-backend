<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \App\Models\{MarriedStatus};

class MarriedStatusController extends Controller
{

    /**
     * Create New MarriedStatus
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

        $married_status = MarriedStatus::create([
            'name' => $request->name,
        ]);

        if(!$married_status) {
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
     * MarriedStatus List
     * 
     * @return json
     */

    public function List(Request $request) {
        $list = MarriedStatus::selectRaw('msid, name')
            ->orderBy('name','ASC')
            ->get(25);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    /**
     * MarriedStatus Detail
     * 
     * @param married_status_id string
     * @return json
     */

    public function Detail($married_status_id) {
        $married_status = MarriedStatus::find($married_status_id);

        if(!$married_status) {
            return response()->json([
                'status' => false,
                'message' => 'Data tipe identitas tidak ditemukan'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data tipe identitas ditemukan',
            'data' =>  $married_status
        ]);

    }

    /**
     * Update MarriedStatus Data
     * 
     * @param   married_status_id int
     * @param   Request $request
     * @return  json
     */

    public function Update($married_status_id, Request $request) {
        $married_status = MarriedStatus::find($married_status_id);

        if(!$married_status) {
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

        $update = $married_status->update([
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
     * @param married_status_id int
     * @return json
     */

    public function Delete($married_status_id) {
        $married_status = MarriedStatus::find($married_status_id);

        if(!$married_status) {
            return response()->json([
                'status' => false,
                'message' => 'Data tipe identitas tidak ditemukan'
            ]);
        }

        // Cek jika married_status ini digunakan

        $delete = $married_status->delete();

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
