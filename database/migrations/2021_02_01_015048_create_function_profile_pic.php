<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateFunctionProfilePic extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
                CREATE OR REPLACE FUNCTION profile_pic(asset_url character varying, profile_pic character varying, lid bigint, gid bigint)
                    RETURNS character varying
                    LANGUAGE plpgsql
                AS $function$
                declare
                    image varchar;
                    BEGIN
                    if profile_pic <> \'\' then
                        image = profile_pic;
                    else
                        if lid = \'1\' then
                            image = \'admin.png\';
                        elsif lid = \'2\' then
                            if gid = \'2\' then
                                image = \'doctor-female.png\';
                            else
                                image = \'doctor-male.png\';
                            end if;
                        else
                            if gid = \'2\' then
                                image = \'patient-female.png\';
                            else
                                image = \'patient-male.png\';
                            end if;
                        end if;
                    end if;
                    
                    return CONCAT(asset_url, \'/\', image);
                    END;
                $function$
                ;
       
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP FUNCTION IF EXISTS profile_pic');
    }
}
