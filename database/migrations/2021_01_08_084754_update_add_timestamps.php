<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAddTimestamps extends Migration
{

    protected $tables = [
        'level', 'users', 'genders', 'religions', 'married_status', 'titles', 'identity_type', 'persons'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // foreach($this->tables as $tableName) {
        //     Schema::table($tableName, function(Blueprint $table){
                
        //         $table->timestamp('created_at')->useCurrent();
        //         $table->foreignId('create_id')
        //             ->default('0')
        //             ->references('uid')
        //             ->on('users');

        //         $table->timestamp('last_update')->nullable(true)->default(null);

        //         $table->foreignId('delete_id')
        //             ->nullable(true)
        //             ->default(null)
        //             ->references('uid')
        //             ->on('users');
        //         $table->timestamp('deleted_at')->nullable(true)->default(null);
        //     });
        // }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach($this->tables as $tableName) {
            Schema::table($tableName, function(Blueprint $table){
                $table->dropColumn([
                    'created_at', 'create_id', 'last_update', 'delete_id', 'deleted_at'
                ]);
            });
        }
    }
}
