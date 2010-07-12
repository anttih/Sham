<?php
require_once 'Sham/Mock.php';
class Sham
{
    const ANY = '8e1bde3c3d303163521522cf1d62f21f';

    public static function create($class = null)
    {
        if (! empty($class)) {
            $builder = new Sham_Builder();
            return $builder->build($class);
        }

        return new Sham_Mock();
    }

}
