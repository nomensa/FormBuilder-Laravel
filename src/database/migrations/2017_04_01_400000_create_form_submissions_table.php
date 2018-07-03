<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFormSubmissionsTable extends Migration
{

    protected $table = 'form_submissions';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');

            $table->integer('form_instance_id')->unsigned();

            $table->integer('user_id');
            $table->integer('form_submission_status_id')->default(1);
            $table->string('title')->nullable();
            $table->boolean('readonly')->default(0)->nullable();
            $table->boolean('signoff')->default(0)->nullable();
            $table->integer('state_id')->nullable();

            $table->timestamp('date_submitted_at')->nullable();
            $table->timestamp('date_signoff_at')->nullable();
            $table->timestamp('date_rejected_at')->nullable();
            $table->integer('rejected_count')->default(0);

            $table->text('uuid')->nullable();
            $table->integer('randomiser')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('form_instance_id')
                ->references('id')
                ->on('form_instances')
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
