Kirby's `Pages::factory` method expects an array in the following form:

```php
class TestPage extends Page
{
    public function children()
    {
        $children = Pages::factory([
            [
                'slug' => 'aaa-en',
                'template' => 'testt',
                'model' => 'testt',
                'translations' => [
                    'en' => [
                        'code' => 'en',
                        'slug' => 'aaa-en',
                        'content' => [
                            'title' => 'aaa aaa - en'
                        ]
                    ],
                    'nl' => [
                        'code' => 'nl',
                        'slug' => 'aaa-nl',
                        'content' => [
                            'title' => 'aaa aaa - nl'
                        ]
                    ],
                    'fr' => [
                        'code' => 'fr',
                        'slug' => 'aaa-fr',
                        'content' => [
                            'title' => 'aaa aaa - fr'
                        ]
                    ],
                    'de' => [
                        'code' => 'de',
                        'slug' => 'aaa-de',
                        'content' => [
                            'title' => 'aaa aaa - de'
                        ]
                    ]
                ]
            ],
            [
                'slug' => 'bbb-en',
                'template' => 'testt',
                'model' => 'testt',
                'translations' => [
                    'en' => [
                        'code' => 'en',
                        'slug' => 'bbb-en',
                        'content' => [
                            'title' => 'bbb bbb - en'
                        ]
                    ],
                    'nl' => [
                        'code' => 'nl',
                        'slug' => 'bbb-nl',
                        'content' => [
                            'title' => 'bbb bbb - nl'
                        ]
                    ],
                    'fr' => [
                        'code' => 'fr',
                        'slug' => 'bbb-fr',
                        'content' => [
                            'title' => 'bbb bbb - fr'
                        ]
                    ],
                    'de' => [
                        'code' => 'de',
                        'slug' => 'bbb-de',
                        'content' => [
                            'title' => 'bbb bbb - de'
                        ]
                    ]
                ]
            ]
        ], $this);

        return $children;
    }
}
```
