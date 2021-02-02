<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateFunctionAge extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
        CREATE OR REPLACE FUNCTION age(start_date date)
            RETURNS character varying
            LANGUAGE plpgsql
        AS $function$
            declare
                result varchar;
            begin
                result = age(current_date, start_date);
                return result;
            END;
        $function$
        ;
        ');

        DB::unprepared('
        CREATE OR REPLACE FUNCTION age_trans(start_date date)
            RETURNS character varying
            LANGUAGE plpgsql
        AS $function$
            declare
                result varchar;
            begin
                result = age(start_date);
                result = REPLACE(result, \'years\', \'Tahun\');
                result = REPLACE(result, \'mons\', \'Bulan\');
                result = REPLACE(result, \'days\', \'Hari\');
                return result;
            END;
        $function$
        ;
        ');

        DB::unprepared('
        CREATE OR REPLACE FUNCTION public.date_translate(date_var date, format varchar, locale varchar)
            RETURNS varchar
            LANGUAGE plpgsql
        AS $function$
            declare 
                result varchar ;
                _temp varchar;
            begin
                select set_config(\'lc_time\', locale, true) into _temp; 
                result = to_char(date_var, format);
                return result;
            END;
        $function$
        ;
        ');

        DB::unprepared('
        CREATE OR REPLACE FUNCTION public.date_translate(date_var date)
            RETURNS varchar
            LANGUAGE plpgsql
        AS $function$
            BEGIN
                return date_translate(date_var, \'TMDay, DD TMMonth YYYY\');
            END;
        $function$

        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
