<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErrorLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('error_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string("error_name")->nullable(false);
            $table->string("error_status")->default(200);
            $table->string("name")->nullable(false);
            $table->string("ip")->nullable(false);
            $table->string("port")->nullable(false);
            $table->string("endpoint")->nullable();
            $table->string("server_type")->default('Others');
            $table->string("request_type")->default('POST');
            $table->string("success")->default(0);
            $table->integer("error_count")->default(1);
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
        Schema::dropIfExists('error_logs');
    }
}
