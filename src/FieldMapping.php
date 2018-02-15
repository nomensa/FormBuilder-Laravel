<?php

namespace Nomensa\FormBuilder;

/**
 * Trait FieldMapping
 *
 * @package Nomensa\FormBuilder
 */
trait FieldMapping
{

    /** @var array - Array of Fields and  */
    private $fields = [];

    /** @var array - Key-pair values of field IDs to labels */
    private $fieldMap = [];


    /**
     * Gets fieldMap if its set or makes it if it isnt
     *
     * @return array
     */
    public function getFieldMap() : array
    {
        if (!$this->fieldMap) {
            $this->fieldMap = $this->mapFieldMapFromSchema();
        }
        return $this->fieldMap;
    }


    /**
     * @return array
     */
    private function mapFieldMapFromSchema() : array
    {
        $fields = [];

        foreach ($this->components as $component) {

            if (!empty($component->rowGroup)) {

                $rowsWithColumns = $this->getRowsWithColumns($component->rowGroup);

                foreach ($rowsWithColumns as $key => $column) {
                    $fields[$key] = $column->label;
                }

            }

            if ($component->fieldMappings) {
                foreach ($component->fieldMappings as $id => $label) {
                    $fields[$id] = $label;
                }
            }

        }

        return $fields;
    }


    /**
     * Gets an array of Columns (fields) keys by ID
     *
     * @return array
     */
    public function getFields() : array
    {
        if (!$this->fields) {
            $this->fields = $this->mapFieldsFromSchema();
        }
        return $this->fields;
    }


    /**
     * Gets array of fields (from schema) and their labels
     *
     * @return array
     */
    private function mapFieldsFromSchema() : array
    {
        $fields = [];

        foreach ($this->components as $component) {

            if (!empty($component->rowGroup)) {

                $rowsWithColumns = $this->getRowsWithColumns($component->rowGroup);

                $fields = array_merge($fields, $rowsWithColumns);

            }

        }

        return $fields;
    }


    /**
     * Get Rows with Columns and extract their fields
     *
     * @param \Nomensa\FormBuilder\RowGroup $rowGroup
     *
     * @return array
     */
    private function getRowsWithColumns(RowGroup $rowGroup) : array
    {
        $rowsWithColumns = [];

        foreach ($rowGroup->rows as $row) {

            if(isset($row->columns)){
                $thisRowsWithColumns = $this->extractFieldsFromColumn($row->columns);

                $rowsWithColumns = array_merge($rowsWithColumns, $thisRowsWithColumns);
            } else if ($row->cloneable) {
                // This is not a row, but a cloneable rowGroup Recursion FTW
                $thisRowsWithColumns = $this->getRowsWithColumns($row);

                $rowsWithColumns = array_merge($rowsWithColumns, $thisRowsWithColumns);
            }
        }

        return $rowsWithColumns;
    }


    /**
     * Iterate through the column and return array of fields
     *
     * @param $columns
     *
     * @return array
     */
    private function extractFieldsFromColumn($columns)
    {
        $fields = [];

        foreach ($columns as $column){
            $fields[$column->fieldName] = $column;
        }

        return $fields;
    }

    /**
     * Returns the validation rules for this state
     */
    public function getStateRules($state_id)
    {
        $rules = [];

        $state_id = 'state-'.$state_id;

        foreach ($this->getFields() as $key => $field) {

            if ($field->cloneable) {
                // Don't assume required for cloneable fields, developer should implement their own rules

            } elseif (!isSet($field->states[$state_id]) || $field->states[$state_id] == 'editable') {
                $rules[$key] = 'required';
            }
        }
        return $rules;
    }

    /**
     * Returns the validateable textareas for this state
     */
    public function getEditableTextAreas()
    {
        $textareas = [];
        foreach ($this->getFields() as $key => $field) {
            if ((!isSet($field->states[$this->state_id]) || $field->states[$this->state_id] == 'editable') && $field->type =='textarea') {

                $textareas[] = $key;

            }
        }
        return $textareas;
    }


}
