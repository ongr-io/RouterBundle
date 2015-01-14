Installation to ONGR
====================

Add to Composer:

.. code-block:: bash

    php composer.phar require "ongr/router-bundle" "~0.1"
..

.. code-block:: php

    // app/AppKernel.php
    
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new ONGR\RouterBundle(),
        );
    }
..
