<?php

namespace Nomensa\FormBuilder;

use Illuminate\Support\ServiceProvider;

class FormBuilderServiceProvider extends ServiceProvider {

    protected $commands = [
        'Nomensa\FormBuilder\Commands\MakeFormCommand',
        'Nomensa\FormBuilder\Commands\InstallCommand'
    ];

    public function register(){
        $this->commands($this->commands);
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

}
