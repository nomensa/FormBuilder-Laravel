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

            $table->integer('form_version_id')->unsigned();

            $table->integer('form_instance_parent_id')->nullable();
            $table->integer('status')->default(0);
            $table->integer('state_id')->default(1);
            $table->integer('user_id')->nullable();
            $table->integer('workflow_id')->unsigned()->nullable();
            $table->boolean('started_by_assessor')->default(0);
            $table->integer('allow_multiple_submissions')->default(0)->nullable();

            $table->timestamps();

            $table->foreign('form_version_id')
                ->references('id')
                ->on('form_versions');

            $table->foreign('workflow_id')
                ->references('id')
                ->on('workflows');
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
