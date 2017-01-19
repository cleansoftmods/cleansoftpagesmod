<?php

return [
    /**
     * Public routes
     */
    'public_routes' => [
        'get' => [
            [
                '/{slug?}',
                [
                    'as' => 'front.web.resolve-pages.get',
                    'uses' => 'WebEd\Base\Pages\Http\Controllers\Front\ResolvePagesController@handle',
                    'where' => [
                        'slug' => '[-A-Za-z0-9]+'
                    ]
                ]
            ]
        ]
    ],
];
