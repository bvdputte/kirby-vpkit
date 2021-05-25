<?php

namespace bvdputte\kirbyVr;
use Kirby\Cms\Pages;
use Kirby\Cms\Page;
use cache;

class Helper {
    private $fetch_func;
    private $template;
    private $parentPage;

    private $cache;
    private $cacheID;
    private $cachedItems;

    // Expects $config["fetch_func"=>function(){}, "parentUid"=>"some-parent-id", "template"=>"some-template"]
    public function __construct(Array $config)
    {
        $this->fetch_func = $config["fetch"];
        $this->template = $config["template"];

        if ($parentPage = site()->children()->findById($config["parentUid"])) {
            $this->parentPage = $parentPage;
        } else {
            throw new \Exception("Kirby VR: parent `" . $config["parentUid"] . "` is not found in the site.");
        }

        $this->cache = kirby()->cache("bvdputte.kirby-vr.vrData");
        $this->cacheID = $config["parentUid"]; // Cache uses the parentUID as ID
    }

    // Fetch and cache
    // Returns an array of the fetched items
    private function fetch()
    {
        // Check if we have "memory cache"
        if (is_null($this->cachedItems)) {
            $cache = $this->cache;
            $vrCache = $cache->retrieve($this->cacheID);

            // Nothing in cache; Fetch the items, cache it & return them
            if (is_null($vrCache)) {
                try {
                    $items = ($this->fetch_func)();
                    $cache->set($this->cacheID, json_encode($items), option("bvdputte.kirby-vr.cache.timeout"));
                    // var_dump("Empty cache created from source");

                    return $items;
                } catch (\Throwable $e) {
                    // Something is wrong at the endpoint and we have nothing in cache => exit with Error
                    throw new \Exception($e->getMessage());
                }
            }

            // Cache is expired
            if ($cache->expired($this->cacheID)) {
                // Re-fetch && re-cache
                try {
                    $items = ($this->fetch_func)();
                    $cache->set($this->cacheID, json_encode($items), option("bvdputte.kirby-vr.cache.timeout"));
                    // var_dump("Cache was expired, re-fetched and cached succesfully");

                    return $items;
                } catch (\Throwable $e) {
                    // Something went wrong, but we have an expired version => re-cache invalid cache
                    $items = json_decode($cache->retrieve($this->cacheID)->value(), true);
                    $cache->set($this->cacheID, json_encode($items), option("bvdputte.kirby-vr.cache.timeout-retry-fail"));
                    // var_dump("Cache was expired, re-fetched failed; but re-cached from expired cache succesfully");

                    if (site()->hasMethod('logger')) {
                        site()->logger(option("bvdputte.kirby-vr.errorlogname"))->log($e->getMessage(), 'error');
                    }

                    return $items;
                }
            }

            // Items are in valid cache
            $this->cachedItems = json_decode($cache->get($this->cacheID), true);
        }

        return $this->cachedItems;
    }

    // Deletes the cached articles
    private function flushCache()
    {
        $cache = $this->cache;
        $cache->remove($this->cacheID);
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
