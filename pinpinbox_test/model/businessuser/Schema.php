<?php

namespace Schema;

class businessuser
{
    static
        $account_Length = 64,
        $mode = [
        'personal' => 'Personal',
        'company' => 'Company',
    ],
        $name_Length = 64,
        $enabled = [
        false => 'False',
        true => 'True'
    ];
}