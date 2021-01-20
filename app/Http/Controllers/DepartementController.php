<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \App\Models\{Departement};

class DepartementController extends Controller
{

    /**
     * Create New Departement
     * 
     * @param Request $request
     * @return json
     */

    public function Create(Request $request) {
        $valid = Validator::make($request->all(),[
            'name' => 'required',
        ],[
            'name.required' => 'Masukan nama Departemen'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak valid',
                'errors' => $valid->errors(),
            ], 400);
        }

        $thumbnail_name = null;

        $departement = Departement::create([
            'name' => $request->name,
            'thumbnail' => $thumbnail_name
        ]);

        if(!$departement) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan departemen, silahkan coba lagi'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menambahkan departemen',
            ]);
        }
    }

    /**
     * Departement List
     * 
     * @return json
     */

    public function List(Request $request) {
        $list = Departement::orderBy('name','ASC')
            ->paginate(25);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    /**
     * Departement Detail
     * 
     * @param departement_id string
     * @return json
     */

    public function Detail($departement_id) {
        $departement = Departement::find($departement_id);

        if(!$departement) {
            return response()->json([
                'status' => false,
                'message' => 'Data departemen tidak ditemukan'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data departemen ditemukan',
            'data' =>  $departement
        ]);

    }

    /**
     * Update Departement Data
     * 
     * @param   departement_id int
     * @param   Request $request
     * @return  json
     */

    public function Update($departement_id, Request $request) {
        $departement = Departement::find($departement_id);

        if(!$departement) {
            return response()->json([
                'status' => false,
                'message' => 'Data departemen tidak ditemukan'
            ]);
        }

        $valid = Validator::make($request->all(),[
            'name' => 'required',
        ],[
            'name.required' => 'Masukan nama departemen'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak valid',
                'errors' => $valid->errors(),
            ], 400);
        }

        $thumbnail_name = null;

        $update = $departement->update([
            'name' => $request->name,
            'thumbnail' => $thumbnail_name
        ]);

        if(!$update) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update departemen, silahkan coba lagi'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil update departemen',
            ]);
        }
    }

    /**
     * Delete
     * 
     * @param departement_id int
     * @return json
     */

    public function Delete($departement_id) {
        $departement = Departement::find($departement_id);

        if(!$departement) {
            return response()->json([
                'status' => false,
                'message' => 'Data departemen tidak ditemukan'
            ]);
        }

        // Cek jika departement ini digunakan

        $delete = $departement->delete();

        if(!$delete) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal hapus departemen, silahkan coba lagi'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil hapus departemen',
            ]);
        }
    }
}
