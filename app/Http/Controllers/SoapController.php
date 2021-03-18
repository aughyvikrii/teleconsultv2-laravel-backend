<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use \App\Models\{Person, Doctor, Appointment, Soap, Laboratory, Radiology, Pharmacy};
use \App\Models\{LogSoap, LogPharmacy, LogLaboratory, LogRadiology};
class SoapController extends Controller
{
    public function Input($aid, Request $request) {
        $appointment = Appointment::DoctorUID()
                        ->where('appointments.aid', $aid)
                        ->first();
        
        if(!$appointment) {
            return response()->json([
                'status' => false,
                'message' => 'Perjanjian tidak ditemukan'
            ]);
        }

        $soap = Soap::where('aid', $appointment->aid)->first();

        if(!$soap) $soap = Soap::create(['aid' => $appointment->aid, 'create_id' => auth()->user()->uid]);

        $update = $soap->update([
            'subjective' => $request->input('subjective'),
            'objective' => $request->input('objective'),
            'assesment' => $request->input('assesment'),
            'plan' => $request->input('plan'),
            'last_update' => date('Y-m-d H:i:s')
        ]);

        $lab = Laboratory::where('aid', $appointment->aid)->first();
        if(!$lab) $lab = Laboratory::create(['aid' => $appointment->aid, 'create_id' => auth()->user()->uid]);

        $update = $lab->update([
            'recommendation' => $request->input('lab_recom'),
            'diagnosis' => $request->input('lab_diagnosis'),
            'allergy' => $request->input('lab_allergy'),
            'last_update' => date('Y-m-d H:i:s')
        ]);

        $rad = Radiology::where('aid', $appointment->aid)->first();
        if(!$rad) $rad = Radiology::create(['aid' => $appointment->aid, 'create_id' => auth()->user()->uid]);

        $update = $rad->update([
            'recommendation' => $request->input('rad_recom'),
            'diagnosis' => $request->input('rad_diagnosis'),
            'allergy' => $request->input('rad_allergy'),
            'last_update' => date('Y-m-d H:i:s')
        ]);

        $phar = Pharmacy::where('aid', $appointment->aid)->first();
        if(!$phar) $phar = Pharmacy::create(['aid' => $appointment->aid, 'create_id' => auth()->user()->uid]);

        $update = $phar->update([
            'recommendation' => $request->input('phar_recom'),
            'diagnosis' => $request->input('phar_diagnosis'),
            'allergy' => $request->input('phar_allergy'),
            'last_update' => date('Y-m-d H:i:s')
        ]);

        $update_data = [
            'status' => 'done',
            'last_update' => date('Y-m-d H:i:s')
        ];

        if(!$appointment->end_consul) {
            $update_data['end_consul'] = date('Y-m-d H:i:s');
        }

        $update = $appointment->update($update_data);

        return response()->json([
            'status'  => true,
            'message' => 'Berhasil input soap'
        ]);
    }

    public function Update ($aid, Request $request) {
        $soap = Soap::joinAppointment()
                ->selectRaw('soaps.*')
                ->where('appointments.aid', $aid)
                ->createByMe()
                ->first();

        if(!$soap) {
            return response()->json([
                'status' => false,
                'message' => 'Riwayat soap tidak ditemukan'
            ]);
        }
        DB::BeginTransaction();
        // UPDATE SOAP----------------------
        $new_soap = [
            'subjective' => $request->input('subjective'),
            'objective' => $request->input('objective'),
            'assesment' => $request->input('assesment'),
            'plan' => $request->input('plan')
        ];

        $soap_diff = array_diff([
            'subjective' => $soap->subjective,
            'objective' => $soap->objective,
            'assesment' => $soap->assesment,
            'plan' => $soap->plan,
        ], $new_soap);

        if(!empty($soap_diff)) {
            LogSoap::create($soap->toArray());

            $new_soap['last_update'] = date('Y-m-d H:i:s');
            $update = $soap->update($new_soap);
        }
        // UPDATE SOAP----------------------

        // UPDATE LAB----------------------
        $lab = Laboratory::where('aid', $aid)->first();
        $new_lab = [
            'recommendation' => $request->input('lab_recom'),
            'diagnosis' => $request->input('lab_diagnosis'),
            'allergy' => $request->input('lab_allergy'),
        ];

        $lab_diff = array_diff([
            'recommendation' => @$lab->recommendation,
            'diagnosis' => @$lab->diagnosis,
            'allergy' => @$lab->allergy,
        ], $new_lab);

        if(!empty($lab_diff)) {
            if(!$lab) $lab = Laboratory::create(['aid' => $aid, 'create_id' => auth()->user()->uid]);
            else LogLaboratory::create($lab->toArray());

            $new_lab['last_update'] = date('Y-m-d H:i:s');
            $update = $lab->update($new_lab);
        }
        // UPDATE LAB----------------------

        // UPDATE RAD----------------------
        $rad = Radiology::where('aid', $aid)->first();
        $new_rad = [
            'recommendation' => $request->input('rad_recom'),
            'diagnosis' => $request->input('rad_diagnosis'),
            'allergy' => $request->input('rad_allergy'),
        ];

        $rad_diff = array_diff([
            'recommendation' => @$rad->recommendation,
            'diagnosis' => @$rad->diagnosis,
            'allergy' => @$rad->allergy,
        ], $new_rad);

        if(!empty($rad_diff)) {
            if(!$rad) $rad = Radiology::create(['aid' => $aid, 'create_id' => auth()->user()->uid]);
            else LogRadiology::create($rad->toArray());

            $new_rad['last_update'] = date('Y-m-d H:i:s');
            $update = $rad->update($new_rad);
        }
        // UPDATE RAD----------------------

        // UPDATE PHAR----------------------
        $phar = Pharmacy::where('aid', $aid)->first();
        
        $new_phar = [
            'recommendation' => $request->input('phar_recom'),
            'diagnosis' => $request->input('phar_diagnosis'),
            'allergy' => $request->input('phar_allergy'),
        ];

        $phar_diff = array_diff([
            'recommendation' => @$phar->recommendation,
            'diagnosis' => @$phar->diagnosis,
            'allergy' => @$phar->allergy,
        ], $new_phar);

        if(!empty($phar_diff)) {
            if(!$phar) $phar = Pharmacy::create(['aid' => $aid, 'create_id' => auth()->user()->uid]);
            else LogPharmacy::create($phar->toArray());
            
            $new_phar['last_update'] = date('Y-m-d H:i:s');
            $update = $phar->update($new_phar);
        }
        // UPDATE PHAR----------------------
        DB::commit();

        return response()->json([
            'status'  => true,
            'message' => 'Berhasil input soap'
        ]);
    }
}
