<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePersons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('persons', function (Blueprint $table) {
            $table->id('pid');

            $table->foreignId('uid')
                ->nullable()
                ->references('uid')
                ->on('users')
                ->comment('uid on table users');

            $table->string('first_name', 155);

            $table->string('last_name', 155)->default(null)->nullable(true);

            $table->string('full_name', 255)->nullable(true)->default(null);

            $table->string('display_name', 255)->nullable(true)->default(null);

            $table->string('phone_number', 155)->nullable(true)->default(null);

            $table->foreignId('gid')
                ->nullable()
                ->references('gid')
                ->on('genders')
                ->comment('gid on table genders');

            $table->string('birth_place')->nullable(true)->default(null);

            $table->date('birth_date')->nullable(true)->default(null);

            $table->text('address')->nullable(true);

            $table->string('profile_pic')->nullable(true)->default(null);

            $table->foreignId('rid')
                ->nullable()
                ->references('rid')
                ->on('religions')
                ->comment('rid on table religions');

            $table->foreignId('msid')
                ->nullable()
                ->references('msid')
                ->on('married_status')
                ->comment('msid on table married_status');

            $table->foreignId('tid')
                ->nullable()
                ->references('tid')
                ->on('titles')
                ->comment('tid on table titles');

            $table->string('identity_number')->nullable(true);

            $table->foreignId('itid')
                ->nullable()
                ->references('itid')
                ->on('identity_type')
                ->comment('itid on table identity_type');

            $table->text('allergy')->nullable(true)->default(null);
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('persons');
    }
}
