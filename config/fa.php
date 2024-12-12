<?php
/**
 *Name: Alauddin F-a
 *Designation: Sr. Software Engineer
 *Date: 11/24/24 12:07 PM
 */
return [
    'global'=>[
        'name'=>env('APP_NAME','Travel Guide CoxBazar'),
        'email'=>env('EMAIL','info@inflack.com'),
    ],
    'api'=>env('SERVER_API_URL','http://localhost:8000/api/v1/'),
    'server'=>env('SERVER_URL','http://localhost:8000'),
    'weather'=>[
        'visual'=>env('WEATHER_VISUAL','HEZQUSQHMA7C557E4JQ564EWX'),
        'glass'=>env('WEATHER_GLASS','5b7f3e8e-b86d-11ef-bb67-0242ac130003-5b7f3ef2-b86d-11ef-bb67-0242ac130003'),
    ]
];
