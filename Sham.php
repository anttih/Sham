<?php
require_once 'Sham/Mock.php';
class Sham
{
    const ANY = '8e1bde3c3d303163521522cf1d62f21f';

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
