<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Libraries\{Midtrans, Whatsapp};
use \App\Models\{Branch, Bill, Appointment, Person, Department, Schedule};
use \Carbon\Carbon;
use DB;

class MidtransController extends Controller
{
    public function Notification(Request $request) {
        $date_now = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
        $branch = Branch::where('midtrans_id_merchant', $request->input('merchant_id'))->first();

        if(!$branch) {
            return response()->json([
                'status' => false,
                'message' => 'merchant_id: ' . $request->input('merchant_id') . " tidak terdaftar di database"
            ]);
        }

        $bill = Bill::where('uniq', $request->input('order_id'))->first();

        if(!$bill) {
            return response()->json([
                'status' => false,
                'message' => "Bill dengan id: {$request->order_id} tidak ditemukan"
            ]);
        }

        $appointment = Appointment::find($bill->aid);

        $midtrans_lib = new Midtrans($branch);

        list($check, $error) = $midtrans_lib->status($request->input('transaction_id'));
        
        if(!$status = @$check->transaction_status) {
            return response()->json([
                'status' => false,
                'message' => 'transaction status not set',
                'log' => $check
            ]);
        }

        $newrequest = new Request;
        $newrequest->replace(json_decode(json_encode($check), true));

        if($request->order_id != $newrequest->order_id) {
            return response()->json([
                'status'  => false,
                'message' => 'Parameter yang diberikan tidak match dengan data asli',
                'data_received' => $request->toArray(),
                'data_valid' => $newrequest->toArray()
            ]);
        }

        /**
         * User memilih payment channel
         * Data tampil di dashboard midtrans
         */
        if($status == 'pending') {
            $update = $bill->update([
                'midtrans_payment_type' => @$check->payment_type,
                'midtrans_pending_raw' => json_encode($check),
                'last_update' => $date_now,
            ]);

            echo "ok: status update to pending";
            exit();
        }
        /**
         * Pembayaran sudah diterima
         */
        else if($status == 'capture') {
            return $this->captureTransaction([
                'branch' => $branch,
                'bill' => $bill,
                'appointment' => $appointment,
                'check' => $check,
                'request' => $request,
            ]);
            // $update = $bill->update([
            //     'paid_on' => $date_now,
            //     'status' => 'paid',
            //     'midtrans_payment_type' => @$check->payment_type,
            //     'midtrans_payment_raw' => json_encode($check),
            //     'last_update' => $date_now,
            // ]);

            // $update_appointment = $appointment->update([
            //     'status' => 'waiting_consul',
            //     'last_update' => $date_now,
            // ]);

            // echo "ok: status paid";
            // exit();
        }
        /**
         * Pembayaran sudah disettle
         * Dana sudah diambil dari espay
         */
        else if($status == 'settlement') {
            return $this->confirmAppointment([
                'branch' => $branch,
                'bill' => $bill,
                'appointment' => $appointment,
                'check' => $check,
                'request' => $request,
            ]);
        }
        /**
         * Transaksi kedaluwarsa
         */
        else if($status == 'expire') {
            return $this->cancelAppointment([
                'branch' => $branch,
                'bill' => $bill,
                'appointment' => $appointment,
                'check' => $check,
                'request' => $request,
                'status' => 'expire'
            ]);
        }
        /**
         * Transaksi dibatalkan
         */
        else if($status == 'cancel') {
            return $this->cancelAppointment([
                'branch' => $branch,
                'bill' => $bill,
                'appointment' => $appointment,
                'check' => $check,
                'request' => $request,
                'status' => 'cancel'
            ]);
        }
        /**
         * Transaksi dibatalkan
         */
        else if($status == 'deny') {
            return $this->cancelAppointment([
                'branch' => $branch,
                'bill' => $bill,
                'appointment' => $appointment,
                'check' => $check,
                'request' => $request,
                'status' => 'deny'
            ]);
        }
        /**
         * Lainnya
         */
        else {
            $update = $bill->update([
                'midtrans_last_raw' => json_encode($check),
                'last_update' => $date_now,
            ]);
            
            echo "ok";
            exit();
        }
    }

    public function captureTransaction($data) {

    }

    // Notifikasi dari midtrans sudah settlement
    public function confirmAppointment($data) {
        $branch = @$data['branch'];
        $bill = @$data['bill'];
        $appointment = @$data['appointment'];
        $check = @$data['check'];
        $request = @$data['request'];
        $patient = Person::find($appointment->patient_id);
        $schedule = Schedule::JoinFullInfo()
                    ->selectRaw('schedules.scid as schedule_id, persons.pid as doctor_id, persons.display_name as doctor_name, departments.deid as department_id, departments.name as department')
                    ->whereRaw('schedules.scid = ?', [$appointment->scid])
                    ->first();

        if($bill->status == 'paid') {
            echo "fail: status order sudah dikonfirmasi";
            exit();
        }

        $now = Carbon::now()->format('Y-m-d H:i:s');

        $midtrans_log = $bill->midtrans_log ? json_decode($bill->midtrans_log, true) : [];
        $midtrans_log[] = json_encode($request->all());

        $bill->update([
            'paid_on' => $now,
            'status' => 'paid',
            'last_update' => $now,
            'midtrans_paid_log' => json_encode($request->all()),
            'midtrans_log' => json_encode($midtrans_log)
        ]);

        $appointment->update([
            'status' => 'waiting_consul',
            'last_update' => $now,
        ]);

        $message_patient = "Hallo {$patient->first_name},\n\n";
        $message_patient .= "Anda memiliki perjanjian telekonsultasi pada:\n\n";
        $message_patient .= "*Tgl Konsultasi*\n";
        $message_patient .=  Carbon::parse($appointment->consul_date)->translatedFormat('l, d F Y') . " Pukul ".date("H:i", strtotime($appointment->consul_time))." \n\n";
        $message_patient .= "*Dokter*\n";
        $message_patient .= "{$schedule->doctor_name}\n\n";
        $message_patient .= "*Poli*\n";
        $message_patient .= "{$schedule->department}\n\n";
        $message_patient .= "*Keluhan Utama*\n";
        $message_patient .= "{$appointment->main_complaint}\n\n";
        $message_patient .= "_*Mohon tidak terlambat untuk mengikuti telekonsultasi.*_\n\n";
        $message_patient .= "Untuk info lebih lanjut bisa menghubungi nomor dibawah ini:\n";
        $message_patient .= "{$branch->whatsapp_number} (Whatsapp)\n";
        $message_patient .= "{$branch->phone_number} (Phone Number)\n\n";
        $message_patient .= "Terimakasih\n\n";
        $message_patient .= "{$branch->name}";

        Whatsapp::send($patient->phone_number,$message_patient);

        $message_doctor = "Hallo {$patient->first_name},\n\n";
        $message_doctor .= "Anda memiliki perjanjian telekonsultasi pada:\n\n";
        // $message_doctor .= "_*------------ INFORMASI ------------*_\n\n";
        $message_doctor .= "*Tgl Konsultasi*\n";
        $message_doctor .=  Carbon::parse($appointment->consul_date)->translatedFormat('l, d F Y') . " Pukul ".date("H:i", strtotime($appointment->consul_time))." \n\n";
        $message_doctor .= "*Poli*\n";
        $message_doctor .= "{$schedule->department}\n\n";
        $message_doctor .= "*Pasien*\n";
        $message_doctor .= "{$patient->full_name}\n\n";
        $message_doctor .= "*Keluhan Utama*\n";
        $message_doctor .= "{$appointment->main_complaint}\n\n";
        $message_doctor .= "_*Mohon tidak terlambat untuk memberikan telekonsultasi.*_\n\n";
        $message_doctor .= "Untuk info lebih lanjut bisa menghubungi nomor dibawah ini:\n";
        $message_doctor .= "{$branch->whatsapp_number} (Whatsapp)\n";
        $message_doctor .= "{$branch->phone_number} (Phone Number)\n\n";
        $message_doctor .= "Terimakasih\n\n";
        $message_doctor .= "{$branch->name}";

        Whatsapp::send($patient->phone_number,$message_doctor);

        echo "ok: pendaftaran dikonfirmasi";
        exit();
    }

    public function cancelAppointment($data) {
        $branch = @$data['branch'];
        $bill = @$data['bill'];
        $appointment = @$data['appointment'];
        $check = @$data['check'];
        $request = @$data['request'];
        $patient = Person::find($appointment->patient_id);

        if($bill->status != 'waiting_payment') {
            echo "fail: status pembayaran tercatat [{$bill->status}]";
            exit();
        }

        DB::BeginTransaction();

        $bill->update([
            'status' => 'cancel',
            'last_update' => date('Y-m-d H:i:s')
        ]);

        $appointment->update([
            'status' => 'payment_cancel',
            'last_update' => date('Y-m-d H:i:s')
        ]);

        DB::commit();

        echo 'ok: Tagihan dan perjanjian dibatalkan dengan status [cancel]';
        exit();
    }
}
