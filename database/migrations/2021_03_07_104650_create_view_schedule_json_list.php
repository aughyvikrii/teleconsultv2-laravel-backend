<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateViewScheduleJsonList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("CREATE OR REPLACE VIEW public.v_schedule_json_list
            AS SELECT x.doctor_id,
                x.bid,
                x.deid,
                json_build_object('monday',
                    CASE
                        WHEN x.monday IS NULL THEN '[]'::json
                        ELSE x.monday
                    END, 'tuesday',
                    CASE
                        WHEN x.tuesday IS NULL THEN '[]'::json
                        ELSE x.tuesday
                    END, 'wednesday',
                    CASE
                        WHEN x.wednesday IS NULL THEN '[]'::json
                        ELSE x.wednesday
                    END, 'thursday',
                    CASE
                        WHEN x.thursday IS NULL THEN '[]'::json
                        ELSE x.thursday
                    END, 'friday',
                    CASE
                        WHEN x.friday IS NULL THEN '[]'::json
                        ELSE x.friday
                    END, 'saturday',
                    CASE
                        WHEN x.saturday IS NULL THEN '[]'::json
                        ELSE x.saturday
                    END, 'sunday',
                    CASE
                        WHEN x.sunday IS NULL THEN '[]'::json
                        ELSE x.sunday
                    END) AS schedule
            FROM ( SELECT persons.pid AS doctor_id,
                        schedules.bid,
                        schedules.deid,
                        COALESCE(json_agg(
                            CASE
                                WHEN schedules.weekday <> 1 THEN NULL::json
                                ELSE json_build_object('schedule_id', schedules.scid, 'start_hour', schedules.start_hour, 'end_hour', schedules.end_hour, 'duration', schedules.duration, 'fee', schedules.fee)
                            END) FILTER (WHERE schedules.weekday = 1)) AS monday,
                        COALESCE(json_agg(
                            CASE
                                WHEN schedules.weekday <> 2 THEN NULL::json
                                ELSE json_build_object('schedule_id', schedules.scid, 'start_hour', schedules.start_hour, 'end_hour', schedules.end_hour, 'duration', schedules.duration, 'fee', schedules.fee)
                            END) FILTER (WHERE schedules.weekday = 2)) AS tuesday,
                        COALESCE(json_agg(
                            CASE
                                WHEN schedules.weekday <> 3 THEN NULL::json
                                ELSE json_build_object('schedule_id', schedules.scid, 'start_hour', schedules.start_hour, 'end_hour', schedules.end_hour, 'duration', schedules.duration, 'fee', schedules.fee)
                            END) FILTER (WHERE schedules.weekday = 3)) AS wednesday,
                        COALESCE(json_agg(
                            CASE
                                WHEN schedules.weekday <> 4 THEN NULL::json
                                ELSE json_build_object('schedule_id', schedules.scid, 'start_hour', schedules.start_hour, 'end_hour', schedules.end_hour, 'duration', schedules.duration, 'fee', schedules.fee)
                            END) FILTER (WHERE schedules.weekday = 4)) AS thursday,
                        COALESCE(json_agg(
                            CASE
                                WHEN schedules.weekday <> 5 THEN NULL::json
                                ELSE json_build_object('schedule_id', schedules.scid, 'start_hour', schedules.start_hour, 'end_hour', schedules.end_hour, 'duration', schedules.duration, 'fee', schedules.fee)
                            END) FILTER (WHERE schedules.weekday = 5)) AS friday,
                        COALESCE(json_agg(
                            CASE
                                WHEN schedules.weekday <> 6 THEN NULL::json
                                ELSE json_build_object('schedule_id', schedules.scid, 'start_hour', schedules.start_hour, 'end_hour', schedules.end_hour, 'duration', schedules.duration, 'fee', schedules.fee)
                            END) FILTER (WHERE schedules.weekday = 6)) AS saturday,
                        COALESCE(json_agg(
                            CASE
                                WHEN schedules.weekday <> 7 THEN NULL::json
                                ELSE json_build_object('schedule_id', schedules.scid, 'start_hour', schedules.start_hour, 'end_hour', schedules.end_hour, 'duration', schedules.duration, 'fee', schedules.fee)
                            END) FILTER (WHERE schedules.weekday = 7)) AS sunday
                    FROM persons
                        JOIN ( SELECT schedules_1.scid,
                                schedules_1.pid,
                                schedules_1.bid,
                                schedules_1.deid,
                                schedules_1.weekday,
                                schedules_1.fee,
                                schedules_1.start_hour,
                                schedules_1.end_hour,
                                schedules_1.duration,
                                schedules_1.created_at,
                                schedules_1.create_id,
                                schedules_1.last_update,
                                schedules_1.delete_id,
                                schedules_1.deleted_at,
                                schedules_1.is_active
                            FROM schedules schedules_1
                            ORDER BY schedules_1.start_hour) schedules ON persons.pid = schedules.pid
                    WHERE schedules.is_active IS TRUE
                    GROUP BY persons.pid, schedules.bid, schedules.deid) x");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('view_schedule_json_list');
    }
}
