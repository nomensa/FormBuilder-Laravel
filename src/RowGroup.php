<?php

namespace Nomensa\FormBuilder;

class RowGroup
{
    const CLONEABLE = true;

    /** @var string */
    public $name;

    /** @var bool */
    public $cloneable = false;

    /** @var array - Can contain both Rows and RowGroups */
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
     * Calls markupClone the required number of times (mostly just once)
     *
     * @param \Nomensa\FormBuilder\FormBuilder $form
     *
     * @return string HTML markup
     */
    public function markup(FormBuilder $formBuilder) : string
    {
        $html = '';

        if ($this->cloneable) {
            // Decide if we need to loop over multiple times
            $rowGroupValueCounts = $formBuilder->getRowGroupValueCount($this->name);

            // Add a hidden field to track to number of active groups
            $html .= '<input type="hidden" id="cloneableRowGroupsCounts-' . $this->name . '" name="cloneableRowGroupsCounts[' . $this->name . ']" value="' . $rowGroupValueCounts . '">';

            // We ALWAYS want at least 1 copy of a cloneable rowGroup, otherwise editing
            // a form attempting to add a group for the first time is impossible.
            $iLimit = max(1,$rowGroupValueCounts);

            for ($group_index = 0; $group_index < $iLimit; $group_index++) {
                $html .= $this->markupClone($formBuilder, $group_index);
            }
        } else {
            $html .= $this->markupClone($formBuilder);
        }

        return $html;
    }

    /**
     * Iterates over rows, concatenating markup
     *
     * @param \Nomensa\FormBuilder\FormBuilder $formBuilder
     * @param null|int $group_index
     *
     * @return string HTML markup
     */
    private function markupClone(FormBuilder $formBuilder, $group_index = null) : string
    {
        $html = '';
        foreach ($this->rows as $row) {
            $html .= $row->markup($formBuilder, $group_index);
        }
        if ($this->cloneable && strlen($html)) {
            $html = MarkerUpper::wrapInTag($html,'div',['class'=>'rowGroup-cloneable', 'id'=>$this->name]);

            if ($formBuilder->displayMode !== 'readonly') {
                $html .= '<p><span class="btn btn-link btn-clone-rowGroup" data-target="' . $this->name . '">Add another</span></p>';
            }

        }
        return $html;
    }


    /**
     * @param string $row_name
     * @param string $field_name
     *
     * @return null|array
     */
    public function findFieldOptions($row_name, $field_name)
    {
        foreach ($this->rows as $row) {
            $options = $row->findFieldOptions($row_name, $field_name);
            if ($options) {
                return $options;
            }
        }
    }

}
