<?php

namespace Nomensa\FormBuilder;

/**
 * Trait FieldMapping
 *
 * @package Nomensa\FormBuilder
 */
trait FieldMapping
{

    /** @var an array of Fields and  */
    private $fieldMap;


    /**
     * Gets fieldMap if its set or makes it if it isnt
     * @return mixed
     */
    public function getFieldMap()
    {

        /* get an array of fieldnames and labels */

        if (!$this->fieldMap) {
            $this->fieldMap = $this->mapFieldsFromSchema();
        }

        return $this->fieldMap;
    }


    /**
     * Gets array of fields (from schema) and their labels
     *
     * @return array
     */
    private function mapFieldsFromSchema()
    {

        $fields = [];

        foreach ($this->components as $component) {

            if (!empty($component->rowGroup)) {

                $rowsWithColumns = $this->getRowsWithColumns($component->rowGroup);

                $fields = array_merge($fields, $rowsWithColumns);
            }

        };

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
    public function getStateRules()
    {
        $rules = [];

        foreach ($this->getFieldMap() as $key => $field) {
            if ($field->cloneable) {
                // Don't assume required for cloneable fields, developer should implement their own rules

            } else if (!isSet($field->states[$this->state_id]) || $field->states[$this->state_id] == 'editable') {
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
        foreach ($this->getFieldMap() as $key => $field) {
            if ((!isSet($field->states[$this->state_id]) || $field->states[$this->state_id] == 'editable') && $field->type =='textarea') {

                $textareas[] = $key;

            }
        }
        return $textareas;
    }


}
