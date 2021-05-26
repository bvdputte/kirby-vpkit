# Virtual pages helper kit for multilingual Kirby 3

Opinionated boilerplate helper to make it easier to work with [virtual pages](https://getkirby.com/docs/guide/virtual-pages) in a multilingual kirby 3 setup.

By default virtual pages in a multilingual Kirby environment require some additional, repetitive work. This plugin is an attempt to keep it DRY, and it also takes care of some other niceties such as caching to avoid hammering your backend.

## Installation

- unzip [master.zip](https://github.com/bvdputte/kirby-vpkit/archive/master.zip) as folder `site/plugins/kirby-vpkit` or
- `git submodule add https://github.com/bvdputte/kirby-vpkit.git site/plugins/kirby-vpkit`

## Setup

1. Add a configuration array to your `config.php` that contains:
   1. `fetch`: a closure (function) that returns the array below. This needs to have a `fetch`, `parentUid` and `template`-key. This will be used to generate the virtual pages.
   2. `parentUid`: The ID of the parent page where the virtual pages will be put
   3. `template`: The template you want for the virtual pages
2. Create a [model](https://getkirby.com/docs/guide/templates/page-models) for `template` to `site/models` and reuse the `children()` method to return the virtual pages with this plugin's helper
3. Create a template that matches the given `template`-name to `site/templates`
4. Done. Kirby should now use your added virtual pages as regular pages.

This plugin expects a configuration that returns data in the following form to convert into "virtual Kirby pages", e.g.:

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

_Check the included `demo` folder in this repo for some examples._

## Caching

### Default

By default, each fetch is cached. This is so to avoid latency occuring when fetching data from the endpoint.\
If you don't want this, you can opt out via `'bvdputte.kirby-vpkit.cache' => false` in `config.php`.

Each fetch will be cached by default for 1 minute.\
You can change this with `'bvdputte.kirby-vpkit.cache.timeout' => 60` in `config.php` (The value is in minutes).

### Re-cache when backend is down

When cache is enabled, and your backend is down, this plugin will also continue to serve the already cached data instead of erroring. You can opt out this behaviour via `'bvdputte.kirby-vpkit.recache-on-fail' => false` in `config.php`.\
The timeout for this cache is also 1 minute by default but can set via option: `'bvdputte.kirby-vpkit.recache-on-fail.timeout' => 30` in `config.php` (The value is also in minutes).

In this case, and if you have the [kirby-log plugin](https://github.com/bvdputte/kirby-log) installed, failed attempts will be logged.

### Combine with pages cache

If you want to combine this with pages cache you'll need a strategy to invalidate the reinstantiate cache; a common strategy here is to use a worker via a cronjob. There's also a demo worker included in this repo. Or you could exclude your virtual pages from your page cache.

## Caveats

- Default language; the imported content must **at least exist in the default language of Kirby**.
- When using workers to flush the cache; you must set an `url`-key in `config.php`. Since caching is used, and caches are bound to the url, we have to explicitely add it to make the worker find it. Especially when that worker is envoked over CLI in a cronjob e.g.
- You cannot use the panel to manage these pages as they don't exist on the file system
