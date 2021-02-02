<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth, DB;
use \App\Models\{Specialist};


class SpecialistController extends Controller
{

    /**
     * Create New Specialist
     * 
     * @param Request $request
     * @return json
     */

    public function Create(Request $request) {
        $valid = Validator::make($request->all(),[
            'title' => 'required',
            'alt_name' => 'required'
        ],[
            'title.required' => 'Masukan Gelar dokter',
            'alt_name.required' => 'Masukan nama lengkap gelar dokter'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak valid',
                'errors' => $valid->errors(),
            ]);
        }

        if(Specialist::titleExist($request->title)) {
            return response()->json([
                'status' => false,
                'message' => 'Spesialis dengan titel ini sudah ada',
                'errors' => [
                    'title' => [
                        'titel sudah digunakan'
                    ]
                ],
            ]);
        }

        $thumbnail_name = null;

        $specialist = Specialist::create([
            'title' => $request->title,
            'alt_name' => $request->alt_name,
            'create_id' => Auth::user()->uid,
        ]);

        if(!$specialist) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan spesialis, silahkan coba lagi'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menambahkan spesialis',
            ]);
        }
    }

    /**
     * Specialist List
     * 
     * @return json
     */

    public function List(Request $request) {

        if($request->input('all_data')) {
            $list = Specialist::selectRaw('specialists.sid, specialists.title, specialists.alt_name')
                    ->orderBy('specialists.title')
                    ->active()
                    ->get();
        } else {
            $list = Specialist::active()->orderBy('alt_name','ASC');
            if($query = $request->input('query')) {
                $list->whereRaw('LOWER(title) LIKE LOWER(?) OR LOWER(alt_name) LIKE LOWER(?) ',["%$query%", "%$query%"]);
            }
    
            $list = $list->paginate($request->input('data_per_page', 10));
        }

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    /**
     * Specialist Detail
     * 
     * @param specialist_id string
     * @return json
     */

    public function Detail($specialist_id) {
        $specialist = Specialist::find($specialist_id);

        if(!$specialist) {
            return response()->json([
                'status' => false,
                'message' => 'Data spesialis tidak ditemukan'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data spesialis ditemukan',
            'data' =>  $specialist
        ]);

    }

    /**
     * Update Specialist Data
     * 
     * @param   specialist_id int
     * @param   Request $request
     * @return  json
     */

    public function Update($specialist_id, Request $request) {
        $specialist = Specialist::find($specialist_id);

        if(!$specialist) {
            return response()->json([
                'status' => false,
                'message' => 'Data spesialis tidak ditemukan'
            ]);
        }

        $valid = Validator::make($request->all(),[
            'title' => 'required',
            'alt_name' => 'required'
        ],[
            'title.required' => 'Masukan Gelar dokter',
            'alt_name.required' => 'Masukan nama lengkap gelar dokter'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak valid',
                'errors' => $valid->errors(),
            ], 400);
        }

        $thumbnail_name = null;

        $update = $specialist->update([
            'title' => $request->title,
            'alt_name' => $request->alt_name
        ]);

        if(!$update) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update spesialis, silahkan coba lagi'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil update spesialis',
            ]);
        }
    }

    /**
     * Delete
     * 
     * @param specialist_id int
     * @return json
     */

    public function Delete($specialist_id) {
        $specialist = Specialist::find($specialist_id);

        if(!$specialist) {
            return response()->json([
                'status' => false,
                'message' => 'Data spesialis tidak ditemukan'
            ]);
        }

        // Cek jika specialist ini digunakan

        $delete = $specialist->delete();

        if(!$delete) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal hapus spesialis, silahkan coba lagi'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil hapus spesialis',
            ]);
        }
    }
}
