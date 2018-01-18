<?php

namespace Nomensa\FormBuilder;

use CSSClassFactory;

class Row
{

    /** @var string */
    protected $title;
    protected $intro;
    protected $row_description;
    protected $notes;
    protected $component;
    protected $errors;

    /** @var array - A row contains many columns */
    protected $columns = [];

    public function __construct(array $row_schema)
    {
        $this->title = $row_schema['title'] ?? '';
        $this->intro = $row_schema['intro'] ?? '';
        $this->row_description = $row_schema['row_description'] ?? '';
        $this->notes = $row_schema['notes'] ?? '';
        $this->columns = $row_schema['columns'] ?? null;

        if(isSet($this->columns)){

            foreach ($this->columns as &$column) {

                $column['row_name'] = $row_schema['row_name'];

                $column = new Column($column);

            }
        }
    }


    /**
     * @param FormBuilder/FormBuilder $form
     *
     * @return string
     */
    public function markup(FormBuilder $formBuilder)
    {
        $html = '';
        $colMarkup = '';

        if(isSet($this->columns)){
            $colCount = count($this->columns);
            foreach ($this->columns as $column) {
                $colMarkup .= $column->markup($formBuilder,$colCount);
            }
        }

        /* markup column */

        if ($this->title) {
            $html .= MarkerUpper::wrapInTag($this->title, 'h2', ['class' => 'heading']);
        }

        if ($this->intro) {
            $html .= MarkerUpper::wrapInTag($this->intro, 'p');
        }

        if ($this->row_description) {
            $html .= MarkerUpper::wrapInTag($this->row_description, 'p');
        }

        if ($colMarkup) {
            $html .= $this->wrapInRowTags($colMarkup);
        }
        if ($this->notes) {
            $html .= $this->wrapInRowTags($this->notes);
        }

        return $html;
    }



    /**
     * @param $content
     * @param array $attributes
     *
     * @return string
     */
    private function wrapInRowTags($content, $attributes=[])
    {
        $classBundle = CSSClassFactory::rowClassBundle();
        if (!empty($attributes['class'])) {
            $classBundle->add($attributes['class']);
        }
        $attributes['class'] = $classBundle->__toString();

        return MarkerUpper::wrapInTag($content,'div',$attributes);
    }



    /**
     * Returns field name
     * @return string
     */
    public function getFieldName()
    {
        return $this->rowId.'.'.$this->field;
    }


}
