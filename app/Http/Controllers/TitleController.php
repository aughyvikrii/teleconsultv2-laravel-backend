<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \App\Models\{Title};

class TitleController extends Controller
{

    /**
     * Create New Title
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

        $title = Title::create([
            'name' => $request->name,
        ]);

        if(!$title) {
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
     * Title List
     * 
     * @return json
     */

    public function List(Request $request) {
        $list = Title::selectRaw('tid, name, short')
            ->orderBy('name','ASC')
            ->get(25);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    /**
     * Title Detail
     * 
     * @param title_id string
     * @return json
     */

    public function Detail($title_id) {
        $title = Title::find($title_id);

        if(!$title) {
            return response()->json([
                'status' => false,
                'message' => 'Data tipe identitas tidak ditemukan'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data tipe identitas ditemukan',
            'data' =>  $title
        ]);

    }

    /**
     * Update Title Data
     * 
     * @param   title_id int
     * @param   Request $request
     * @return  json
     */

    public function Update($title_id, Request $request) {
        $title = Title::find($title_id);

        if(!$title) {
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

        $update = $title->update([
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
     * @param title_id int
     * @return json
     */

    public function Delete($title_id) {
        $title = Title::find($title_id);

        if(!$title) {
            return response()->json([
                'status' => false,
                'message' => 'Data tipe identitas tidak ditemukan'
            ]);
        }

        // Cek jika title ini digunakan

        $delete = $title->delete();

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
