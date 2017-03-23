# How it works

## URL generation

When your document uses [SeoAwareTrait](https://github.com/ongr-io/RouterBundle/blob/master/Document/SeoAwareTrait.php) you have private property called `$url`. It contains the url for specific document,which is used for url generation.

Your document must be defined in the configuration of the bundle, under the node `seo_routes`. There you will define the bundle, controller and action that will be called after the router matches the route in your document.

Don't forget that your document also needs to implement [SeoAwareInterface](https://github.com/ongr-io/RouterBundle/blob/master/Document/SeoAwareInterface.php) in order to be used by the ONGR router.

Let's say we have following document:

```json
{
    ...,
    "url": "/drums"
}
```

In order to generate link to this document (referenced by variable `product`) we would add this line to TWIG template `{{ path(product) }}`. This will result in `drums/`

> The route `ongr_route_product` is generated from `seo_routes` parameter type name, so it's formed by: `ongr_route_<type-name>`.

## URL matching

As shown in [README example](http://docs.ongr.io/RouterBundle) when url matches document, real object representing document is passed to action instead of original string from URI. This is how it works:
- Symfony chain router begins to parse request.
- By default there are two routers registered in the chain: standard Symfony router and ONGR router.
- If Symfony router does not match any routes for given URI it is passed to ONGR router.
- ONGR router queries ES to get document by URI.
- If query returned document it is passed to action, if not - NotFound exception is thrown.

> Note that it is default behaviour and it can be easily changed by adding and removing routers from chain. [More info](http://docs.ongr.io/RouterBundle/chain_router)

## Advanced usage
* [How to add custom router](http://docs.ongr.io/RouterBundle/chain_router)
