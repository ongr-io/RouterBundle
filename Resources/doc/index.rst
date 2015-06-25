Router Bundle
=============

Contents:

.. toctree::
    :titlesonly:
    :glob:

    *
    
Introduction
------------

RouterBundle provides URLs transformation functionality to ONGR platform. This can be used for generating nice URLs for products, categories or any document. Beautiful URLs helps SEO and improves user's usability experience.

For example:

Visiting ``/shorts/a-bit-overpriced-shorts.html`` can be configured to open:

.. code-block:: php

    public function productAction(Document\Product $product, $seoKey) {
        // Note, that $product has already been resolved automatically to contain actual document from ElasticsearchBundle.
        // See explanation for $seoKey below.
    }
..

Matched product:

.. code-block:: json

    {
        "name": "Overpriced Shorts",
        ...
        "url": [
            {
                "url": "/summer/a-bit-overpriced-shorts.html", 
                "key": "summer"
            },
            {
                "url": "/shorts/a-bit-overpriced-shorts.html", 
                "key": "shorts"
            }
        ],
        ...
    }
..

SEO keys can be used when rendering URLs. See explanation further in the document.

Configuring
-----------

Document fields
~~~~~~~~~~~~~~~

To enable SEO URLs for selected document type, please implement ``SeoAwareInterface`` and include ``SeoAwareTrait``:

.. code-block:: php

    class TestDocument extends BaseDocument implements SeoAwareInterface
    {
        use SeoAwareTrait; 
        // Above adds public $url and $expiredUrl fields.
    
        // Other fields ...
    }
..

Routing
~~~~~~~

First, routes needs to be defined in ``app/routing.yml`` file.

Example:

.. code-block:: yaml

    # Meta route that is called by OngrRouterBundle.
    ongr_product:
        pattern:  /productDocument/{document}/ # This pattern is ignored and required for compatibility with Symfony.
        defaults: { _controller: ONGRDemoBundle:Product:document }
    
    # Actual route for accessing by document id (not SEO).
    ongr_product_show:
        pattern:  /product/{id}
        defaults: { _controller: ONGRDemoBundle:Product:show }
        requirements:
            page:  \d+
..

Parameters are handled though configuration tree in ``app/config.yml`` file.

.. code-block:: yaml

    ongr_router:
        es_manager: default
        seo_routes:
            product:
                _route: ongr_product
                _controller: ONGRDemoBundle:Product:document
                _default_route: ongr_product_show
                _id_param: documentId
..

Basic parameters for each routing configuration are:
* ``_route`` - Main route for matching and generating SEO URLs.
* ``_controller`` - controller this route will use.
* ``_default_route`` - route which will be used if generating URL using RouterBundle fails. E. g. document has no SEO URLs assigned.
* ``_id_param`` - id field to be passed to ``_default_route``.

Router manager is ElasticsearchBundle manager name which will be used to get documents.

Generating URL
~~~~~~~~~~~~~~

Use Symfony's default generator to produce links to specific documents. For example:

.. code-block:: twig

    <a href="{{ path('ongr_product', {'document': product, '_seo_key': 'summer'} }}">My Product</a>
..

Such template will generate SEO link to the document. Function ``path`` is just an example. You can also choose ``url`` or plain ``$this->get('router')->generate(...)`` generator.

SEO key
~~~~~~~

Conroller action, document URLs and route parameters all have SEO key variable. Since document can have multiple SEO URLs, e. g. for multiple categories or sections, one can differentiate URLs by SEO key. It is optional, but key as a simple string can be passed to the URL generator (like in the example above). If the key is omitted, first document's URL is used.

Alternative action
~~~~~~~~~~~~~~~~~~

It is possible for the document to not have any SEO URLs defined. Therefore, it is recommended to include such action in the controller:

.. code-block:: php

    public function productAction($productId) {
        $product = $this->get('es.manager')->getRepository('product')->find($productId);
        if ($product === null) {
            throw $this->createNotFoundException();
        }

        return $this->render(
            // Your template.
            $this->getProductTemplate($product),
            [
                'product' => $product
            ]
        );
    }
..

SEO generator will use this action and it's associated route to produce URL from ``ongr_product_show`` or similar route defined in ``_default_route``. This route and action will use document ID as it's fallback argument, not SEO URL.

Setup
~~~~~

Setup documentation for the Router bundle is available `<setup.rst>`_.
