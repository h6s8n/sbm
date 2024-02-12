<?php

return [
	'mode'                  => 'utf-8',
	'format'                => 'A4',
	'author'                => '',
	'subject'               => '',
	'keywords'              => '',
	'creator'               => 'Laravel Pdf',
	'display_mode'          => 'fullpage',
	'tempDir'               => base_path('../temp/'),
    'font_path' => '/var/www/vhosts/sandbox.sbm24.net/httpdocs/assets/adminui/assets/font/ttf',
    'font_data' => [
        'fa' => [
            'R'  => 'IRANSansWeb(FaNum).ttf',
            'B'  => 'IRANSansWeb(FaNum).ttf',
            'useOTL' => 0xFF,
            'useKashida' => 75,
        ],
        'en' => [
            'R'  => 'Arial',
            'B'  => 'Arial',
        ]
    ]
];
