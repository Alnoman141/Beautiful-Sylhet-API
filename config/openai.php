<?php
/**
 *Name: Alauddin F-a
 *Designation: Sr. Software Engineer
 *Date: 27-Mar-25 12:29 PM
 */
return [
    'api_key' => env('OPENAI_API_KEY',
        //'sk-svcacct-qM2jqzjZ8T_N2k3YbK8ASNQ84RcA9vt0YRSKMVnlkcfol-dDZ-9Fw73WCgOd0diT3BlbkFJI2hFMlZHPdS_dw2fOZwTVlGq9S6fxw1GM0Cs0S38UsTDjTtolqKOUystDSzf1fwA',
        //'AIzaSyAfafD_51W9GVFuTia5NzHB0fTn8Y5-TNg' //alauddin020
        'AIzaSyA77SgI4D_p_zInHUYX5dqfGYEFh6OfsLM', //alal.inflack
    ),
    'daily_limit'=>env('OPENAI_DAILY_LIMIT', 3),
];
