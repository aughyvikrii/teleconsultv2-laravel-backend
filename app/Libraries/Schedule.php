<?php
namespace App\Libraries;

use \App\Models\Schedule as MSchedule;
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
        $endTime = Carbon::parse('2020-01-01 ' . $this->data->end_hour);
        $intervals = CarbonInterval::minutes($this->data->duration)->toPeriod($startTime, $endTime);

        $times = [];

        foreach($intervals as $time) {
            if($withKey) $times[ $time->format('H:i') ] = $time->format('H:i');
            else $times[] = $time->format('H:i');
        }

        return $times;
    }

    public function getTimeDetail($withKey = false, $availableOnly = false) {
        if($this->error()) return $this->message;

        $startTime = Carbon::parse('2020-01-01 ' . $this->data->start_hour);
        $endTime = Carbon::parse('2020-01-01 ' . $this->data->end_hour);
        $intervals = CarbonInterval::minutes($this->data->duration)->toPeriod($startTime, $endTime);

        $times = [];

        foreach($intervals as $time) {
            
            $data = [
                'time' => $time->format('H:i'),
                'status' => 'available'
            ];

            if($withKey) $times[ $data['time'] ] = $data;
            else $times[] = $data;
        }

        return $times;
    }
}