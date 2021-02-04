<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


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

    public function saveProfilePicture($data, $filename=false, $appendExt = true, $withUniq = true) {
        return self::storeImageB64($data, 'images/profile/', $filename, $appendExt, $withUniq);
    }
}
