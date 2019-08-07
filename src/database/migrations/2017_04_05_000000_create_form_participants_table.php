<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFormParticipantsTable extends Migration
{

    protected $table = 'form_participants';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {

            $table->increments('id');

            $table->integer('form_instance_id')->unsigned(); // This will be deprecated in future

            $table->integer('form_submission_id')->unsigned()->nullable();

            $table->integer('form_participant_type_id')->unsigned();

            $table->integer('form_participant_status_id')->unsigned();

            $table->string('name', 200)->nullable();
            $table->string('position', 200)->nullable();
            $table->string('role', 64)->nullable();
            $table->string('reference', 200)->nullable();
            $table->string('email', 200)->nullable();

            $table->integer('participant_user_id')->nullable();
            $table->integer('user_id')->unsigned();
            $table->text('comments')->nullable();
            $table->boolean('signoff')->default(0);
            $table->integer('status')->default(0);
            $table->timestamp('date_rejected')->nullable();
            $table->timestamp('date_signoff')->nullable();
            $table->integer('rejected_count')->default(0);

            $table->boolean('is_guest')->default(0)->index();
            $table->string('uuid', 64)->nullable()->index();

            $table->text('signoff_position')->nullable();
            $table->text('signoff_code_gmc')->nullable();
            $table->dateTime('last_email_sent_at')->nullable();

            $table->timestamps();


            $table->foreign('form_instance_id')
                ->references('id')
                ->on('form_instances')
                ->onDelete('cascade');

            $table->foreign('form_submission_id')
                ->references('id')
                ->on('form_submissions')
                ->onDelete('cascade');

            $table->foreign('user_id', 'u_id_foreign')
                ->references('id')
                ->on('users');

            $table->foreign('form_participant_type_id')
                ->references('id')
                ->on('form_participant_types');

            $table->foreign('form_participant_status_id')
                ->references('id')
                ->on('form_participant_statuses');
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