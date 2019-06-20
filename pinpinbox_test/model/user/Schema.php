<?php

namespace Schema;

class user
{
    static
        $act = [
        'none' => 'None',
        'close' => 'Close',
        'open' => 'Open',
    ],
        $act_Default = 'open',
        $birthday_Default = '1900-01-01',
        $creative_name_Length = 32,
        $gender = ['none', 'male', 'female'],
        $gender_Default = 'none',
        $sociallink = [
        'web',
        'facebook',
        'google',
        'twitter',
        'youtube',
        'instagram',
        'pinterest',
        'linkedin',
    ],
        $way = ['none', 'facebook'];
}