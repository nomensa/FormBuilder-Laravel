<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEntryFormsTable extends Migration
{

    protected $table = 'entry_forms';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('entry_form_type_id')->unsigned()->default(1);
            $table->string('title');
            $table->string('code'); // RCoA's internal reference code
            $table->string('slug')->unique()->nullable();
            $table->string('abbreviation')->nullable();

            $table->text('description')->nullable();
            $table->integer('form_group_id_temp')->unsigned()->nullable(); // This is dropped after seeding
            $table->integer('entry_form_complexity_id')->unsigned()->nullable();
            $table->integer('entry_form_priority_id')->unsigned()->nullable();

            $table->text('dependencies')->nullable();
            $table->string('reviewed')->nullable();
            $table->string('source')->nullable();

            $table->integer('allow_multiple_submissions')->default(0)->nullable();

            $table->integer('weight')->default(0)->nullable();
            $table->boolean('live')->default(0);

            $table->integer('form_child_id')->nullable();
            $table->boolean('has_supporting_documents')->default(1)->nullable();

            $table->timestamps();

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
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists($this->table);
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
