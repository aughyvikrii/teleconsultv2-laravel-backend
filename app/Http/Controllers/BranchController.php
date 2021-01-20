<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \App\Models\{Branch};

class BranchController extends Controller
{
    /**
     * Branch List
     * 
     * @return json
     */

    public function List(Request $request) {
        $list = Branch::orderBy('name','ASC')
            ->paginate(25);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    /**
     * Branch Detail
     * 
     * @param branch_id string
     * @return json
     */

    public function Detail($branch_id) {
        $branch = Branch::find($branch_id);

        if(!$branch) {
            return response()->json([
                'status' => false,
                'message' => 'Data cabang tidak ditemukan'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data cabang ditemukan',
            'data' =>  $branch
        ]);

    }

    /**
     * Create New Branch
     * 
     * @param Request $request
     * @return json
     */

    public function Create(Request $request) {
        $valid = Validator::make($request->all(),[
            'code'  => 'required',
            'company' => 'required',
            'name' => 'required',
        ],[
            'code.required' => 'Masukan kode cabang',
            'company.required' => 'Masukan nama perusahaan',
            'name.required' => 'Masukan nama cabang'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak valid',
                'errors' => $valid->errors(),
            ], 400);
        }

        $thumbnail_name = null;

        $branch = Branch::create([
            'code' => $request->code,
            'company' => $request->company,
            'name' => $request->name,
            'npwp' => $request->npwp,
            'bank_name'=> $request->bank_name,
            'account_number' => $request->account_number,
            'phone_number' => $request->phone_number,
            'whatsapp_number' => $request->whatsapp_number,
            'his_api_production' => $request->his_api_production,
            'his_api_development' => $request->his_api_development,
            'his_api_user' => $request->his_api_user,
            'his_api_pass' => $request->his_api_pass,
            'espay_commcode' => $request->espay_commcode,
            'espay_api_key' => $request->espay_api_key,
            'espay_password' => $request->espay_password,
            'espay_signature' => $request->espay_signature,
            'thumbnail' => $thumbnail_name
        ]);

        if(!$branch) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan cabang, silahkan coba lagi'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menambahkan cabang',
            ]);
        }
    }

    /**
     * Update Branch Data
     * 
     * @param   branch_id int
     * @param   Request $request
     * @return  json
     */

    public function Update($branch_id, Request $request) {
        $branch = Branch::find($branch_id);

        if(!$branch) {
            return response()->json([
                'status' => false,
                'message' => 'Data cabang tidak ditemukan'
            ]);
        }

        $valid = Validator::make($request->all(),[
            'code'  => 'required',
            'company' => 'required',
            'name' => 'required',
        ],[
            'code.required' => 'Masukan kode cabang',
            'company.required' => 'Masukan nama perusahaan',
            'name.required' => 'Masukan nama cabang'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak valid',
                'errors' => $valid->errors(),
            ], 400);
        }

        $thumbnail_name = null;

        $update = $branch->update([
            'code' => $request->code,
            'company' => $request->company,
            'name' => $request->name,
            'npwp' => $request->npwp,
            'bank_name'=> $request->bank_name,
            'account_number' => $request->account_number,
            'phone_number' => $request->phone_number,
            'whatsapp_number' => $request->whatsapp_number,
            'his_api_production' => $request->his_api_production,
            'his_api_development' => $request->his_api_development,
            'his_api_user' => $request->his_api_user,
            'his_api_pass' => $request->his_api_pass,
            'espay_commcode' => $request->espay_commcode,
            'espay_api_key' => $request->espay_api_key,
            'espay_password' => $request->espay_password,
            'espay_signature' => $request->espay_signature,
            'thumbnail' => $thumbnail_name
        ]);

        if(!$update) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update cabang, silahkan coba lagi'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil update cabang',
            ]);
        }
    }

    /**
     * Delete
     * 
     * @param branch_id int
     * @return json
     */

    public function Delete($branch_id) {
        $branch = Branch::find($branch_id);

        if(!$branch) {
            return response()->json([
                'status' => false,
                'message' => 'Data cabang tidak ditemukan'
            ]);
        }

        // Cek jika branch ini digunakan

        $delete = $branch->delete();

        if(!$delete) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal hapus cabang, silahkan coba lagi'
            ]);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil hapus cabang',
            ]);
        }
    }
}
