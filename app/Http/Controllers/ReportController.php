<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\{Appointment};
use DB, PDF;

class ReportController extends Controller
{
    public $type = [
        'full', 'register', 'soap', 'pharmacy', 'radiology', 'laboratory'
    ];

    public function Print(Request $request) {
        $type = $request->input('type');
        $appointment_id = $request->input('appointment_id');

        if(!in_array($type, $this->type)) {
            return response()->json([
                'status' => false,
                'message' => "Tipe laporan #{$type} tidak dikenali"
            ]);
        }

        $appointment = Appointment::joinFullInfo()
            ->selectRaw('
                row_to_json(appointments) as appointment_json,
                row_to_json(patient) as patient_json,
                row_to_json(doctor) as doctor_json,
                row_to_json(schedules) as schedule_json,
                row_to_json(departments) as department_json,
                row_to_json(branches) as branch_json,
                row_to_json(bills) as bill_json,
                id_date(patient.birth_date) as id_birth_date,
                id_age(patient.birth_date) as age
            ')
            ->doctorUID()
            ->where('appointments.aid', $appointment_id)
            ->first();

        if(!$appointment) {
            return response()->json([
                'status' => false,
                'message' => "Perjanjian dengan id #{$appointment_id} tidak ditemukan"
            ]);
        }

        $uniq = uniqid();

        $pdf = PDF::loadView('report.pdf.'. $type, [
            'data' => $appointment,
            'id' => $uniq
        ]);
        $pdf->setOptions([
            'defaultPaperSize' => 'a4',
            'defaultFont' => 'Times New Roman'
        ]);
        return $pdf->stream('Laporan_Pendaftaran_'. @$appointment->appointment_json['aid'] . '_'. $uniq . '.pdf');
    }
}
