<?php

include __DIR__ . DS . 'classes' . DS . 'Helper.php';

Kirby::plugin('bvdputte/kirby-vr', [
    'options' => [
        'cache.vrData' => true, // Add cache to plugin
        'cache.timeout' => 1, // in minutes
        'cache.timeout-retry-fail' => 1, // in minutes
        'errorlogname' => 'virtual-reality-errors.log'
    ]
]);
