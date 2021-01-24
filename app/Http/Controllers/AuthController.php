<?php

namespace App\Http\Controllers;

use App\Libraries\Whatsapp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Register
     * 
     * @field   email           string
     * @field   phone_number    string
     * @field   password        string
     * 
     * @return json
     */

    public function Register(Request $request) {
        $valid = Validator::make($request->all(),[
            'email' => 'required|email',
            'phone_number' => 'required',
            'password' => 'required'
        ],[
            'email.required' => 'Masukan alamat email',
            'phone_number.required' => 'Masukan nomor telepon',
            'password.required' => 'Masukan password'
        ]);

        if($valid->fails()) {
            return response()->json([
                'message' => 'parameter tidak tepat',
                'errors' => $valid->errors()
            ], 400);
        }

        $phone_number = format_phone($request->phone_number);

        if(User::emailExist($request->email)) {
            return response()->json([
                'message' => 'email sudah digunakan',
                'errors' => ['email'=> ['email sudah digunakan']]
            ], 400);
        }
        else if (User::phoneExist($phone_number)) {
            return response()->json([
                'message' => 'Nomor telepon sudah digunakan',
                'errors' => ['phone_number'=> ['Nomor telepon sudah digunakan']]
            ], 400);
        }

        $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone_number' => $phone_number,
            'lid'   => 3, // Level id member
        ]);

        if(!$user) {
            return response()->json([
                'message' => 'Pendaftaran gagal, silahkan coba lagi!'
            ], 400);
        }

        $user = User::find($user->uid);

        $code = [
            'code' => $user->code,
            'uid' => $user->uid,
            'expire' => strtotime('+7 days')
        ];

        $verif_code = _encode($code);
        $app_name = config('app.name');
        $link = URL::to('verification/account/'.$user->code.'/'.$verif_code);

        $message = "Hallo, terimakasih telah mendaftar di *_aplikasi {$app_name}_*";
        $message .= "\nBerikut adalah link konfirmasi akun anda:\n";
        $message .= "\n{$link}";
        $message .= "\n\nJika anda tidak melakukan pendaftaran, abaikan pesan ini.";
        $message .= "\nTerimakasih.";
        
        $send = Whatsapp::send($phone_number,$message);
        $message = "Pendaftaran berhasil";

        if(empty(@$send['data']['message_id'])) {
            // Kirim Email
        } else {
            $message = 'Silahkan cek aplikasi <span class="text-success">WhatsApp</span> anda untuk melakukan verifikasi';
        }

        return response()->json([
            'status' => 201,
            'message' => $message
        ], 201);
    }

    /**
     * VerificationAccount
     * 
     * @parameter   code    string
     * @parameter   token   string
     * 
     * @return redirect url
     */

    public function VerificationAccount($code, $token) {
        $error = false;
        $decode = null;
        try {
            $decode = json_decode(_decode($token));
        } catch (\Exception $e) {
            $error = $e->getMessage();
        } catch (\RuntimeException $e) {
            $error = $e->getMessage();
        }

        if(@$decode->code !== $code) {
            return response()->json([
                'message' => 'link verifikasi tidak valid'
            ], 400);
        }

        $user = User::whereRaw('code = ? AND uid = ?',[$code, $decode->uid])->first();
        
        if(!$user) {
            return response()->json([
                'message' => 'link verifikasi tidak valid'
            ], 400);
        } else if ($user->verified_at) {
            return response()->json([
                'message' => 'user sudah diverifikasi'
            ], 400);
        }

        $update = $user->update([
            'verified_at' => date('Y-m-d H:i:s')
        ]);

        if(!$update) {
            return response()->json([
                'message' => 'Gagal konfirmasi akun'
            ], 400);
        } else {
            return response()->json([
                'message' => 'Berhasil konfirmasi akun'
            ], 200);
        }
    }

    /**
     * Login
     * 
     * @parameter   email       string
     * @parameter   password    string
     * 
     * @return json
     */

    public function Login(Request $request) {
        $valid = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required'
        ],[
            'email.required' => 'Masukan alamat email',
            'password.required' => 'Masukan password'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'parameter tidak tepat',
                'errors' => $valid->errors()
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if(!$user) {
            return response()->json([
                'status' => false,
                'message' => 'email/password salah'
            ]);
        }
        else if (!$user->verified_at) {
            return response()->json([
                'status' => false,
                'message' => 'Silahkan konfirmasi akun anda terlebih dahulu'
            ]);
        }

        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => false,
                    'message' => 'invalid_credentials'
                ]);
            }
        } catch (JWTException $e) {
            return response()->json([
                'status' => false,
                'message' => 'could_not_create_token'
            ]);
        }

        return response()->json([
            'status' => true,
            'token' => $token,
            'user' => $user
        ]);
    }

    /**
     * User
     * Need Auth
     * 
     * @return json
     */

    public function User() {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }

        if($user->lid != '1') {
            $user = array_match_key($user, [
                'code', 'email', 'phone_number',
            ]);
        }
        
        return response()->json([
            'user' => $user,
        ]);
    }
}
