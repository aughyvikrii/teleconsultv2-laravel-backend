<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use \App\Models\{Person, Doctor, Appointment, Soap, Laboratory, Radiology, Pharmacy};

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
}
