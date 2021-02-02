<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth, DB;
use \App\Models\{Person, Schedule};

class ScheduleController extends Controller
{
    /**
     * List Schedule
     * 
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */

    public function List(Request $request) {
        $list = Schedule::selectRaw('schedules.scid as schedule_id, persons.pid as doctor_id, persons.display_name as doctor
                , branches.bid as branch_id, branches.name as branch, departments.deid as department_id, departments.name as department
                , schedules.weekday as weekday_id, weekday_translate(schedules.weekday) as weekday, schedules.fee, schedules.start_hour,  schedules.end_hour
                , schedules.duration, schedules.is_active
                , specialists.sid as specialist_id, specialists.title as specialist_title, specialists.alt_name as specialist')
                ->selectRaw("profile_pic('".asset('storage/img/profile')."', persons.profile_pic, users.lid, persons.gid) as profile_pic")
                ->joinFullInfo()
                ->get();

        return response()->json([
            'status' => true,
            'message' => 'Data ditemukan',
            'data' => $list
        ]);
    }
}
