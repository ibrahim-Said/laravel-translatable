<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTranslatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('translates')) return;
        Schema::create('translates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('translatable_type');
            $table->integer('translatable_id');
            $table->string('column');
            $table->text('content');
            $table->string('locale', 5)->index();
            $table->timestamps();
            $table->index(['translatable_id', 'translatable_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('translates');
    }
}
