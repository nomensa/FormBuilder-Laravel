<?php

namespace Nomensa\FormBuilder;

use CSSClassFactory;

class Row
{
    /** @var bool */
    public $cloneable = false;

    /** @var string */
    protected $title;
    protected $intro;
    protected $description;
    protected $notes;

    /** @var array - A row contains many columns */
    public $columns = [];

    /**
     * Row constructor.
     *
     * @param array $row_schema Defines a single row of a RowGroup. Can contain 'title', 'intro', 'description', 'notes'
     * @param bool $cloneable
     */
    public function __construct(array $row_schema, bool $cloneable = false)
    {
        $this->cloneable = $cloneable;

        $this->title = $row_schema['title'] ?? '';
        $this->intro = $row_schema['intro'] ?? '';
        $this->description = $row_schema['description'] ?? '';
        $this->notes = $row_schema['notes'] ?? '';
        $this->columns = $row_schema['columns'] ?? null;

        if (isSet($this->columns)) {

            foreach ($this->columns as &$column) {

                $column['row_name'] = $row_schema['row_name'];

                $column = new Column($column,$this->cloneable);

            }
        }
    }


    /**
     * @param \Nomensa\FormBuilder\FormBuilder $form
     * @param null|int $group_index
     *
     * @return string
     */
    public function markup(FormBuilder $formBuilder, $group_index) : string
    {
        $html = '';
        $colsMarkup = '';
        $rowHasVisibleContent = false;

        if (isSet($this->columns)) {
            $colCount = count($this->columns);
            foreach ($this->columns as $column) {
                $colMarkup = $column->markup($formBuilder,$colCount,$group_index);
                $colsMarkup .= $colMarkup->html;

                if ($colMarkup->hasVisibleContent) {
                    $rowHasVisibleContent = true;
                }
            }
        }

        if ($this->title) {
            $html .= MarkerUpper::wrapInTag($this->title, 'h2', ['class' => 'heading']);
        }

        if ($this->intro) {
            $html .= MarkerUpper::wrapInTag($this->intro, 'p');
        }

        if ($this->description) {
            $html .= MarkerUpper::wrapInTag($this->description, 'p');
        }

        if ($rowHasVisibleContent) {
            $html .= $this->wrapInRowTags($colsMarkup);
        } else {
            $html .= $colsMarkup;
        }

        if ($this->notes) {
            $html .= $this->wrapInRowTags($this->notes);
        }

        return $html;
    }


    /**
     * @param string $content
     * @param array $attributes
     *
     * @return string
     */
    private function wrapInRowTags($content, $attributes=[]) : string
    {
        $classBundle = CSSClassFactory::rowClassBundle();
        if (!empty($attributes['class'])) {
            $classBundle->add($attributes['class']);
        }
        $attributes['class'] = $classBundle->__toString();

        return MarkerUpper::wrapInTag($content,'div',$attributes);
    }


    /**
     * @param string $row_name
     * @param string $field_name
     *
     * @return null|array
     */
    public function findFieldOptions($row_name, $field_name)
    {
        foreach ($this->columns as $column) {
            if ($column->row_name == $row_name && $column->field == $field_name) {
                return $column->options;
            }
        }
    }

}
