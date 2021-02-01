<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTablePersons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->foreignId('sid')
                ->nullable()
                ->comment('Spesialis if person is doctor')
                ->references('sid')
                ->on('specialists');

            $table->foreignId('vid')
                ->nullable()
                ->references('vid')
                ->on('villages');

            $table->foreignId('fmid')
                ->nullable()
                ->references('fmid')
                ->on('family_master');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn([
                'sid', 'vid',  'fmid'
            ]);
        });
    }
}
