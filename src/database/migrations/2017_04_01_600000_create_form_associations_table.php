<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFormAssociationsTable extends Migration
{

    protected $table = 'form_associations';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('root_form_submission_id')->unsigned();
            $table->string('type');
            $table->integer('destination_form_submission_id')->unsigned();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('root_form_submission_id')
                ->references('id')
                ->on('form_submissions')
                ->onDelete('cascade');

            $table->foreign('destination_form_submission_id')
                ->references('id')
                ->on('form_submissions')
                ->onDelete('cascade');
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
