<?php

// Put this in /site/models

class VirtualPagePage extends Page
{
    public function children()
    {
        $VrHelper = new bvdputte\kirbyVr\Helper(option('bvdputte.kirby-vr.config')['virtual-pages-demo']);

        return $VrHelper->getPages();
    }
}
