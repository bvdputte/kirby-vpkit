<?php

namespace bvdputte\kirbyVr;
use Kirby\Cms\Pages;
use Kirby\Cms\Page;
use cache;

class Helper {
    private $fetch;
    private $parent;
    private $template;

    private $cache;
    private $cachedItems;
    private $parentPage;

    // Expects $config["fetch"=>function(){}, "parentUid"=>"some-parent-id", "template"=>"some-template"]
    public function __construct(Array $config)
    {
        $this->cache = kirby()->cache("bvdputte.kirby-vr.vrData");

        $this->parent = $config["parentUid"];
        $this->fetch = $config["fetch"];
        $this->template = $config["template"];

        if ($parentPage = site()->children()->findById($this->parent)) {
            $this->parentPage = $parentPage;
        } else {
            throw new \Exception("Kirby VR: parent `" . $this->parent . "` is not found in the site.");
        }
    }

    // Fetch, migrate and cache
    // Returns an array of the fetched items
    private function fetch()
    {
        if (is_null($this->cachedItems)) {
            $cache = $this->cache;
            // Cache uses the parent as ID
            $cacheData = $cache->get($this->parent);

            // There's nothing in the cache, so let's fetch it
            if ($cacheData === null) {

                $items = ($this->fetch)();

                $cache->set($this->parent, json_encode($items), option("bvdputte.kirby-vr.cache.timeout"));
                return $items;
            }

            $this->cachedItems = json_decode($cacheData, true);
        }

        return $this->cachedItems;
    }

    // Deletes the cached articles
    private function flushCache()
    {
        $cache = $this->cache;
        $cache->remove($this->parent);
    }

    // Updates the cached articles from the API
    public function replenishCache()
    {
        $this->flushCache();
        $data = $this->fetch();

        if ($data != null) {
            return true;
        } else {
            return false;
        }
    }

    // Return an Pages-object of items with specified keys per item (per language)
    public function getPages()
    {
        $vrPages = [];
        foreach ($this->getItemsInCurrentLang() as $vrPageProps) {
            array_push($vrPages, $this->getVirtualPageProps($vrPageProps));
        }

        return Pages::factory($vrPages, $this->parentPage);
    }

    // Return an array of items with specified keys per item (per language)
    private function getItemsInCurrentLang()
    {
        $currentLang = kirby()->language()->code();
        $allItems = $this->fetch();

        if(isset($allItems[$currentLang])) {
            return $allItems[$currentLang];
        } else {
            return [];
        }
    }

    // Builds the necessary props for a given $job to build the virtual page
    private function getVirtualPageProps($vrPageProps)
    {
        return [
            'slug'     => $vrPageProps["slug"],
            'num'      => 0,
            'template' => $this->template,
            'model'    => $this->template,
            'parent'   => $this->parentPage,
            'translations' => $this->getTranslations($vrPageProps["id"]),
            'content' => $vrPageProps['content']
        ];
    }

    // Returns the translations for given id in the fetched items
    private function getTranslations($id)
    {
        $translations = [];
        foreach($this->fetch() as $lang => $localizedItems) {
            foreach($localizedItems as $item) {
                if( $item["id"] == $id) {
                    $config["code"] = $lang;
                    $config["slug"] = $item['slug'];
                    $translations[$lang] = $config;
                }
            }
        }

        return $translations;
    }
}
