<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableFamilyTree extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('family_tree', function (Blueprint $table) {
            $table->id('ftid');
            $table->foreignId('fmid')
                ->references('fmid')
                ->on('family_master');
            $table->foreignId('pid')
                ->references('pid')
                ->on('persons');
            $table->string('status', 15)->nullable();
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
        Schema::dropIfExists('family_tree');
    }
}
