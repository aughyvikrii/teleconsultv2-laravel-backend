<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \App\Models\{Department};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DepartmentController extends Controller
{

    /**
     * Create New Department
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

        $department = Department::create([
            'name' => $request->name,
            'thumbnail' => $thumbnail_name,
            'description' => $request->description
        ]);

        if(!$department) {
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
               $imageName = $department->deid.''.Str::random(10).'.'.$extension;
               Storage::disk('public')->put('img/department/'.$imageName, base64_decode($image));
               $thumbnail_name = $imageName;
               $department->update([
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
     * Department List
     * 
     * @return json
     */

    public function List(Request $request) {
        
        if($request->input('all_data')) {
            $list = Department::selectRaw('departments.name, departments.deid')
                    ->active()
                    ->orderBy('departments.name', 'ASC')
                    ->get();
        } else {
            $list = Department::selectRaw('departments.*')
            ->selectRaw("CONCAT('".asset('storage/img/department')."/', departments.thumbnail) as thumbnail");
        
            if($query = $request->input('query')) {
                $list->whereRaw('LOWER(departments.name) LIKE LOWER(?) OR LOWER(departments.description) LIKE LOWER(?) ',["%$query%", "%$query%"]);
            }

            $list = $list->paginate($request->input('data_per_page', 10));
        }

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    /**
     * Department Detail
     * 
     * @param department_id string
     * @return json
     */

    public function Detail($department_id) {
        $department = Department::find($department_id);

        if(!$department) {
            return response()->json([
                'status' => false,
                'message' => 'Data departemen tidak ditemukan'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data departemen ditemukan',
            'data' =>  $department
        ]);

    }

    /**
     * Update Department Data
     * 
     * @param   department_id int
     * @param   Request $request
     * @return  json
     */

    public function Update($department_id, Request $request) {
        $department = Department::find($department_id);

        if(!$department) {
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

        $update = $department->update([
            'name' => $request->name,
            'description' => $request->description
        ]);

        $old_thumbnail = @$department->thumbnail;

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
               $imageName = $department->deid.''.Str::random(10).'.'.$extension;
               Storage::disk('public')->put('img/department/'.$imageName, base64_decode($image));
               $thumbnail_name = $imageName;
               $department->update([
                   'thumbnail' => $thumbnail_name
               ]);

                Storage::disk('public')->delete('img/department/'.$old_thumbnail);
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
     * @param department_id int
     * @return json
     */

    public function Delete($department_id) {
        $department = Department::find($department_id);

        if(!$department) {
            return response()->json([
                'status' => false,
                'message' => 'Data departemen tidak ditemukan'
            ]);
        }

        // Cek jika department ini digunakan
        $file_name = @$department->thumbnail;
        $delete = $department->delete();

        if(!$delete) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal hapus departemen, silahkan coba lagi'
            ]);
        } else {
            Storage::disk('public')->delete('img/department/'.$file_name);
            return response()->json([
                'status' => true,
                'message' => 'Berhasil hapus departemen',
                'image' => 'app/public/img/department/'.$file_name
            ]);
        }
    }
}
