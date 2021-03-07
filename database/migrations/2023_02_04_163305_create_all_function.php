<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAllFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $base_url = 'http://localhost:8000';
        $person_pic_dir = 'storage/image/profile';
        $doctor_pic_male = 'doctor-male.png';
        $doctor_pic_female = 'doctor-female.png';
        $patient_pic_male = 'patient-male.png';
        $patient_pic_female = 'patient-female.png';
        $admin_pic_male = 'admin.png';
        $admin_pic_female = 'admin.png';

        DB::unprepared('
            CREATE OR REPLACE FUNCTION config(confname varchar)
                RETURNS varchar
                LANGUAGE plpgsql
            AS $function$'.
            "   declare
                    base_url varchar default '{$base_url}';
                    person_pic_dir varchar default '${person_pic_dir}';
                    doctor_pic_female varchar default '{$doctor_pic_female}';
                    doctor_pic_male varchar default '${doctor_pic_male}';
                    patient_pic_male varchar default '${patient_pic_male}';
                    patient_pic_female varchar default '${patient_pic_female}';
                    admin_pic_male varchar default '${admin_pic_male}';
                    admin_pic_female varchar default '${admin_pic_female}';
                begin
                    if confname = 'base_url' then return base_url;
                    elsif confname = 'person_pic_dir' then return person_pic_dir;
                    elsif confname = 'doctor_pic_female' then return doctor_pic_female;
                    elsif confname = 'doctor_pic_male' then return doctor_pic_male;
                    elsif confname = 'patient_pic_male' then return patient_pic_male;
                    elsif confname = 'patient_pic_female' then return patient_pic_female;
                    elsif confname = 'admin_pic_male' then return admin_pic_male;
                    elsif confname = 'admin_pic_female' then return admin_pic_female;
                    else return null;
                    end if;
                END;
            "
            .'$function$;'
        );

        DB::unprepared('
            CREATE OR REPLACE FUNCTION base_url(custom_path varchar default null)
                RETURNS varchar
                LANGUAGE plpgsql
            AS $function$
                begin
                    return concat(config(\'base_url\'), \'/\', LTRIM(custom_path, \'/\'));
                END;
            $function$
            ;
        ');

        DB::unprepared('
            CREATE OR REPLACE FUNCTION profile_pic(picture varchar default \'person.png\')
                RETURNS varchar
                LANGUAGE plpgsql
            AS $function$
                declare
                    pic_url varchar default base_url(config(\'person_pic_dir\'));
                BEGIN
                    return CONCAT(pic_url, \'/\', LTRIM(picture, \'/\'));
                END;
            $function$
        ');

        DB::unprepared('
            CREATE OR REPLACE FUNCTION doctor_pic(picture character varying DEFAULT \'\'::character varying, gid integer DEFAULT NULL::integer)
                RETURNS character varying
                LANGUAGE plpgsql
            AS $function$
            begin
                if picture = \'\' OR picture IS NULL then
                    if picture = \'\' and gid is null then picture = config(\'doctor_pic_male\');
                    elsif gid = 1 then picture = config(\'doctor_pic_male\');
                    elsif gid = 2 then picture = config(\'doctor_pic_female\');
                    else picture = config(\'doctor_pic_male\');
                    end if;
                end if;
                return profile_pic(picture);
            END;
            $function$
            ;
        ');

        DB::unprepared('
            CREATE OR REPLACE FUNCTION patient_pic(picture character varying DEFAULT \'\'::character varying, gid integer DEFAULT NULL::integer)
                RETURNS character varying
                LANGUAGE plpgsql
            AS $function$
            begin
                if picture = \'\' OR picture IS NULL then
                    if picture = \'\' and gid is null then picture = config(\'patient_pic_male\');
                    elsif gid = 1 then picture = config(\'patient_pic_male\');
                    elsif gid = 2 then picture = config(\'patient_pic_female\');
                    else picture = config(\'patient_pic_male\');
                    end if;
                end if;
                return profile_pic(picture);
            END;
            $function$
            ;
        ');

        DB::unprepared('
            CREATE OR REPLACE FUNCTION department_pic(picture character varying DEFAULT \'department.png\'::character varying)
                RETURNS character varying
                LANGUAGE plpgsql
            AS $function$
            declare
                image_url varchar default base_url(\'storage/image/department\');
            begin
                if picture = \'\' or picture is null then picture = \'department.png\'; end if;
                return CONCAT(image_url, \'/\', LTRIM(picture, \'/\'));
            END;
            $function$
            ;
        ');

        DB::unprepared('
            CREATE OR REPLACE FUNCTION branch_pic(picture character varying DEFAULT \'branch.png\'::character varying)
                RETURNS character varying
                LANGUAGE plpgsql
            AS $function$
            declare
                image_url varchar default base_url(\'storage/image/branch\');
            begin
                if picture = \'\' or picture is null then picture = \'branch.png\'; end if;
                return CONCAT(image_url, \'/\', LTRIM(picture, \'/\'));
            END;
            $function$
            ;
        ');

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
            CREATE OR REPLACE FUNCTION id_age(start_date date)
                RETURNS character varying
                LANGUAGE plpgsql
            AS $function$
                declare
                    result varchar;
                begin
                    result = age(start_date);
                    result = REPLACE(result, \'years\', \'Tahun\');
                    result = REPLACE(result, \'year\', \'Tahun\');
                    result = REPLACE(result, \'mons\', \'Bulan\');
                    result = REPLACE(result, \'mon\', \'Bulan\');
                    result = REPLACE(result, \'days\', \'Hari\');
                    result = REPLACE(result, \'day\', \'Hari\');
                    return result;
                END;
            $function$
            ;
        ');

        DB::unprepared('
            CREATE OR REPLACE FUNCTION date(date_var date, format varchar default \'TMDay, DD TMMonth YYYY\', locale varchar default \'en_US\')
                RETURNS varchar
                LANGUAGE plpgsql
            AS $function$
                declare 
                    result varchar ;
                    _temp varchar;
                begin
                    select concat(locale, \'.UTF-8\') into locale; 
                    select set_config(\'lc_time\', locale, true) into _temp; 
                    result = to_char(date_var, format);
                    return result;
                END;
            $function$
            ;
        ');

        DB::unprepared('
            CREATE OR REPLACE FUNCTION id_date(date_var date, format varchar default \'TMDay, DD TMMonth YYYY\')
                RETURNS varchar
                LANGUAGE plpgsql
            AS $function$
                declare 
                    result varchar ;
                    _temp varchar;
                begin
                    return date(date_var, format, \'id_ID\');
                END;
            $function$
            ;
        ');

        DB::unprepared('
            CREATE OR REPLACE FUNCTION weekday(weekday integer, lang varchar default \'en_US\')
                RETURNS character varying
                LANGUAGE plpgsql
            AS $function$
                declare
                    result varchar;
                    temp varchar ;
                    date_temp  date;
                BEGIN
                    if weekday = 1 then
                        date_temp = \'2018-01-01\';
                    elsif weekday = 2 then
                        date_temp = \'2018-01-02\';
                    elsif weekday = 3 then
                        date_temp = \'2018-01-03\';
                    elsif weekday = 4 then
                        date_temp = \'2018-01-04\';
                    elsif weekday = 5 then
                        date_temp = \'2018-01-05\';
                    elsif weekday = 6 then
                        date_temp = \'2018-01-06\';
                    else
                        date_temp = \'2018-01-07\';
                    end if;
                    select concat(lang, \'.UTF-8\') into lang; 
                    select set_config(\'lc_time\', lang, true) into temp;
                    
                    return TO_CHAR(date_temp, \'TMDay\');
                END;
            $function$
            ;
        ');

        DB::unprepared('
            CREATE OR REPLACE FUNCTION id_weekday(weekday integer)
            RETURNS character varying
            LANGUAGE plpgsql
            AS $function$
                declare
                    result varchar;
                    temp varchar ;
                    date_temp  date;
                BEGIN
                    return weekday(weekday, \'id_ID\');
                END;
            $function$
            ;
        ');

        DB::unprepared("CREATE OR REPLACE FUNCTION ftime(the_time time)
            RETURNS character varying
            LANGUAGE plpgsql
            AS $function$
                BEGIN
                    return to_char(the_time, 'HH24:MI');
                END;
            $function$
        ;");
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
