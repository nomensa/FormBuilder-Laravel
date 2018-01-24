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
     * @param $rowGroup
     *
     * @return array
     */
    private function getRowsWithColumns($rowGroup)
    {

        $rowsWithColumns = [];

        foreach ($rowGroup->rows as $row){

            if(isset($row->columns)){
                $thisRowsWithColumns = $this->extractFieldsFromColumn($row->columns);

                $rowsWithColumns = array_merge($rowsWithColumns, $thisRowsWithColumns);
            }
        }

        return $rowsWithColumns;
    }


    /**
     * Iterate through the column and return array of fields
     *
     * @param $column
     *
     * @return array
     */
    private function extractFieldsFromColumn($column)
    {

        $fields = [];

        foreach ($column as $field){
            $fields[$field->fieldName] = '<strong>'.$field->label.'</strong>';
        }

        return $fields;

    }
}
