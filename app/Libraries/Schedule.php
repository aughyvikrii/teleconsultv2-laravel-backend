<?php
namespace App\Libraries;

use \App\Models\Schedule as MSchedule;
use \App\Models\Appointment;
use DB;
use \Carbon\{ Carbon, CarbonPeriod, CarbonInterval };

class Schedule {

    public $data;

    public $error;

    public $message;

    public $limit_appointment = '30';

    public $weekday_en = [
        '1' => 'monday', '2' => 'tuesday', '3' => 'wednesday', '4' => 'thursday', '5' => 'friday', '6' => 'saturday', '7' => 'sunday'
    ];

    public $weekday_id = [
        '1' => 'senin', '2' => 'selasa', '3' => 'rabu', '4' => 'kamis', '5' => 'jumat', '6' => 'sabtu', '7' => 'minggu'
    ];

    function __construct($schedule) {
        if($schedule instanceof \App\Models\Schedule) {
            $this->data = $schedule;
        }
        else if ( is_integer($schedule) ) {
            $schedule = MSchedule::apiScheduleDetailByScid($schedule);
            if(!$schedule) {
                $this->message = 'Schedule not found';
                $this->error = true;
            } else {
                $this->data = $schedule;
            }
        } else {
            $this->message = 'error: construct';
            $this->error = true;
        }
    }

    public function error() { return $this->error; }
    public function message() { return $this->message; }
    public function weekday($id, $lang='en') {
        $bucket = "weekday_$lang";
        if(!isset($this->$bucket)) return null;
        return @$this->$bucket[$id];
    }

    public function getDateTeleconsult($withKey = false) {
        if($this->error()) return $this->message;

        $startDate = Carbon::now()->modify('this '. $this->weekday($this->data->weekday) );
        $endDate = Carbon::now()->addDays($this->limit_appointment);
        
        $days = [];

        for ($date = $startDate; $date->lte($endDate); $date->addWeek()) {
            
            $day = [
                'date' => $date->format('Y-m-d'),
                'translate' => $date->translatedFormat('l, d F Y'),
            ];

            if($withKey) $days[$day['date']] = $day;
            else $days[] = $day;
        }

        return $days;
    }

    public function getTimeTeleconsult($withKey = false) {
        if($this->error()) return $this->message;

        $startTime = Carbon::parse('2020-01-01 ' . $this->data->start_hour);
        $endTime = Carbon::parse('2020-01-01 ' . $this->data->end_hour)->subMinutes($this->data->duration);
        $intervals = CarbonInterval::minutes($this->data->duration)->toPeriod($startTime, $endTime);

        $times = [];

        foreach($intervals as $time) {
            if($withKey) $times[ $time->format('H:i') ] = $time->format('H:i');
            else $times[] = $time->format('H:i');
        }

        return $times;
    }

    public function getTimeDetail($withKey = false, $availableOnly = false, $date = null) {
        if($this->error()) return $this->message;

        $startTime = Carbon::parse('2020-01-01 ' . $this->data->start_hour);
        $endTime = Carbon::parse('2020-01-01 ' . $this->data->end_hour)->subMinutes($this->data->duration);
        $intervals = CarbonInterval::minutes($this->data->duration)->toPeriod($startTime, $endTime);

        $list_appointment = $times = [];

        if($date) {
            $list_appointment = $this->list_appointment([
                'date' => $date
            ]);
        }

        foreach($intervals as $time) {
            
            $data = [
                'time' => $time->format('H:i'),
                'status' => 'not_checked'
            ];

            if($date) {
                $format_date = $date . " " . $data['time'];
                if(in_array($format_date, array_keys($list_appointment))) {

                    switch($list_appointment[$format_date]['status']) {
                        case 'waiting_consul': $status = 'booked'; break;
                        case 'done': $status = 'booked'; break;
                        case 'waiting_payment': $status = 'waiting_payment'; break;
                        default: $status = 'available';
                    }

                    $data['status'] = $status;

                    if($data['status'] == 'waiting_payment') {
                        $data['expired_at'] = $list_appointment[$format_date]['expired_at'];
                    }
                } else if(strtotime($format_date) <= strtotime("+60 minutes")) {
                    $data['status'] = 'not_available';
                } else {
                    $data['status'] = 'available';
                }
            }

            if($availableOnly && @$data['status'] != 'available') continue;

            if($withKey) $times[ $data['time'] ] = $data;
            else $times[] = $data;
        }

        return $times;
    }

    public function validConsulDate($date, $time) {
        $date = date('Y-m-d', strtotime($date));
        $time = date('H:i', strtotime("2020-01-01 ". $time));
        $available_date = $this->getDateTeleconsult(true);

        if(!in_array($date, array_keys($available_date))) {
            return [null, 'Tanggal tidak valid'];
        }

        $available_time = $this->getTimeDetail(true, false, $date);

        if(!in_array($time, array_keys($available_time))) {
            return [null, 'Jam tidak valid'];
        }

        $time_detail = $available_time[$time];
        if(@$time_detail['status'] != 'available') {
            switch($time_detail['status']) {
                case 'waiting_payment': $message = "Jam ini sudah dipesan"; break;
                case 'waiting_consul': $message = "Jam ini sudah dipesan"; break;
                case 'done': $message = "Jam ini sudah dipesan"; break;

                default: $message = 'Jam ini tidak dapat didaftarkan';
            }
            return [null, $message];
        }

        $full_date_format = $date . " " . $time;

        if(strtotime($full_date_format) <= time()) {
            return [null, 'Tidak bisa daftar kurang dari waktu saat ini'];
        }

        return [true, null];
    }

    public function list_appointment($params) {
        $addWhere = "";

        $filters[] = $this->data->schedule_id;

        if($date = @$params['date']) {
            $addWhere .= " AND appointments.consul_date = ?";
            $filters[] = $date;
        }

        $list = Appointment::join('bills', 'appointments.aid', '=', 'bills.aid')
                ->selectRaw('appointments.aid, scid, consul_date, TO_CHAR(consul_time, \'HH24:MI\') as consul_time, appointments.status
                , bills.expired_at')
                ->whereRaw("appointments.scid = ? AND appointments.status IN ('waiting_payment' , 'waiting_consul', 'done') $addWhere", $filters)
                ->get();
        $return = [];
        foreach($list->toArray() as $list) {
            $list = (Object) $list;
            $return[
                @$list->consul_date . " " . @$list->consul_time
            ] = (array) $list;
        }

        return $return;
    }
}