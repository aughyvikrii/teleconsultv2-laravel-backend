<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('uid');
            $table->string('code')->nullable(true)->default(null)->unique();
            $table->string('email')->nullable(false);
            $table->string('password')->nullable(false);
            $table->string('phone_number');
            $table->foreignId('lid')
                    ->references('lid')
                    ->on('level')
                    ->comment('Level ID');
            $table->timestamp('verified_at')->nullable(true)->default(null);
            $table->string('verified_code')->nullable(true)->default(null);
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
