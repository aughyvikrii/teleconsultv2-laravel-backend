<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

use App\Models\{Person, Department};

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function storeImageB64($data, $filedir, $filename=false, $withEXT = false,  $withUniq = false) {
        $extension = @explode('/', @explode(':', substr($data, 0, strpos($data, ';')))[1])[1];   // .jpg .png .pdf
    
        $replace = substr($data, 0, strpos($data, ',')+1); 
        
        // find substring fro replace here eg: data:image/png;base64,
        
        $image = str_replace($replace, '', $data); 
        $image = str_replace(' ', '+', $image); 
        $imageName = Str::random(10);
        if($withUniq) $filename .= $imageName;
        if($filename) $imageName = $filename;

        if($withEXT) $imageName .= '.'.$extension;

        $fullPathName = $filedir.$imageName;

        $result = Storage::disk('public')->put($fullPathName, base64_decode($image));
        
        if(!$result) return false;
        else {
            $storagePath = storage_path('app/public/'.$fullPathName);
            $info = pathinfo($storagePath);
            return $info;
        }
    }

    public function getUser() {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json([
                'status' => true,
                'user' => $user
            ]);
            
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json([
                    'status' => false,
                    'message' => 'Token is Invalid'
                ], 401);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json([
                    'status' => false,
                    'message' => 'Token is Expired'
                ], 401);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Authorization Token not found'
                ], 401);
            }
        }
    }

    public function redirect() {
        $response = self::getUser();
        $response = $response->original;
        
        if(!$response['status']) return redirect('/auth');
        $user = $response['user'];

        if($user->lid == '1') return redirect('/admin');
        else if($user->lid == '2') return redirect('/doctor');
        else return redirect('/home');
    }

    public function index_mapping() {
        $response = self::getUser();
        $response = $response->original;
        
        if(!$response['status']) return;
        $user = $response['user'];

        if($user->lid == '1') {
            return view('admin');
        } else if($user->lid == '2') {
            return view('doctor');
        } else if($user->lid == '3') {
            return view('patient');
        } else {
            return view('patient');
        }
    }

    public function saveProfilePicture($data, $filename=false, $appendExt = true, $withUniq = true) {
        return self::storeImageB64($data, 'image/profile/', $filename, $appendExt, $withUniq);
    }

    public function landing() {

        $doctors = Person::join('specialists', 'specialists.sid', '=', 'persons.sid')
                ->selectRaw('persons.display_name, doctor_pic(persons.profile_pic) as profile_pic, specialists.alt_name as specialist')
                ->limit(3)
                ->inRandomOrder()
                ->get();
        
        $departments = Department::selectRaw('departments.*, department_pic(thumbnail) as pic')->get();

        return view('landing', compact([
            'doctors', 'departments'
        ]));
    }
}
