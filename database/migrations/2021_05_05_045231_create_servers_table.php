<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name")->nullable(false);
            $table->string("type")->default('test');
            $table->string("ip")->nullable(false);
            $table->string("port")->nullable(false);
            $table->string("login")->nullable();
            $table->string("password")->nullable();
            $table->boolean("working")->default(true);
            $table->string("server_type")->default('Others');
            $table->string("endpoint")->nullable();
            $table->string("status")->default(200);
            $table->string("statusText")->default('OK');
            $table->string("request_type")->default('POST');
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
        Schema::dropIfExists('servers');
    }
}