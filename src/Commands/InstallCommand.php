<?php

namespace Nomensa\FormBuilder\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'formbuilder:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds schema folder and database migrations to your application';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // TODO Make a Form model in the app folder that extends the package Model

        // TODO Copy package database migrations into database/migrations

        // Make directory
        File::makeDirectory(app_path('FormBuilder/Forms/'));

        return 1;
    }
}
