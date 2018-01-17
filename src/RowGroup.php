<?php

namespace Nomensa\FormBuilder;

class RowGroup
{
    /** @var string */
    public $name;

    /** @var array - A RowGroup contains many Rows */
    public $rows = [];

    /**
     * RowGroup constructor.
     *
     * @param array $rowGroup_schema
     * @param $name
     */
    public function __construct(array $rows, $name)
    {
        if (empty($name)) {
            $name = 'dynamic';
        }
        $this->name = $name;

        $this->rows = $rows;

        // Iterate over array of rows as arrays, converting them to instances of Row
        foreach ($this->rows as $key => &$row) {
            $row['row_name'] = $this->name . '-' . $key;

            $row = new Row($row);
        }
    }

    /**
     * Iterates over rows, concatenating markup
     *
     * @param Nomensa\FormBuilder\FormBuilder $form
     *
     * @return string HTML markup
     */
    public function markup(FormBuilder $form)
    {
        $html = '';
        foreach ($this->rows as $row) {
            $html .= $row->markup($form);
        }
        return $html;
    }

}
