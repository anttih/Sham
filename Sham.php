<?php
require_once 'Sham/Mock.php';
require_once 'Sham/Builder.php';
require_once 'Sham/Matcher/Any.php';
class Sham
{
    public static function any()
    {
        return new Sham_Matcher_Any();
    }

    public static function create($class = null)
    {
        if (! empty($class)) {
            $builder = new Sham_Builder();
            return $builder->build($class);
        }

        return new Sham_Mock();
    }
}
