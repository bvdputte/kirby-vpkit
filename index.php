<?php

include __DIR__ . DS . 'classes' . DS . 'Helper.php';

Kirby::plugin('bvdputte/kirby-vr', [
    'options' => [
        'cache' => true, // Add cache to plugin
        'cache.timeout' => 1, // in minutes
        'cache.recache-on-fail' => true,
        'cache.recache-on-fail.timeout' => 1, // in minutes
        'logname' => 'virtual-reality-errors.log'
    ]
]);
