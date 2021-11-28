<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultToLikesTable extends Migration
{
    const TABLE_NAME = 'likes';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->integer('likes')->default(0)->change();
            $table->integer('week_likes')->default(0)->change();
            $table->integer('month_likes')->default(0)->change();
            $table->integer('all_likes')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->integer('likes');
            $table->integer('week_likes');
            $table->integer('month_likes');
            $table->integer('all_likes');
        });
    }
}
