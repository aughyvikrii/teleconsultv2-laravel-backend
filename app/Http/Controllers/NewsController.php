<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \App\Models\{News};
use DB;

class NewsController extends Controller
{
    public function List(Request $request) {
        $list = News::JoinCreator()
                ->selectRaw("nid as news_id, title, viewer_count, base_url(CONCAT('storage/image/news/thumbnail/', thumbnail)) as thumbnail, id_date(news.created_at) as date, creator.full_name as creator")
                ->paginate(25);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function Detail($nid) {
        $data = News::selectRaw("nid as news_id, title, viewer_count, information, base_url(CONCAT('storage/image/news/thumbnail/', thumbnail_long)) as thumbnail_long,base_url(CONCAT('storage/image/news/thumbnail/', thumbnail)) as thumbnail, id_date(created_at) as date")
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

    public function create(Request $request) {
        $valid = Validator::make($request->all(), [
            'longImage' => 'required',
            'shortImage' => 'required',
            'news' => 'required',
            'title' => 'required'
        ], [
            'longImage.required' => 'Pilih thumbnail panjang',
            'shortImage.required' => 'Pilih thumbnail pendek',
            'news.required' => 'Masukan isi berita',
            'title.required' => 'Masukan judul berita'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak valid',
                'errors' => $valid->errors()
            ]);
        }

        $news = News::create([
            'thumbnail' => 'image.png',
            'thumbnail_long' => 'image.png',
            'title'=> $request->title,
            'information' => $request->news,
            'create_id' => auth()->user()->uid,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if(!$news) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat berita! silahkan coba lagi.'
            ]);
        }

        $upload = parent::storeImageB64($request->shortImage, 'image/news/thumbnail/', $news->nid."-", true, true);
        if(!$shortThumbnail = @$upload['basename']) {
            $news->delete();
            return response()->json([
                'status' => false,
                'message' => 'Gagal upload thumbnail pendek, silahkan coba lagi'
            ]);
        }

        $upload = parent::storeImageB64($request->longImage, 'image/news/thumbnail/', $news->nid."-", true, true);
        if(!$longThumbnail = @$upload['basename']) {
            @unlink(storage_path('app/public/image/news/thumbnail/'.$shortThumbnail));
            $news->delete();
            return response()->json([
                'status' => false,
                'message' => 'Gagal upload thumbnail pendek, silahkan coba lagi'
            ]);
        }

        $news->update([
            'thumbnail' => $shortThumbnail,
            'thumbnail_long' => $longThumbnail
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Berhasil menambah berita',
            'data' => [
                'nid' => $news->nid
            ]
        ]);
    }

    public function update($id, Request $request) {
        $valid = Validator::make($request->all(), [
            'longImage' => 'required',
            'shortImage' => 'required',
            'news' => 'required',
            'title' => 'required'
        ], [
            'longImage.required' => 'Pilih thumbnail panjang',
            'shortImage.required' => 'Pilih thumbnail pendek',
            'news.required' => 'Masukan isi berita',
            'title.required' => 'Masukan judul berita'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak valid',
                'errors' => $valid->errors()
            ]);
        }

        $news = News::find($id);

        if(!$news) {
            return response()->json([
                'status' => false,
                'message' => 'Berita tidak ditemukan',
            ]);
        }

        $update = $news->update([
            'title' => $request->title,
            'information' => $request->news,
            'update_id' => auth()->user()->uid,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if(!$update) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat berita! silahkan coba lagi.'
            ]);
        }

        $newThumbnail = $news->thumbnail;
        $newThumbnailLong = $news->thumbnail_long;

        // Jika long Image dipost dan string yang dikirim berbeda dengan yang ada di database
        if($request->longImage && ( !preg_match("/{$news->thumbnail_long}/", $request->longImage) || !$news->thumbnail_long )) {
            $upload = parent::storeImageB64($request->longImage, 'image/news/thumbnail/', $news->nid."-", true, true);
            if($longThumbnail = @$upload['basename']) {
                @unlink(storage_path('app/public/image/news/thumbnail/'.$news->thumbnail_long));
               $newThumbnailLong = $longThumbnail;
            }
        }

        // Jika short Image dipost dan string yang dikirim berbeda dengan yang ada di database
        if($request->shortImage && !preg_match("/{$news->thumbnail}/", $request->shortImage)) {
            $upload = parent::storeImageB64($request->shortImage, 'image/news/thumbnail/', $news->nid."-", true, true);
            if($shortThumbnail = @$upload['basename']) {
                @unlink(storage_path('app/public/image/news/thumbnail/'.$news->thumbnail));
               $newThumbnail = $shortThumbnail;
            }
        }

        $news->update([
            'thumbnail' => $newThumbnail,
            'thumbnail_long' => $newThumbnailLong
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Berhasil update berita',
            'data' => [
                'nid' => $news->nid
            ]
        ]);
    }

    public function delete($id) {
        $news = News::find($id);

        if(!$news) {
            return response()->json([
                'status' => false,
                'message' => 'Berita tidak ditemukan'
            ]);
        }

        @unlink(storage_path('app/public/image/news/thumbnail/'. $news->thumbnail));
        @unlink(storage_path('app/public/image/news/thumbnail/'. $news->thumbnail_long));

        $news->delete();

        return response()->json([
            'status' => true,
            'message' => 'Berita berhasil dihapus'
        ]);
    }
}
