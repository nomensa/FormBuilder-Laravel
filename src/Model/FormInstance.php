<?php

namespace Nomensa\FormBuilder\Model;

use Illuminate\Database\Eloquent\Model;
use Nomensa\FormBuilder\Exceptions\InvalidSchemaException;
use File;

class FormInstance extends Model
{
    /** @var string Location of form definitions (relative to app folder */
    protected $formDefinitionsFolder = 'FormBuilder/Forms/';

    protected $options_filename = 'options.json';
    protected $schema_filename = 'schema.json';


    /**
     * Load Default schema if the field isnt present in the db
     *
     * @param $assoc bool
     *
     * @return array
     */
    public function getSchema($assoc=true) : array
    {
        $output = !empty($this->schema) ? $this->schema : $this->getSavedSchema();

        if (isset($output)) {

            $output = $this->modifySchema($output);

            $schema = json_decode($output, $assoc);

            if (is_null($schema)) {
                throw new InvalidSchemaException('Invalid JSON in schema file');
            }

            return $schema;

        } else {
            throw new InvalidSchemaException('Schema file was empty');
        }
    }


    /**
     * Load Default options if the field isnt present in the db
     *
     * @return string
     */
    public function getOptions()
    {
        $output = !empty($this->options) ? $this->options : $this->getSavedOptions();

        if (isset($output)) {

            $options = json_decode($output);

            if (is_null($options)) {
                throw new InvalidSchemaException('Invalid JSON in options file');
            }

            return $options;

        } else {
            throw new InvalidSchemaException('Options file was empty');
        }
    }


    /**
     * Get Form Schema saved in the file system
     *
     * @return File
     */
    public function getSavedSchema()
    {
        return File::get($this->getFormDefinitionFolder() . '/' . $this->schema_filename);
    }


    /**
     * Get Form Options saved in the file system
     *
     * @return File
     */
    public function getSavedOptions()
    {
        return File::get($this->getFormDefinitionFolder() . '/' . $this->options_filename);
    }


    /**
     * In most cases this will return '/path/to/laravel/app/FormBuilder/Forms/Your_Form_Name'
     *
     * @return string
     */
    protected function getFormDefinitionFolder() : string
    {
        return app_path(trim($this->formDefinitionsFolder, '/') . '/' . $this->entryForm->code);
    }


    /**
     * This exists so the developer can override it an do string-replace operations
     * on the saved JSON.
     *
     * @param string $jsonSchema
     *
     * @return string
     */
    protected function modifySchema(string $jsonSchema) : string
    {
        return $jsonSchema;
    }

}
