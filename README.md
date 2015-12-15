# ONGR Router Bundle

Router Bundle allows to define and match URLs for elasticsearch documents.
At url matching phase it additionaly searches for elasticsearch documents with specified url.

This can be used for generating/matching nice URLs for any document.
Beautiful URLs help SEO and improve user's usability experience.


## Documentation

The online documentation of the bundle is [here](Resources/doc/index.md)

For contribution rules take a look at [contribute](Resources/doc/contribute.md) topic.


## Setup the bundle

> This bundle strongly uses [ONGR Elasticsearch Bundle](https://github.com/ongr-io/ElasticsearchBundle).
> We assume that you are familiar with it and it already suits your needs.

#### Step 1: Install Router bundle

Router bundle is installed using [Composer](https://getcomposer.org).

```bash
composer require ongr/router-bundle "~1.0"

```

Enable Router and Elasticsearch bundles in your AppKernel:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new ONGR\ElasticsearchBundle\ONGRElasticsearchBundle(),
    	new ONGR\RouterBundle\ONGRRouterBundle(),
    );
}

```

Yep, that's it, **1 step** installation. All the next steps are demonstration how to setup product document with SEO URLs. So look below how easy it is and adapt it for your needs.


#### Step 2: Add configuration

Add minimal configuration for Router and Elasticsearch bundles.

```yaml

#app/config/config.yml
ongr_elasticsearch:
    analysis:
        analyzer:
            urlAnalyzer:
                type: custom
                tokenizer: keyword
                filter: [lowercase]
    connections:
        default:
            index_name: acme
            analysis:
                analyzer:
                    - urlAnalyzer
    managers:
        default:
            connection: default
            mappings:
                - AppBundle

ongr_router:
    es_manager: default
    seo_routes:
        product: # <- document type here
            _route: ongr_product
            _controller: AppBundle:Product:document
            _default_route: ~
            _id_param: ~
        # seo routes for other types
        # ...

```

At `_controller` you define controller and action for every document type.
`_route` is a name of this route and it can be used at path generation.

`urlAnalyzer` at `ongr_elasticsearch` configuration defines how all url fields are analyzed by Elasticsearch.
Check [Elasticsearch bundle mappings docs](https://github.com/ongr-io/ElasticsearchBundle/blob/master/Resources/doc/mapping.md) for more information about the configuration.


#### Step 3: Create a Product document

Lets create a `Product` class in the `Document` folder. We assume that we have an AppBundle installed.
> Folder name could not be changed, please make sure you put your documents in the right place.

```php
<?php
// src/AppBundle/Document/Product.php

namespace AppBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;
use ONGR\ElasticsearchBundle\Document\AbstractDocument;

/**
 * @ES\Document()
 */
class Product extends AbstractDocument
{
    use SeoAwareTrait; // <- Trait for URLs

    /**
     * @var string
     *
     * @ES\Property(name="title", type="string")
     */
    private $title;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    // ...
}

```

In order to have friendly URLs, the Document has to use `SeoAwareTrait`.


#### Step 4: Create an action for product page

```php
<?php
// src/AppBundle/Controller/ProductController.php

namespace AppBundle\Controller;

use AppBundle\Document\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function documentAction(Product $document, $seoKey)
    {
        return new Response("Product: " . $document->getTitle());
    }
}
```

#### Step 5: Create index and insert demo product

Create an elasticsearch index by running this command in your terminal:

```bash

    app/console ongr:es:index:create

```

> More info about all commands can be found in the [Elasticsearch bundle commands chapter](https://github.com/ongr-io/ElasticsearchBundle/blob/master/Resources/doc/commands.md).

Also, run the following curl command in your terminal to insert a product for this demonstration.

```bash

    curl -XPOST 'http://localhost:9200/acme/product?pretty=1' -d '{"title":"Acoustic Guitar","urls":[{"url":"music/acoustic-guitar/"}],"expired_urls":[]}'
```

#### Step 6: Check if it works

Just visit `/music/acoustic-guitar` page and you should see a title of the product that you inserted in **step 5**.

## License

This bundle is under the MIT license. Please, see the complete license
in the bundle [`LICENSE`](LICENSE) file.
