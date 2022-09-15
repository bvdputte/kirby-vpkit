<?php

include __DIR__ . DS . 'classes' . DS . 'VPKit.php';
include __DIR__ . DS . 'classes' . DS . 'Register.php';

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('bvdputte/kirby-vpkit', [
    'options' => [
        'cache' => true, // Add cache to plugin
        'cache.timeout' => 1, // in minutes
        'cache.recache-on-fail' => true,
        'cache.recache-on-fail.timeout' => 1, // in minutes
        'logname' => 'vpkit-errors.log',
        'register' => new bvdputte\kirbyVPKit\Register
    ]
]);
