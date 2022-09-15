<?php

namespace bvdputte\kirbyVPKit;
use Kirby\Cms\Pages;
use Kirby\Cms\Page;
use cache;

class VPKit {
    private $template;
    private $parentPage;
    private $fetch_func;

    private $rawItems;
    private $cache=false;
    private $cacheID;

    // Expects $config['fetch_func"=>function(){}, "parentUid"=>"some-parent-id", "template"=>"some-template']
    public function __construct(Array $config)
    {
        $this->template = $config['template'];

        if ($parentPage = site()->children()->find($config['parentUid'])) {
            $this->parentPage = $parentPage;
        } else {
            $errorMessage = "Kirby VR: parent `" . $config['parentUid'] . "` is not found in the site.";
            $this->log($errorMessage);
            throw new \Exception($errorMessage);
        }

        if (option("bvdputte.kirby-vpkit.cache")) {
            $this->cache = kirby()->cache("bvdputte.kirby-vpkit");
            $this->cacheID = $config['parentUid']; // Cache uses the parentUID as ID
        }

        // Fetch raw items array via the supplied closure
        $this->fetch_func = $config['fetch'];
    }

    // Return an Pages-object of items with specified keys per item (per language)
    public function getPages()
    {
        $vrPages = [];
            array_push($vrPages, $this->getVirtualPageProps($vrPageProps));
        }
            foreach ($this->getItemsInDefaultLang() as $vrPageProps) {

        return Pages::factory($vrPages, $this->parentPage);
    }

    // Updates the cached articles from the API
    public function replenishCache()
    {
        $this->flushCache();
        $this->fetchRawItems();
    }

    // Fetch and cache
    // Returns an array of the fetched items
    private function fetchRawItems()
    {
        // This is a potential slow function
        // So, use a local variable for quicker successive traversals
        if (isset($this->rawItems)) {
            return $this->rawItems;
        }

        if ($this->cache) {
            $cache = $this->cache;

            // Nothing in cache; Fetch the items, cache it & return them
            if (is_null($cache->retrieve($this->cacheID))) {
                $items = ($this->fetch_func)();
                $cache->set($this->cacheID, json_encode($items), option("bvdputte.kirby-vpkit.cache.timeout"));

                $this->rawItems = $items;
                return $items;
            }

            // Cache is expired
            if ($cache->expired($this->cacheID)) {
                try {
                    // Re-fetch && re-cache
                    $items = ($this->fetch_func)();
                    $cache->set($this->cacheID, json_encode($items), option("bvdputte.kirby-vpkit.cache.timeout"));

                    $this->rawItems = $items;
                    return $items;
                } catch (\Throwable $e) {
                    if (option("bvdputte.kirby-vpkit.cache.recache-on-fail")) {
                        // Something went wrong, but we have an expired version => re-cache invalid cache
                        $items = json_decode($cache->retrieve($this->cacheID)->value(), true);
                        $cache->set($this->cacheID, json_encode($items), option("bvdputte.kirby-vpkit.cache.recache-on-fail.timeout"));
                        $this->log($e->getMessage());

                        $this->rawItems = $items;
                        return $items;
                    } else {
                        $this->log($e->getMessage());
                        throw new \Exception($e->getMessage());
                    }
                }
            }

            // Items already are in a valid cache
            $this->rawItems = json_decode($cache->get($this->cacheID), true);
            return $this->rawItems;
        }

        return ($this->fetch_func)();
    }

    // Return an array of items with specified keys per item in the default language
    private function getItemsInDefaultLang()
    {
        $defaultLang = kirby()->defaultLanguage()->code();
        $rawItems = $this->fetchRawItems();

        if(isset($rawItems[$defaultLang])) {
            return $rawItems[$defaultLang];
        } else {
            return [];
        }
    }

    // Builds the necessary props for a given $job to build the virtual page
    private function getVirtualPageProps($vrPageProps)
    {
        return [
            'slug'     => $vrPageProps['slug'],
            'num'      => 0,
            'template' => $this->template,
            'model'    => $this->template,
            'parent'   => $this->parentPage,
            'translations' => $this->getTranslations($vrPageProps['id'])
        ];
    }

    // Returns the translations for given id in the fetched items
    private function getTranslations($id)
    {
        $rawItems = $this->fetchRawItems();
        $translations = [];

        foreach($rawItems as $lang => $localizedItems) {
            foreach($localizedItems as $item) {
                if( $item['id'] == $id) {
                    $config['code'] = $lang;
                    $config['slug'] = $item['slug'];
                    $config['content'] = $item['content'];
                    $translations[$lang] = $config;
                }
            }
        }

        return $translations;
    }

    // Deletes the cached articles
    private function flushCache()
    {
        $cache = $this->cache;
        $cache->remove($this->cacheID);
    }

    private function log($message, $loglevel="error")
    {
        if (site()->hasMethod('logger')) {
            site()->logger(option("bvdputte.kirby-vpkit.logname"))->log($message, $loglevel);
        } else {
            if ($loglevel == "error") {
                error_log($message);
            }
        }
    }
}
