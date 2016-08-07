# KleijnWeb\ApiDescriptions 
[![Build Status](https://travis-ci.org/kleijnweb/php-api-descriptions.svg?branch=master)](https://travis-ci.org/kleijnweb/php-api-descriptions)
[![Coverage Status](https://coveralls.io/repos/github/kleijnweb/php-api-descriptions/badge.svg?branch=master)](https://coveralls.io/github/kleijnweb/php-api-descriptions?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kleijnweb/php-api-descriptions/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kleijnweb/php-api-descriptions/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/kleijnweb/php-api-descriptions/v/stable)](https://packagist.org/packages/kleijnweb/php-api-descriptions)

A PHP7 library for loading api descriptions and using them to validate PSR7 messages. 

Supported formats:

 - [OpenAPI 2.0](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md) (FKA _Swagger_)
 
In the works:

 - [RAML 1.0](https://github.com/raml-org/raml-spec/blob/master/versions/raml-10/raml-10.md/)
 
# Typical Usage

Namespaces omitted for brevity:

```php
$validator = new MessageValidator(
  (new Repository('some/path'))->get('some-service/v1.0.1/swagger.yml')
);
/** @var ServerRequestInterface $request */
$result = $validator->validateRequest($request, $path);

/** @var ResponseInterface $response */
$result = $validator->validateResponse($body, $request, $response, $path);
```

## Limitations

- Does not work with form data
- Requires a router to determine the matching path
- If the request has a body, it will have to be deserialized using objects, not as an associative array 
- Requires the response body to be passed unserialized
- Response validation does not validate headers and content-types (yet)

# Contributing

Pull requests are *very* welcome, but the code has to be PSR2 compliant, follow used conventions concerning parameter and return type declarations, and the coverage can not go below **100%**. 

## License

KleijnWeb\ApiDescriptions is made available under the terms of the [LGPL, version 3.0](https://spdx.org/licenses/LGPL-3.0.html#licenseText).
