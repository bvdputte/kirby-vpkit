<?php

// Configs for kirby-vpkit
// Put it in `/site/content/config` and include it like this in `config.php`:
// 'bvdputte.vpkit.config' => require_once __DIR__ . '/virtual-pages.php',

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
                } catch (\Throwable $e) {
                    throw new \Exception("Error fetching data from " . $api . ": " .  $e->getMessage() );
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

                    // UUID (since Kirby 3.8)
                    // Set UUID only in default language
                    if ($lang->code() == kirby()->defaultLanguage()->code()) {
                        $vrPageProps['content']['uuid'] = $item['uuid'];
                    }

                    $vrPagesPerLang[$slug] = $vrPageProps;
                }

                $virtualPages[$lang->code()] = $vrPagesPerLang;
            }

            return $virtualPages;
        }
    ]
];
