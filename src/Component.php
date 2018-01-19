<?php

namespace Nomensa\FormBuilder;

use View;

class Component
{
    /** @var */
    public $type;

    /** @var Instance of \Nomensa\FormBuilder\RowGroup */
    public $rowGroup;

    /** @var string Optional name */
    public $rowGroupName;

    public function __construct(array $component_schema)
    {
        $this->type = $component_schema['type'];
        $this->errors = []; // TODO Errors pulled in from request when in update

        $this->rowGroupName = $component_schema['validationGroup'] ?? null;

        if (isSet($component_schema['rows']) ) {
            $this->rowGroup = new RowGroup($component_schema['rows'], $this->rowGroupName);
        }

    }

    /**
     * @param \Nomensa\FormBuilder\FormBuilder $formBuilder - The containing form
     *
     * @return string
     */
    public function markup(FormBuilder $formBuilder)
    {

        if ($this->type == "dynamic") {
            return $this->rowGroup->markup($formBuilder);
        } else {
            $data = array_merge( [
                'formBuilder' => $formBuilder
            ], $formBuilder->viewData );
            $view = View::make('components.formbuilder.' . $this->type, $data);
            return $view->render();
        }

    }

}
