<?php

return [
    'PHOTO' => [
        'GENRE' => [
            1 => 'Landscape',
            2 => 'Animal',
            3 => 'Portrait',
            4 => 'SnapShot',
            5 => 'Live Composite',
            6 => 'PinHole/Film',
        ],
        'GENRE_FILE_URL' => [
            1 => 'photo/landscape',
            2 => 'photo/animal',
            3 => 'photo/portrait',
            4 => 'photo/others/snapshot',
            5 => 'photo/others/livecomposite',
            6 => 'photo/others/pinfilm',
        ],
    ],
    'PHOTO_AGGREGATION' => [
        //TODO: enumで置き換える
        'TYPE' => [
            'DAILY'   => 1,
            'WEEKLY'  => 2,
            'MONTHLY' => 3,
        ],
        'STATUS' => [
            'INCOMPLETE' => 0,
            'COMPLETE'   => 1,
        ],
    ],
    'MAIL' => env('MY_EMAIL', 'wadakatukoyo330@gmail.com'),
];
