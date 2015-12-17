# SEO keys

Document may have many URLs. SEO key is an identifier of each URL. It may differ in languages, categories, sections and etc.

The key is a simple string. It can be passed to the URL generator.
```html
<a href="{{ path('ongr_product', {'document': product, '_seo_key': 'fr'}) }}">Check this product in French</a>
```

Also this SEO key is passed to controller's action:
`public function documentAction(Product $document, $seoKey)`

SEO key is optional. If it is omitted, first document's URL is used.

### Use cases

Document may have different URL for every language:

```json
{
    "name": "Drum kit",
    "urls": [
        {
            "key": "en",
            "url": "drums/"
        },
        {
            "key": "de",
            "url": "schlagzeug/"
        },
        {
            "key": "fr",
            "url": "batterie/"
        },
    ]
}
```

Example of URLs by sections:

```json
{
    "name": "Violin",
    "urls": [
        {
            "url": "violin/"
        },
        {
            "key": "type",
            "url": "stringed/violin/"
        },
        {
            "key": "material",
            "url": "wooden/violin"
        },
    ]
}
```

In this :violin: case
```
{{ path('ongr_product', {'document': product}) }}
```
generates `violin/` and

```
{{ path('ongr_product', {'document': product, '_seo_key': 'type'}) }}
```
generates `stringed/violin/`.
