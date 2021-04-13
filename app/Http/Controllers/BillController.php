<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\{Bill};

class BillController extends Controller
{
    public function detail($invoice_id) {
        $bill = Bill::JoinFullInfo()
        ->selectRaw('bills.*, patient.full_name as patient, patient.pid as patient_id, id_date(bills.paid_on) as id_paid_on, appointments.consul_date, ftime(appointments.consul_time) as consul_time, doctor.display_name as doctor, doctor.pid as doctor_id, departments.name as department, departments.deid as department_id, branches.name as branch, branches.bid as branch_id, bills.status bill_status, appointments.status as appointment_status, id_date(appointments.consul_date) as id_consul_date')
        ->where('uniq', $invoice_id)->first();

        if(!$bill) {
            return response()->json([
                'status' => false,
                'message' => 'Tagihan tidak ditemukan'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $bill
        ]);
    }
}
