<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\{Appointment, Bill, ZoomMeeting};
use App\Libraries\Zoom;

use Str;

class GenerateZoom extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronjob:generate_zoom';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Zoom For Paid Appointment';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ungenerate_zoom_meeting = Appointment::JoinFullInfo()
            ->selectRaw("
                row_to_json(appointments.*) as appointment_json,
                row_to_json(patient.*) as patient_json,
                row_to_json(doctor.*) as doctor_json,
                row_to_json(schedules.*) as schedule_json,
                row_to_json(departments.*) as department_json,
                row_to_json(branches.*) as branch_json,
                row_to_json(bills.*) as bill_json,
                row_to_json(zoom_accounts.*) as zoom_account_json
            ")
            ->join('zoom_accounts', 'zoom_accounts.pid', '=', 'doctor.pid')
            ->leftjoin('zoom_meetings', 'zoom_meetings.aid', '=', 'appointments.aid')
            ->whereRaw('bills.status = ? AND zoom_meetings.zmid IS NULL', ['paid'])
            ->limit(10)
            ->get();

        if($ungenerate_zoom_meeting->count() <= 0) {
            $this->info("Tidak ada zoom harus dibuat");
            return 0;
        }

        foreach($ungenerate_zoom_meeting as $item) {
            $zoom = new Zoom($item->zoom_account_json);
            $doctor = $item->doctor_json;
            $appointment = $item->appointment_json;
            $schedule = $item->schedule_json;

            $appointment['consul_datetime'] = $appointment['consul_date'] .'T'. $appointment['consul_time'];

            $appointment['consul_datetime_translated'] = \Carbon\Carbon::parse($appointment['consul_date'].' '.$appointment['consul_time'])->translatedFormat('l, d F Y');

            $agenda = "Telekonsultasi {$doctor['display_name']} pada {$appointment['consul_datetime_translated']} Pukul ". date('H:i', strtotime($appointment['consul_time']));

            $createMeeting = $zoom->createMeeting([
                'topic' => $agenda,
                'type' => 2,
                'start_time' => $appointment['consul_datetime'],
                'duration' => $schedule['duration'],
                'timezone' => 'Asia/Jakarta',
                'password' => rand(100000, 999999),
                'agenda' => $agenda,
                'settings' => [
                    'host_video' => false,
                    'join_before_host' => true,
                    'jbh_time' => 0,
                    'audio' => 'both',
                ]
            ]);

            if(!@$createMeeting['uuid']) {
                $this->info("Gagal membuat zoom meeting untuk perjanjian {$appointment['aid']}");
                continue;
            }

            $zoomMeeting = ZoomMeeting::create([
                'aid' => $appointment['aid'],
                'scid' =>  $schedule['scid'],
                'meeting_id' => $createMeeting['id'],
                'uuid' => $createMeeting['uuid'],
                'start_url' =>  $createMeeting['start_url'],
                'join_url' => $createMeeting['join_url'],
                'password' => $createMeeting['password'],
                'raw_data' => $createMeeting
            ]);

            $this->info("Berhasil membuat zoom meeting untuk perjanjian {$appointment['aid']} | meeting id: {$createMeeting['id']}");
        }

        return 0;
    }
}
