<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFormSubmissionStatusesTable extends Migration
{

    protected $table = 'form_submission_statuses';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('entry_form_type_id')->unsigned()->nullable();
            $table->string('slug',64)->unique();
            $table->string('title',50);
            $table->string('alt_title',50);
            $table->text('description')->nullable();

            $table->foreign('entry_form_type_id')
                ->references('id')
                ->on('entry_form_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::select( DB::raw('ALTER TABLE ' . $this->table . '
            DROP FOREIGN KEY form_submission_statuses_entry_form_type_id_foreign'));

        DB::select( DB::raw('ALTER TABLE ' . $this->table . '
            DROP INDEX form_submission_statuses_entry_form_type_id_foreign'));

        Schema::dropIfExists($this->table);
    }
}
