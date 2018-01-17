<?php

namespace Nomensa\FormBuilder\Commands;

use Illuminate\Console\Command;
use App\EntryForm;
use Illuminate\Support\Facades\File;

class MakeFormCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'formbuilder:make-form 
                            {code : The URL-friendly alias}
                            {title? : The human-friendly title}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new form schema and database entry';

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
        $title = (string) $this->argument('title');
        $code = (string) $this->argument('code');

        // Check if a form already exists with that code
        $count = EntryForm::where('code', $code)->count();

        if ($count) {

            $this->warn("A database entry for Form with code $code already exists!");

        } else {

            $form = EntryForm::create([
                'title' => $title,
                'code' => $code,
                'slug' => strtolower($code)
            ]);

            if ($form) {
                $this->info("Created database entry for Form");
            }

        }

        // TODO Grab the folder location from app/Form model incase developer has overridden location
        $folder = 'FormBuilder/Forms/' . $code;

        if (!File::exists(app_path($folder))) {
            // Make folder
            File::makeDirectory(app_path($folder));
            $this->info('Created ' . $folder);
        }

        // OPTIONS FILE

        $optionsJSON = '{' . PHP_EOL . '  "rules": {' . PHP_EOL . '    "draft": {},' . PHP_EOL .
            '    "default": {' . PHP_EOL . '    }' . PHP_EOL . '  }' . PHP_EOL . '}';

        // TODO Grab the options file name from app/Form incase developer has overridden filename
        $this->makeFile($folder, 'options.json', $optionsJSON);


        // SCHEMA FILE

        $schemaJSON = '[' . PHP_EOL . '  {' . PHP_EOL . '    "type": "dynamic",' . PHP_EOL . '    "rows": [' . PHP_EOL . '    ]' . PHP_EOL . '  }' . PHP_EOL . ']' . PHP_EOL;

        // TODO Grab the schema file name from app/Form incase developer has overridden filename
        $this->makeFile($folder, 'schema.json', $schemaJSON);

        return 0;
    }

    /**
     * @param string $folder
     * @param string $filename
     * @param string $contents
     */
    private function makeFile($folder, $filename, $contents)
    {
        $fullPath = app_path($folder . '/' . $filename );

        if (File::exists($fullPath)){
            $this->warn($filename . ' already exists');
        } else {
            // Make the schema file
            File::put($fullPath, $contents);
            $this->info('Created ' . $filename );
        }
    }

}
