<?php
require_once 'Mocker/Mock.php';
class Mocker
{
    const DONTCARE = 0xF00;

    public static function create($class = null)
    {
        if (! empty($class) && class_exists($class)) {
            $builder = new Mocker_Builder();
            return $builder->build($class);
        }

        return new Mocker_Mock();
    }

}
