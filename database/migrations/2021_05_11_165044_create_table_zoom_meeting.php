<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableZoomMeeting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zoom_meetings', function (Blueprint $table) {
            $table->id('zmid');
            $table->foreignId('aid')
                ->references('aid')
                ->on('appointments');

            $table->foreignId('scid')
                ->references('scid')
                ->on('schedules');

            $table->string('meeting_id');
            $table->string('uuid');
            $table->text('start_url');
            $table->text('join_url');
            $table->string('password');

            $table->json('raw_data');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zoom_meetings');
    }
}
