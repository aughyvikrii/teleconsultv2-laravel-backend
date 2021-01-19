<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoctorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id('doid');
            $table->foreignId('pid')
                ->references('pid')
                ->on('persons');

            $table->foreignId('bid')
                ->references('bid')
                ->on('branches');

            $table->foreignId('deid')
                ->references('deid')
                ->on('departements');

            $table->foreignId('sid')
                ->nullable()
                ->default(null)
                ->references('sid')
                ->on('specialists');

            $table->integer('fee_consultation');

            $table->boolean('is_active')->default(true);

            $table->timestamp('created_at')->useCurrent();
            $table->foreignId('create_id')
                ->references('uid')
                ->on('users');

            $table->timestamp('last_update')->nullable(true)->default(null);

            $table->foreignId('delete_id')
                ->nullable(true)
                ->default(null)
                ->references('uid')
                ->on('users');
            $table->timestamp('deleted_at')->nullable(true)->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('doctors');
    }
}
