<?php

// Configs for kirby-vr
// Put it in `/site/content/config` and include it like this in `config.php`:
// 'bvdputte.kirby-vr.config' => require_once __DIR__ . '/virtual-pages.php',

return [
    "virtual-pages-demo" => [
        'parentUid' => 'some-parent', // All virtual pages will be hung under `/some-parent`. This page should exist in `content`.
        'template' => 'virtual-page',
        'fetch' => function() {
            $api = "https://some-json-api.com";

            $virtualPages = [];
            /* Virtual pages needs following structure:
                $virtualPages = [
                    "en" => [
                        [
                            "id" => "some-uuid",
                            "slug" => "slug",
                            "content" => [
                                "title" => "Title is required",
                                "somefield" => "Other fields are added like this"
                            ]
                        ]
                    ]
                ];
            */
            foreach (kirby()->languages() as $lang) {

                try {
                    $apiData = file_get_contents($api);
                } catch (Exception $e) {
                    error_log("Error fetching data from " . $api . ": " .  $e->getMessage() );
                }

                $vrPagesPerLang = [];
                foreach ($apiData as $item) {
                    $vrPageProps = [
                        'id' => $item["uuid"],
                        'slug' => $item["slug"],
                        'content' => [
                            "title" => $item["title"],
                            "somefield" => $item["somefield"],
                            //...
                        ]
                    ];

                    $vrPagesPerLang[$slug] = $vrPageProps;
                }

                $virtualPages[$lang->code()] = $vrPagesPerLang;
            }

            return $virtualPages;
        }
    ]
];
