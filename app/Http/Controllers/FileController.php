<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Storage, URL;

class FileController extends Controller
{
    public function create(Request $request) {
        $valid = Validator::make($request->all(), [
            'ext' => 'required'
        ],[
            'ext.required' => 'Ekstensi tidak didefinisikan'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Parameter tidak valid',
                'errors' => $valid->errors()
            ]);
        }

        $name = md5(uniqid().time().rand(000000,999999)). '.' . $request->ext;
        $storage = storage_path("app/public/image/news");
        $fileLocation = "{$storage}/{$name}";

        $create = touch($fileLocation);

        if(!$create) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat file',
            ]);
        }

        $file = new \SplFileInfo($fileLocation);

        return [
            'name' => $file->getFileName(),
            'url' => URL::to("storage/image/news/{$name}"),
            'size' => $file->getSize(),
        ];
    }
}
