# ONGR Router Bundle

Router Bundle allows to define and match URLs for elasticsearch documents.
At url matching phase it additionaly searches for elasticsearch documents with specified url.

This can be used for generating/matching nice URLs for any document.
Beautiful URLs help SEO and improve user's usability experience.

If you have any questions, don't hesitate to ask them on [![Join the chat at https://gitter.im/ongr-io/support](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/ongr-io/support)
 chat, or just come to say Hi ;).
 
[![Build Status](https://travis-ci.org/ongr-io/RouterBundle.svg?branch=master)](https://travis-ci.org/ongr-io/RouterBundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ongr-io/RouterBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ongr-io/RouterBundle/?branch=master)
[![Code Climate](https://codeclimate.com/github/ongr-io/RouterBundle/badges/gpa.svg)](https://codeclimate.com/github/ongr-io/RouterBundle)
[![Coverage Status](https://coveralls.io/repos/ongr-io/RouterBundle/badge.svg?branch=master&service=github)](https://coveralls.io/github/ongr-io/RouterBundle?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/61b9cee8-6150-4566-b04b-7b53d0a790fd/mini.png)](https://insight.sensiolabs.com/projects/61b9cee8-6150-4566-b04b-7b53d0a790fd)
[![Codacy Badge](https://api.codacy.com/project/badge/grade/eb45b9135f974c67ae1bbecbed1115f2)](https://www.codacy.com/app/ongr/RouterBundle)
[![Latest Stable Version](https://poser.pugx.org/ongr/router-bundle/v/stable)](https://packagist.org/packages/ongr/router-bundle)
[![Total Downloads](https://poser.pugx.org/ongr/router-bundle/downloads)](https://packagist.org/packages/ongr/router-bundle)
[![Latest Unstable Version](https://poser.pugx.org/ongr/router-bundle/v/unstable)](https://packagist.org/packages/ongr/router-bundle)
[![License](https://poser.pugx.org/ongr/router-bundle/license)](https://packagist.org/packages/ongr/router-bundle)

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
        product: AppBundle:Product:document
        # ...

```

> WARNING: If SeoAwareTrait is used you must implement `urlAnalyzer` analyzer, otherwise there will be a fatal error on index create.

At `_controller` you define controller and action for every document type.
`_route` is a name of this route and it can be used at path generation.

`urlAnalyzer` at `ongr_elasticsearch` configuration defines how all url fields are analyzed by Elasticsearch.

Check [Elasticsearch bundle mappings docs](https://github.com/ongr-io/ElasticsearchBundle/blob/master/Resources/doc/mapping.md) for more information about the configuration.


## Usage example

#### Step 1: Create a Product document

Lets create a `Product` document class. We assume that we have an AppBundle installed.

```php
<?php
// src/AppBundle/Document/Product.php

namespace AppBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;
use ONGR\ElasticsearchBundle\Document\DocumentTrait;

/**
 * @ES\Document()
 */
class Product
{
    use DocumentTrait;
    use SeoAwareTrait; // <- Trait for URL's

    /**
     * @ES\Property(type="string")
     */
    public $title;

    // ...
}

```

In favor to support friendly URLs, the bundle provides `SeoAwareTrait` with predefined mapping.


#### Step 2: Create controller and action for the product page

```php
<?php
// src/AppBundle/Controller/ProductController.php

namespace AppBundle\Controller;

use AppBundle\Document\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function documentAction(Product $document)
    {
        return new Response("Product: " . $document->title);
    }
}
```

#### Step 3: Create index and insert demo product

Create an elasticsearch index by running this command in your terminal:

```bash

    app/console ongr:es:index:create
    
```

> More info about all commands can be found in the [Elasticsearch bundle commands chapter](https://github.com/ongr-io/ElasticsearchBundle/blob/master/Resources/doc/commands.md).

Also, run the following curl command in your terminal to insert a product for this demonstration.

```bash

    curl -XPOST 'http://localhost:9200/acme/product?pretty=1' -d '{"title":"Acoustic Guitar", "url":"/music/electric-guitar"}'
    
```

#### Step 4: Check if it works

Just visit `/music/electric-guitar` page and you should see a title of the product that you inserted in **step 3**.


## License

This bundle is under the MIT license. Please, see the complete license
in the bundle [`LICENSE`](LICENSE) file.
