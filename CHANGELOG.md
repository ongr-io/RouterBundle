# CHANGELOG

## v2.0.0 (2017-03-23)

### Breaking Changes
- Drop PHP 5.5 support. Now only PHP >=5.6 are supported.
- Drop Symfony 2.7 support. Now only Symfony >=2.8 are supported.
- Drop Elasticsearch 2.x support. Now only Elasticsearch >=5.0 are supported.

## v1.0.2 (2016-10-03)
- BUGFIX In compiler pass tags are already processed, so it has to be set in the extension. #96

## v1.0.1 (2016-10-03)
- Introduced option to disable router alias. This is because if a user already replaces Symfony router by alias and it conflicts with ONGR alias. #91
- `DocumentUrlGenerator::support()` function cannot throw exception due to `VersatileGeneratorInterface` interface description. Now it returns false instead. #92

## v1.0.0 (2016-04-05)
- Initial release