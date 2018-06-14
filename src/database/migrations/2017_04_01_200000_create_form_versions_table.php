<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFormVersionsTable extends Migration
{

    protected $table = 'form_versions';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');

            $table->integer('entry_form_id')->unsigned();
            $table->boolean('is_current');

            $table->string('version'); // Something like '1.3'
            $table->string('hash', 32); // MD5 hash of the JSON schema + options
            $table->json('schema')->nullable(); // JSON schema
            $table->json('options')->nullable(); // JSON options

            $table->timestamps();

            $table->foreign('entry_form_id')
                ->references('id')
                ->on('entry_forms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists($this->table);
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

}
