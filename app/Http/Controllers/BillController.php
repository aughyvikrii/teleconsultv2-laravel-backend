<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\{Bill};

class BillController extends Controller
{
    public function detail($invoice_id) {
        $bill = Bill::JoinFullInfo()
        ->selectRaw('bills.*, patient.full_name as patient, patient.pid as patient_id, id_date(bills.paid_on) as id_paid_on, appointments.consul_date, ftime(appointments.consul_time) as consul_time, doctor.display_name as doctor, doctor.pid as doctor_id, departments.name as department, departments.deid as department_id, branches.name as branch, branches.bid as branch_id, bills.status bill_status, appointments.status as appointment_status, id_date(appointments.consul_date) as id_consul_date, patient_pic(patient.profile_pic) as patient_pic, doctor_pic(doctor.profile_pic) as doctor_pic, branches.midtrans_client_key as payment_key')
        ->where('uniq', $invoice_id)->first();

        if(!$bill) {
            return response()->json([
                'status' => false,
                'message' => 'Tagihan tidak ditemukan'
            ]);
        }

        if(is_patient()) {
            $bill->snaptoken = $bill->midtrans_snaptoken;
            $bill->makeHidden([
                'blid', 'delete_id', 'deleted_at', 'is_active', 'midtrans_log', 'midtrans_paid_log', 'midtrans_payment_type', 'midtrans_pending_log', 'midtrans_snaptoken'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $bill
        ]);
    }

    public function list(Request $request) {
        $list = Bill::JoinFullInfo()
                ->selectRaw('bills.uniq as bill_id, bills.description, bills.amount, bills.status, bills.expired_at, bills.created_at, patient.full_name as patient, patient_pic(patient.profile_pic) as patient_pic');

        $search = $request->query('search');
        $list->when($search, function($query) use($search) {
            $s = strtolower($search);
            $s = "%$s%";
            $query->whereRaw('uniq LIKE ? OR LOWER(description) LIKE ?', [$s, $s]);
        });

        $data_per_page = $request->query('data_per_page', 15);
        if(is_patient()) {
            $list->MyFamily();
        }

        $list->orderBy('blid', 'DESC');

        $list = $list->paginate($data_per_page);

        return response()->json([
            'status' => true,
            'data' =>  $list
        ]);
    }
}
