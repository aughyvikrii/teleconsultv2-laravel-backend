<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \App\Models\{Departement};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'thumbnail' => $thumbnail_name,
            'description' => $request->description
        ]);

        if(!$departement) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan departemen, silahkan coba lagi'
            ]);
        } else {

            if($image_64 = $request->input('thumbnail')) {
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
    
                $replace = substr($image_64, 0, strpos($image_64, ',')+1); 
              
              // find substring fro replace here eg: data:image/png;base64,
              
               $image = str_replace($replace, '', $image_64); 
              
               $image = str_replace(' ', '+', $image); 
               $imageName = $departement->deid.''.Str::random(10).'.'.$extension;
               Storage::disk('public')->put('img/departement/'.$imageName, base64_decode($image));
               $thumbnail_name = $imageName;
               $departement->update([
                   'thumbnail' => $thumbnail_name
               ]);
            }

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
        $list = Departement::selectRaw('departements.*')
            ->selectRaw("CONCAT('".asset('storage/img/departement')."/', departements.thumbnail) as thumbnail");
        
        if($query = $request->input('query')) {
            $list->whereRaw('LOWER(departements.name) LIKE LOWER(?) OR LOWER(departements.description) LIKE LOWER(?) ',["%$query%", "%$query%"]);
        }

        $list = $list->paginate($request->input('data_per_page', 10));

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

        $update = $departement->update([
            'name' => $request->name,
            'description' => $request->description
        ]);

        $old_thumbnail = @$departement->thumbnail;

        if(!$update) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update departemen, silahkan coba lagi'
            ]);
        } else {

            if(($image_64 = $request->input('thumbnail')) && !preg_match("/{$old_thumbnail}/", $request->input('thumbnail')) ) {
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
    
                $replace = substr($image_64, 0, strpos($image_64, ',')+1); 
              
              // find substring fro replace here eg: data:image/png;base64,
              
               $image = str_replace($replace, '', $image_64); 
              
               $image = str_replace(' ', '+', $image); 
               $imageName = $departement->deid.''.Str::random(10).'.'.$extension;
               Storage::disk('public')->put('img/departement/'.$imageName, base64_decode($image));
               $thumbnail_name = $imageName;
               $departement->update([
                   'thumbnail' => $thumbnail_name
               ]);

                Storage::disk('public')->delete('img/departement/'.$old_thumbnail);
            }

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
        $file_name = @$departement->thumbnail;
        $delete = $departement->delete();

        if(!$delete) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal hapus departemen, silahkan coba lagi'
            ]);
        } else {
            Storage::disk('public')->delete('img/departement/'.$file_name);
            return response()->json([
                'status' => true,
                'message' => 'Berhasil hapus departemen',
                'image' => 'app/public/img/departement/'.$file_name
            ]);
        }
    }
}
