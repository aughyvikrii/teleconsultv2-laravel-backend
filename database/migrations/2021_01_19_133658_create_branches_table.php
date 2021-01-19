<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id('bid');
            $table->string('code', 50);
            $table->string('company');
            $table->string('name');
            $table->string('npwp')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('his_api_production')->nullable();
            $table->string('his_api_development')->nullable();
            $table->string('his_api_user')->nullable();
            $table->string('his_api_pass')->nullable();
            $table->string('espay_commcode')->nullable();
            $table->string('espay_api_key')->nullable();
            $table->string('espay_password')->nullable();
            $table->string('espay_signature')->nullable();

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
        Schema::dropIfExists('branches');
    }
}
