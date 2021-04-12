<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZoomAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zoom_accounts', function (Blueprint $table) {
            $table->id('zaid');

            $table->foreignId('pid')
                ->nullable()
                ->references('pid')
                ->on('persons');

            $table->string('account_id');
            $table->string('email');
            $table->string('api_key');
            $table->string('api_secret');
            $table->text('jwt_token');
            $table->integer('exp_int');
            $table->timestamp('expire_token');
            
            $table->timestamp('created_at')
                ->useCurrent();
    
            $table->foreignId('create_id')
                ->references('uid')
                ->on('users');

            $table->timestamp('last_update')
                ->nullable(true)
                ->default(null);

            $table->foreignId('delete_id')
                ->nullable(true)
                ->default(null)
                ->references('uid')
                ->on('users');
                
            $table->timestamp('deleted_at')
                ->nullable(true)
                ->default(null);

            $table->boolean('is_active')
                ->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zoom_accounts');
    }
}
