<?php

namespace Lib\Backstage\Html;

class Input
{
    static function number(array $attr)
    {
        return '<input type="number" ' . array2htmlattr($attr) . '>';
    }

    static function url(array $attr)
    {
        return '<input type="url" ' . array2htmlattr($attr) . '>';
    }
}