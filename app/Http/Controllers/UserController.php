<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \App\Models\{User};

class UserController extends Controller
{

    /**
     * Create New User
     * 
     * @param Request $request
     * @return json
     */

    public function Create(Request $request) {
        $valid = Validator::make($request->all(),[
            'email' => 'required|email',
            'phone_number' => 'required',
            'lid'   => 'required',
            'password' => 'required'
        ],[
            'email.required' => 'Masukan alamat email',
            'email.email' => 'Format email tidak valid',
            'phone_number.required' => 'Masukan nomor telepon',
            'lid.required' => 'Pilih level user',
            'password.required' => 'Masukan password'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak valid',
                'errors' => $valid->errors(),
            ], 400);
        }

        $thumbnail_name = null;

        $user = User::create([
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'lid' => $request->lid,
            'password' => bcrypt($request->password)
        ]);

        if(!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan user, silahkan coba lagi'
            ], 403);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menambahkan user',
            ]);
        }
    }

    /**
     * User List
     * 
     * @return json
     */

    public function List(Request $request) {
        $list = User::orderBy('email','ASC')
            ->paginate(25);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    /**
     * User Detail
     * 
     * @param user_id string
     * @return json
     */

    public function Detail($user_id) {
        $user = User::find($user_id);

        if(!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Data user tidak ditemukan'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data user ditemukan',
            'data' =>  $user
        ]);

    }

    /**
     * Update User Data
     * 
     * @param   user_id int
     * @param   Request $request
     * @return  json
     */

    public function Update($user_id, Request $request) {
        $user = User::find($user_id);

        if(!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Data user tidak ditemukan'
            ]);
        }

        $valid = Validator::make($request->all(),[
            'email' => 'required|email',
            'phone_number' => 'required',
            'lid'   => 'required',
        ],[
            'email.required' => 'Masukan alamat email',
            'email.email' => 'Format email tidak valid',
            'phone_number.required' => 'Masukan nomor telepon',
            'lid.required' => 'Pilih level user'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak valid',
                'errors' => $valid->errors(),
            ], 400);
        }

        $update = $user->update([
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'lid' => $request->lid
        ]);

        if(!$update) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update user, silahkan coba lagi'
            ], 403);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil update user',
            ]);
        }
    }

    /**
     * Delete
     * 
     * @param user_id int
     * @return json
     */

    public function Delete($user_id) {
        $user = User::find($user_id);

        if(!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Data user tidak ditemukan'
            ]);
        }

        // Cek jika user ini digunakan

        $delete = $user->delete();

        if(!$delete) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal hapus user, silahkan coba lagi'
            ], 403);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil hapus user',
            ]);
        }
    }
}
