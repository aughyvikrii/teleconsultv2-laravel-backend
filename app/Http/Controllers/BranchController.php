<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Auth, DB;

use \App\Models\{Branch};

class BranchController extends Controller
{
    /**
     * Branch List
     * 
     * @return json
     */

    public function List(Request $request) {
        
        if($request->input('all_data')) {
            $list = Branch::selectRaw('branches.bid as branch_id, branches.name, branch_pic(branches.thumbnail) as thumbnail')
                ->active()
                ->orderBy('branches.name', 'ASC')
                ->get();
        } else {
            $list = Branch::selectRaw("branches.bid as branch_id, branches.code, branches.name
                    , branch_pic(branches.thumbnail) as thumbnail, branches.is_active")
                    ->orderBy('name','ASC');
        
            if($query = $request->input('query')) {
                $list->whereRaw('LOWER(branches.code) LIKE LOWER(?) OR LOWER(branches.name) LIKE LOWER(?) ',["%$query%", "%$query%"]);
            }

            $list = $list->paginate($request->input('data_per_page', 10));
        }

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
        $branch = Branch::selectRaw('branches.bid as branch_id
                    ,branches.code, branches.company, branches.name, branches.npwp, branches.bank_name
                    ,branches.account_number, branches.phone_number, branches.whatsapp_number, branches.is_active')
                ->selectRaw(" branch_pic(branches.thumbnail) as thumbnail")
                ->where('bid', $branch_id)
                ->first();

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
            ]);
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
            'thumbnail' => $thumbnail_name,
            'create_id' => Auth::user()->uid,
        ]);

        if(!$branch) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan cabang, silahkan coba lagi'
            ]);
        } else {

            if($image_64 = $request->input('thumbnail')) {
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
    
                $replace = substr($image_64, 0, strpos($image_64, ',')+1); 
              
              // find substring fro replace here eg: data:image/png;base64,
              
               $image = str_replace($replace, '', $image_64); 
                
               $image = str_replace(' ', '+', $image); 
               $imageName = $branch->bid.''.Str::random(10).'.'.$extension;
               Storage::disk('public')->put('image/branch/'.$imageName, base64_decode($image));
               $thumbnail_name = $imageName;
               $branch->update([
                   'thumbnail' => $thumbnail_name
               ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Berhasil menambahkan cabang',
                'data' => $branch
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
        ]);

        $old_thumbnail = @$branch->thumbnail;

        if(!$update) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update cabang, silahkan coba lagi'
            ]);
        } else {

            $image_64 = $request->input('thumbnail');
            if( preg_match('/data:image/', $image_64) && preg_match('/base64/', $image_64) ) {
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
    
                $replace = substr($image_64, 0, strpos($image_64, ',')+1); 
              
              // find substring fro replace here eg: data:image/png;base64,
              
               $image = str_replace($replace, '', $image_64); 
              
               $image = str_replace(' ', '+', $image); 
               $imageName = $branch->deid.''.Str::random(10).'.'.$extension;
               Storage::disk('public')->put('image/branch/'.$imageName, base64_decode($image));
               $thumbnail_name = $imageName;
               $branch->update([
                   'thumbnail' => $thumbnail_name
               ]);

                Storage::disk('public')->delete('image/branch/'.$old_thumbnail);
            }

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
        $file_name = @$branch->thumbnail;
        $delete = $branch->delete();

        if(!$delete) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal hapus cabang, silahkan coba lagi'
            ]);
        } else {
            Storage::disk('public')->delete('img/branch/'.$file_name);
            return response()->json([
                'status' => true,
                'message' => 'Berhasil hapus cabang',
            ]);
        }
    }
}
