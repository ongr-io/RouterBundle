## Installation to ONGR

Add to Composer:

```bash
php composer.phar require "ongr/router-bundle" "~0.1"
```

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new ONGR\RouterBundle(),
    );
}
```
