<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFormSubmissionFieldsTable extends Migration
{
    protected $table = 'form_submission_fields';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('form_submission_id')->unsigned();
            $table->string('row_name',64)->nullable();
            $table->integer('group_index')->unsigned()->nullable(); // Used for cloneable RowGroups
            $table->string('field_name', 100);

            // Different types of value
            $table->string('value')->nullable();
            $table->integer('value_int')->nullable();
            $table->date('value_date')->nullable();

            $table->integer('weight');
            $table->integer('randomiser')->nullable();

            $table->timestamps();

            $table->foreign('form_submission_id')
                ->references('id')
                ->on('form_submissions')
                ->onDelete('cascade');

            $table->index([
                'form_submission_id',
                'row_name',
                'group_index','field_name'
            ], 'value_identifier_index');

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
