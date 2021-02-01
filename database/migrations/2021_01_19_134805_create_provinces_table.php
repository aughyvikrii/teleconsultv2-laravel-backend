<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProvincesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id('pvid',50);

            $table->foreignId('iid')
                ->references('iid')
                ->on('islands')
                ->comment('IID From table island');
                
            $table->string('iso_code', 10);
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->foreignId('create_id')
                ->default('0')
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
        Schema::dropIfExists('provinces');
    }
}
