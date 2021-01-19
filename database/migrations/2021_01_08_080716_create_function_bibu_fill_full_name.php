<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateFunctionBibuFillFullName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
        CREATE OR REPLACE FUNCTION public.bibu_fill_full_name()
            RETURNS trigger
            LANGUAGE plpgsql
        AS $function$
        declare 
        full_name varchar;
            BEGIN
                select trim(CONCAT(new.first_name,\' \',new.last_name)) into full_name;
                new.full_name = full_name;
                return new;
            END;
        $function$
        ;
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
        DB::unprepared('DROP FUNCTION IF EXISTS bibu_fill_full_name');
    }
}
