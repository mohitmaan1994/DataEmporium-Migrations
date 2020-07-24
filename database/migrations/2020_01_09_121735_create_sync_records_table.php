<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSyncRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sync_records', function (Blueprint $table) {
            $table->increments('record_id');
            $table->string('uin', 30);
            $table->string('name', 100);
            $table->string('sms', 25)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('room_number', 30)->nullable();
            $table->string('room_location', 30)->nullable();
            $table->string('tags', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sync_records');
    }
}
