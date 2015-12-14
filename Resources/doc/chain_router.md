# Chain Router

ONGR Router bundle replaces default Symfony router with **chain router**. It chains Symfony router (default router behaviour) with ONGR router (routes for documents from elasticsearch).

Chain router from Symfony CMF Routing is used for routes handling. More information about chain router can be found at [CMF Routing documentation](http://symfony.com/doc/current/cmf/components/routing/index.html).

### Configuration

Chain routing may be disabled at configuration. In this case only default Symfony router will be used.

Also ONGR and Symfony routers may be removed from the chain.

```yaml
ongr_router:
    enable: true             # Replace default Symfony router with chain router.
    add_symfony_router: true # Add default Symfony router to chain.
    add_ongr_router: true    # Add ONGR router to chain.
    # ...
```

### Custom routers

New routers are added to Chain router, by tagging service with `router` tag.
```yaml
tags:
    - { name: router, priority: 0 }
```

Every router has to implement `RouterInterface`.
Router's priority is optional and by default is 0. Routers with higher priority will be executed first.
