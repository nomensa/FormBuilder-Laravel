<?php

namespace Nomensa\FormBuilder;

class RowGroup
{
    const CLONEABLE = true;

    /** @var string */
    public $name;

    /** @var bool */
    public $cloneable = false;

    /** @var array - A RowGroup contains many Rows */
    public $rows = [];

    /**
     * RowGroup constructor.
     *
     * @param array $rows
     * @param $name
     * @param bool $cloneable
     */
    public function __construct(array $rows, $name, bool $cloneable = false)
    {
        if (empty($name)) {
            $name = 'dynamic';
        }
        $this->name = $name;

        $this->cloneable = $cloneable;

        $this->rows = $rows;

        // Iterate over array of rows as arrays, converting them to instances of Row
        foreach ($this->rows as $key => &$row) {

            if (isSet($row['cloneable_rowgroup']) && $row['cloneable_rowgroup'] == true) {

                $row = new RowGroup($row['rows'], $row['rowgroup_name'], self::CLONEABLE);

            } else {

                $row['row_name'] = $this->name;

                $row = new Row($row, $this->cloneable);

            }
        }
    }

    /**
     * Iterates over rows, concatenating markup
     *
     * @param \Nomensa\FormBuilder\FormBuilder $form
     *
     * @return string HTML markup
     */
    public function markup(FormBuilder $form)
    {
        $html = '';
        foreach ($this->rows as $row) {
            $html .= $row->markup($form);
        }
        if ($this->cloneable) {
            $html = MarkerUpper::wrapInTag($html,'div',['class'=>'rowGroup-cloneable', 'id'=>$this->name]);
            $html .= '<p><span class="btn btn-link btn-clone-rowGroup" data-target="' . $this->name . '">Add another</span></p>';
        }
        return $html;
    }

}
