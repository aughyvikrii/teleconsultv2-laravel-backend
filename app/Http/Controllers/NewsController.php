<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\{News};
use DB;

class NewsController extends Controller
{
    public function List(Request $request) {
        $list = News::selectRaw("nid as news_id, title, viewer_count, base_url(CONCAT('storage/image/news/thumbnail/', thumbnail)) as thumbnail, id_date(created_at) as date")
                ->paginate(25);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function Detail($nid) {
        $data = News::selectRaw("nid as news_id, title, viewer_count, information, thumbnail_long,base_url(CONCAT('storage/image/news/thumbnail/', thumbnail)) as thumbnail, id_date(created_at) as date")
                ->find($nid);

        if(!$data) {
            return response()->json([
                'status' => false,
                'message' =>  'Berita tidak ditemukan'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
}
