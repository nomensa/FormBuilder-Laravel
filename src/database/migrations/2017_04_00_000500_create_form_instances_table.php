<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * A Form Instance is a users interaction (not necesserily a submission) with a form.
 * For example, starting a new form which then gets passed to another user.
 */
class CreateFormInstancesTable extends Migration
{

    protected $table = 'form_instances';

    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('entry_form_id');
            $table->integer('form_instance_parent_id')->nullable();

            $table->string('title');

            // TODO Move these 3 fields to a new table called form_versions
            //$table->string('schema_hash', 32); // MD5 hash of the JSON schema
            $table->text('schema')->nullable();
            $table->text('options')->nullable();

            $table->integer('status')->default(0);
            $table->integer('state_id')->default(1);
            $table->integer('user_id')->nullable();
            $table->integer('workflow_id')->unsigned()->nullable();
            $table->boolean('started_by_assessor')->default(0);
            $table->integer('allow_multiple_submissions')->default(0)->nullable();

            $table->boolean('has_supporting_documents')->default(1)->nullable();
            $table->softDeletes();
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
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists($this->table);
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
