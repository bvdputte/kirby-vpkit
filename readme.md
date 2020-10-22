# Virtual pages helper for multilingual Kirby 3

Opinionated boilerplate helper to make it easier to work with virtual pages in a multilingual kirby 3 setup.

## Installation

- unzip [master.zip](https://github.com/bvdputte/kirby-virtual-reality/archive/master.zip) as folder `site/plugins/kirby-virtual-reality` or
- `git submodule add https://github.com/bvdputte/kirby-virtual-reality.git site/plugins/kirby-virtual-reality`

## How does it work?

This plugin expects a configuration that returns data in the following form:

```php
$virtualPages = [
    "en" => [
        [
            "id" => "some-uuid", // Will be used to figure out which translations belong to eachother
            "slug" => "slug-in-english",
            "content" => [
                "title" => "Title is required",
                "somefield" => "Other fields are added like this"
            ]
        ]
    ],
    "nl" => [
        [
            "id" => "some-uuid", // Will be used to figure out which translations belong to eachother
            "slug" => "slug-in-nederlands",
            "content" => [
                "title" => "Titel is verplicht",
                "somefield" => "Andere velden kunnen zo toegevoegd worden."
            ]
        ]
    ]
];
```

1. Add a configuration array to your `config.php`
  1. `fetch`: a closure (function) that returns the above array. This will be used to generate the virtual pages.
  2. `parentUid`: The ID of the parent page where the virtual pages will be put
  3. `template`: The template you want for the virtual pages
2. Create a [model](https://getkirby.com/docs/guide/templates/page-models) for `template` to `site/models` and reuse the `children()` method to return the virtual pages with this plugin's helper
3. Create a template that matches the given `template`-name to `site/templates`
4. Done. Kirby should now use your added virtual pages as regular pages.

Check the included `demo` folder in this plugin for demo files.

## Caching

By default, each fetch is cached. This is so to avoid latency occuring when fetching data from the endpoint.

Each fetch will be cached for 1 minute. You can change this with `"bvdputte.kirby-vr.cache.timeout" => 60` in `config.php`.
⚠️ The value is in minutes.

Another common strategy is to clear the cache with a worker via a cronjob. There's a demo worker included in this repo.
⚠️ Be sure to set `bvdputte.kirby-vr.cache.timeout` a really high number.

## Caveats

- Default language; the imported content must **at least exist in the default language of Kirby**.
- When using workers to flush the cache; you must set an `url`-key in `config.php`. Since caching is used, and caches are bound to the url, we have to explicitely add it to make the worker find it. Especially when that worker is envoked over CLI in a cronjob e.g.
- You cannot use the panel to manage these pages as they don't exist on the file system
