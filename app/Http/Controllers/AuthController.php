<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

use App\Libraries\Whatsapp;
use App\Models\{User, Person, FamilyMaster, FamilyTree};

use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmEmail;

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
            'first_name' => 'required',
            'contact' => 'required',
            'password' => 'required',
            'birth_date_d' => 'required|min:1|max:31',
            'birth_date_m' => 'required|min:1|max:12',
            'birth_date_y' => 'required|max:'.date('Y'),
            // 'gender' => 'required|exists:genders,gid'
        ],[
            'first_name.required' => 'Masukan nama depan',
            'contact.required' => 'Masukan email/no hp',
            'password.required' => 'Masukan password',
            'birth_date_d.required' => 'Masukan tanggal lahir',
            'birth_date_d.min' => 'Tanggal lahir tidak valid',
            'birth_date_d.max' => 'Tanggal lahir tidak valid',
            'birth_date_m.required' => 'Masukan bulan lahir',
            'birth_date_m.min' => 'Bulan lahir tidak valid',
            'birth_date_m.max' => 'Bulan lahir tidak valid',
            'birth_date_y.required' => 'Masukan tahun lahir',
            'birth_date_y.max' => 'Tahun lahir tidak valid',
            'gender.required' => 'Pilih jenis kelamin',
            'gender.exists' => 'Jenis kelamin tidak valid'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' =>  false,
                'message' => 'parameter tidak tepat',
                'errors' => $valid->errors(),
            ]);
        }

        $contact = $request->input('contact');
        if(preg_match('/@/', $contact)) $contact_type = 'email';
        else $contact_type = 'phone_number';
        $phone_number = format_phone($contact);

        if($contact_type === 'email') {
            $valid = Validator::make($request->only('contact'), [ 'contact' => 'email:rfc,dns' ],[ 'contact.email' => 'Alamat email tidak valid' ]);
            if($valid->fails()) {
                return response()->json([
                    'status' =>  false,
                    'message' => 'parameter tidak tepat',
                    'errors' => $valid->errors()
                ]);
            }

            if(User::emailExist($contact)) {
                return response()->json([
                    'status' =>  false,
                    'message' => 'email sudah digunakan',
                    'errors' => ['contact'=> ['email sudah digunakan']]
                ]);
            }

        } else {
            if (User::phoneExist($phone_number)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Nomor telepon sudah digunakan',
                    'errors' => ['contact'=> ['Nomor telepon sudah digunakan']]
                ]);
            }
        }

        DB::beginTransaction();

        $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'email' => ($contact_type==='email' ? $contact : ''),
            'phone_number' => ($contact_type!=='email' ? $phone_number : ''), 
            'lid'   => 3, // Level id member
        ]);

        if(!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Pendaftaran gagal, silahkan coba lagi!'
            ]);
        }

        $birth_date = date('Y-m-d', strtotime(
            $request->birth_date_y . '-'. $request->birth_date_m . '-'. $request->birth_date_d
        ));

        $person = Person::create([
            'uid' => $user->uid,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => ($contact_type!=='email' ? $phone_number : ''), 
            // 'gid' => $request->gender,
            'birth_date' =>  $birth_date,
            'create_id' => $user->uid,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if(!$person) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Pendaftaran gagal, silahkan coba lagi!'
            ]);
        }

        $family_master = FamilyMaster::create([
            'create_id' => $user->uid,
            'created_at'  => date('Y-m-d H:i:s')
        ]);

        if(!$family_master) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Pendaftaran gagal, silahkan coba lagi!'
            ]);
        }

        $family_tree = FamilyTree::create([
            'fmid' => $family_master->fmid,
            'pid' => $person->pid,
            'create_id' => $user->uid,
            'created_at'  => date('Y-m-d H:i:s')
        ]);

        if(!$family_tree) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Pendaftaran gagal, silahkan coba lagi!'
            ]);
        }

        $person->update(['fmid' => $family_master->fmid]);

        $user = User::find($user->uid);

        $code = [
            'code' => $user->code,
            'uid' => $user->uid,
            'expire' => strtotime('+7 days')
        ];

        $verif_code = _encode($code);
        $app_name = config('app.name');
        $link = URL::to('verification/account/'.$user->code.'/'.$verif_code);

        if($contact_type === 'phone_number') {
            $message = "Hallo, terimakasih telah mendaftar di *_aplikasi {$app_name}_*";
            $message .= "\nBerikut adalah link konfirmasi akun anda:\n";
            $message .= "\n{$link}";
            $message .= "\n\nJika anda tidak melakukan pendaftaran, abaikan pesan ini.";
            $message .= "\nTerimakasih.";
            
            $send = Whatsapp::send($phone_number,$message);
            
            
        } else {
            
            $res = Mail::to($user->email)->send(new ConfirmEmail([
                'name' => $person->full_name,
                'link' => $link
            ]));
            
        }
        $message = 'Silahkan cek <span class="text-success">Email/WhatsApp</span> anda untuk melakukan verifikasi';
        DB::commit();
        return response()->json([
            'status' => true,
            'message' => $message
        ]);
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
            'contact' => 'required',
            'password' => 'required'
        ],[
            'contact.required' => 'Masukan email/nomor telepon',
            'password.required' => 'Masukan password'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'parameter tidak tepat',
                'errors' => $valid->errors()
            ]);
        }
        $contact = $request->contact;
        if(preg_match('/@/', $contact)) {
            $type = 'email';
            $user = User::selectRaw('users.email, users.code, users.verified_at')
            ->where('email', $contact)
            ->first();
        }
        else {
            $type = 'phone_number';
            $contact = format_phone($contact);
            $user = User::selectRaw('users.email, users.code, users.verified_at')
            ->where('phone_number', $contact)
            ->first();
        }
            
        if(!$user) {
            return response()->json([
                'status' => false,
                'message' => ($type === 'email' ? 'Email' : 'Nomor Telepon').'/Password salah',
            ]);
        }
        else if (!$user->verified_at) {
            return response()->json([
                'status' => false,
                'message' => 'Silahkan konfirmasi akun anda terlebih dahulu',
                'need_verification' => true
            ]);
        }

        $credentials = [
            $type => $contact,
            'password' => $request->password,
        ];

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => false,
                    'message' => 'email/password salah'
                ]);
            }
        } catch (JWTException $e) {
            return response()->json([
                'status' => false,
                'message' => 'gagal membuat token'
            ]);
        }

        $redirect = '/home';
        if($user->lid === '1') $redirect = '/admin';
        else if ($user->lid === '2') $redirect = '/doctor';

        $user = User::joinPerson()
        ->selectRaw('users.code, users.email, users.phone_number
                    , persons.first_name, persons.last_name, persons.last_name, persons.display_name,persons.tid as title_id
                    , persons.gid as gender_id, persons.itid as identitytype_id, persons.identity_number, persons.birth_date
                    , persons.birth_date, persons.msid as married_status_id, persons.rid as religion_id, persons.vid as village_id, persons.address
                    , CASE WHEN users.lid = 2 THEN patient_pic(persons.profile_pic) ELSE doctor_pic(persons.profile_pic) END AS profile_pic
                    , users.verified_at, users.created_at as register_at')
        ->where("users.$type", $contact)
        ->first();

        $complete_info = true;
        foreach($user->getAttributes() as $key => $val) {
            if(!$val) {
                $complete_info = false;
                break;
            }
        }

        $user->complete_info = $complete_info;

        return response()->json([
            'status' => true,
            'token' => $token,
            'user' => $user,
            'redirect' => $redirect
        ]);
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

        $user = User::joinPerson()
        ->selectRaw('users.code, users.email, users.phone_number
                    , persons.first_name, persons.last_name, persons.display_name, persons.tid as title_id, persons.gid as gender_id, persons.itid as identity_type_id
                    , persons.identity_number, persons.birth_date, persons.birth_place, persons.msid as married_status_id, persons.rid as religion_id
                    , persons.vid as village_id, persons.address
                    , CASE WHEN users.lid = 2 THEN patient_pic(persons.profile_pic) ELSE doctor_pic(persons.profile_pic) END AS profile_pic
                    , users.verified_at, users.created_at as register_at')
        ->where("users.uid", $user->uid)
        ->first();


        $unchecked_data = [ 'last_name', 'display_name' ];

        $complete_info = true;
        foreach($user->getAttributes() as $key => $val) {
            if(in_array($key, $unchecked_data)) continue;
            if(!$val) {
                $complete_info = false;
                break;
            }
        }

        $user->complete_info = $complete_info;
        
        $token = JWTAuth::getToken();
        $token = JWTAuth::refresh($token);
        
        return response()->json([
            'status' => true,
            'data' => [
                'user' => $user,
                'token' => $token
            ],
        ]);
    }

    public function ResendLinkVerification(Request $request) {
        $valid = Validator::make($request->all(),[
            'contact' => 'required',
        ],[
            'contact.required' => 'Masukan email/nomor telepon anda'
        ]);

        if($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Parameter tidak lengkap',
                'errors' => $valid->errors()
            ]);
        }
        
        $phone_number = $contact = $request->contact;
    
        if(preg_match('/@/', $contact)) {
            $contact_type = 'email';    
            $user = User::where('email', $contact)->first();
        } else {
            $contact_type = 'phone_number';
            $phone_number = format_phone($contact);
            $user = User::where('phone_number', $phone_number)->first();
        }

        if(!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Akun tidak ditemukan',
                'errors' => [
                    'contact' => ['pastian email/nomor anda benar']
                ]
            ]);
        }

        $code = [
            'code' => $user->code,
            'uid' => $user->uid,
            'expire' => strtotime('+7 days')
        ];

        $verif_code = _encode($code);
        $app_name = config('app.name');
        $link = URL::to('verification/account/'.$user->code.'/'.$verif_code);

        if($contact_type === 'phone_number') {
            $message = "Hallo, terimakasih telah mendaftar di *_aplikasi {$app_name}_*";
            $message .= "\nBerikut adalah link konfirmasi akun anda:\n";
            $message .= "\n{$link}";
            $message .= "\n\nJika anda tidak melakukan pendaftaran, abaikan pesan ini.";
            $message .= "\nTerimakasih.";
            
            $send = Whatsapp::send($phone_number,$message);
            
            
        } else {
            
            $res = Mail::to($user->email)->send(new ConfirmEmail([
                'name' => $person->full_name,
                'link' => $link
            ]));
            
        }
        $message = 'Silahkan cek <span class="text-success">Email/WhatsApp</span> anda untuk melakukan verifikasi';

        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }
}
