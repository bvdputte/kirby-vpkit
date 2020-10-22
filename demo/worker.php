<?php

// Bootstrap Kirby (from with the plugin's folder)
$siteRoot = dirname(__FILE__) . "/../../../../";
require $siteRoot.'/kirby/bootstrap.php';

// Instantiate Kirby
$kirby = new Kirby([
    // Override options from `/site/config.php` here:
    'options' => [
        'debug' => true,
        'url' => 'http://mywebsite.com', // Necessary, since caches are prefixed with the URL
    ],
]);

// Replenish the jobs cache
$hrTechApi = new bvdputte\kirbyVr\Helper(option("bvdputte.kirby-vr.config")["virtual-pages-demo"]);
$hrTechApi->replenishCache();

// Also Flush the pages cache
kirby()->cache('pages')->flush();

exit();
