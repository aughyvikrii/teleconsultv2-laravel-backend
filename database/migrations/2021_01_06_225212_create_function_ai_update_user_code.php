<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateFunctionAiUpdateUserCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE OR REPLACE FUNCTION ai_update_user_code()
                RETURNS trigger
                LANGUAGE plpgsql
            AS $function$
            declare 
            isUsed integer;
            randStr varchar;
            uniqCode varchar;

                begin
                    while uniqCode is null loop 
                        randStr = random_string(6);
                        select uid into isUsed from users where code = randStr;
                        if isUsed is null then
                        uniqCode = randStr;
                        end if;
                    end loop;
                    update users set code = uniqCode where uid = new.uid;
                
                    return new;
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
        DB::unprepared('DROP FUNCTION IF EXISTS ai_update_user_code');
    }
}
