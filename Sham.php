<?php
require_once 'Sham/Mock.php';
class Sham
{
    const DONTCARE = 0xF00;

    const NO_VALUE_PASSED = 'a40a85fc5fa01e182e023f2191e08ead';

    public static function create($class = null)
    {
        if (! empty($class) && class_exists($class)) {
            $builder = new Sham_Builder();
            return $builder->build($class);
        }

        return new Sham_Mock();
    }

}
