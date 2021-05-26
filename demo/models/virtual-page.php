<?php

// Put this in /site/models
use bvdputte\kirbyVPKit\VPKit;

class VirtualPagePage extends Page
{
    public function children()
    {
        $myDemoVPKit = new VPKit(option('bvdputte.kirby-vpkit.config')['virtual-pages-demo']);

        return $myDemoVPKit->getPages();
    }
}
