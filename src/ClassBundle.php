<?php

namespace Nomensa\FormBuilder;

class ClassBundle
{
    // Array of strings
    protected $classes = [];

    /**
     * @param string|array $classes
     */
    public function __construct($classes=[])
    {
        if (is_array($classes)) {
            $this->classes = $classes;
        } else if (is_string($classes)) {
            $this->classes = array_filter(explode(' ', trim($classes)));
        }

    }

    /**
     * Adds a class to the list, moves to end if already exists
     *
     * @param string|array $class
     *
     * @return void
     */
    public function add($classes)
    {
        if ($classes == null) {
            return null;
        }

        if (is_string($classes)) {
            $classes = explode(' ', trim($classes));
        }

        foreach ($classes as $class) {
            $this->addSingleClassName($class);
        }
    }

    /**
     * Adds a class to the list, moves to end if already exists
     *
     * @param string $class
     *
     * @return void
     */
    private function addSingleClassName($class)
    {
        $pos = array_search($class,$this->classes);
        if ($pos) {
            array_splice($this->classes,$pos,1);
        }
        // Append the class to the end of the stack (the developer may want to promote the class to end of the string
        $this->classes[] = $class;
    }

    /**
     * Magic method to allow us to call print operations on the instance as if it were a string
     */
    public function __toString()
    {
        return implode(' ', $this->classes);
    }

    public function getArray()
    {
        return $this->classes;
    }

}
