# This project is no longer maintained

# KleijnWeb\PhpApi\Descriptions 
[![Build Status](https://travis-ci.org/kleijnweb/php-api-descriptions.svg?branch=master)](https://travis-ci.org/kleijnweb/php-api-descriptions)
[![Coverage Status](https://coveralls.io/repos/github/kleijnweb/php-api-descriptions/badge.svg?branch=master)](https://coveralls.io/github/kleijnweb/php-api-descriptions?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kleijnweb/php-api-descriptions/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kleijnweb/php-api-descriptions/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/kleijnweb/php-api-descriptions/v/stable)](https://packagist.org/packages/kleijnweb/php-api-descriptions)

A PHP library for creating "contract-first" API applications. 

Supported formats:

 - [OpenAPI 2.0](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md) (FKA _Swagger_)
 
Limited:

 - [RAML 1.0](https://github.com/raml-org/raml-spec/blob/master/versions/raml-10/raml-10.md/)<sup>*</sup>
 
<sup>*</sup> *RAML is much more feature-rich and generally elaborate standard than OpenAPI, it will take some time to support the full set. Help is appreciated.*

The aim is to provide full support and interchangeability.
 
# Typical Usage

## Validating Requests And Responses

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

If you're feeling frisky and want to try RAML support:

```php
$validator = new MessageValidator(
    (new Repository())
        ->setFactory(new DescriptionFactory(DescriptionFactory::BUILDER_RAML))
        ->get('tests/definitions/raml/mobile-order-api/api.raml')
);
```

# (De-)Hydration

```php
// $input is deserialized and validated using $inputSchema

$builder   = new ProcessorBuilder(new ClassNameResolver(['Some\Namespace']));
$processor = $builder->build($schema);
$hydrated  = $processor->hydrate($input, $inputSchema);

// Perform business logic, creating $appOutput

$output = $processor->dehydrate($appOutput, $outputSchema);

// Validate output using $outputSchema
```
### NULLs, Undefined And Defaults

The processor will assume hydration input is pre-validated. This implies that when an input object contains a property with a NULL value, it will leave it as is, 
and it may be casted to something other than NULL if the input is invalid (otherwise it will be "hydrated" by `NullProcessor`). 
When dehydrating, the processors will intentionally *not* try to force validity of anything that may have been set to an invalid value by application processing.

The implied flow is thus: `input > deserialization > validation > hydration > business logic > dehydration [> validation] > serialization > output` .

When adhering to this flow, the behavior should be intuitive. There is a separate document detailing the implementation [here](NULLS.md).

### DateTime

The expected in- and output format can be tweaked by configuring the DateTimeProcessor factory with a custom instance of `DateTimeSerializer` (via the builder):
 
 ```php
$builder = new ProcessorBuilder($classNameResolver, new DateTimeSerializer(\DateTime::RFC850));
 ```

By default output is formatted as 'Y-m-d\TH:i:s.uP' (RFC3339 with microseconds). When passed, the first constructor argument will be used instead. 
Input parsing is attempted as follows:

<ol>
  <li>Arguments to the constructor</li>
  <li>RFC3339 with decreasing precision:</li>
  <ol>
    <li>RFC3339 with microseconds</li>
    <li>RFC3339 with milliseconds</li>
    <li>RFC3339</li>
  </ol>
  <li>ISO8601</li>
</ol>

**NOTE**: Formats not starting with `Y-m-d` do not work with Schema::FORMAT_DATE nor `AnyProcessor`. 

### Custom Processors

Class name resolution and DateTime handling can be tweaked by injecting custom instances into the builder, but pretty much all parts of the hydration and dehydration processes are customizable. You can inject custom processors by injecting factories for them into the "processor factory queue". 
All of the processors and their factories are open for extension. Use cases include:

 - Loading objects from a data store
 - Maintaining identity of objects that occur more than once in a structure
 - Custom typed object hydration (eg. using constructors, setters)
 - Custom object creation per type
 - Issuing domain events on object creation
 - Coercing scalar values (eg. interpreting 'false' as FALSE)
 - Pretty much anything else you can think of

Some examples can be found [here](EXAMPLES.md).

### Performance Expectations

On my old Xeon W3570, both hydration and deydration of a an array of 1000 realistic objects (nested objects, arrays) takes about 100ms; 
on average a little short of 1ms per root object.  


# Integration

If you want OpenAPI support in combination with Symfony, you should check out [SwaggerBundle](https://github.com/kleijnweb/swagger-bundle).
 
And there's [PSR-7/PSR-15 Middleware](https://github.com/kleijnweb/php-api-middleware), which behaves pretty much the same but is much more reusable.

## Limitations

- Very limited RAML support
- Does not work with form data
- Requires a router to determine the matching path
- If the request has a body, it will have to be deserialized using objects, not as an associative array 
- Requires the response body to be passed unserialized
- Response validation does not validate headers and content-types (yet)

# Contributing

Pull requests are *very* welcome, but the code has to be PSR2 compliant, follow used conventions concerning parameter and return type declarations, and the coverage can not go down. 

## License

KleijnWeb\PhpApi\Descriptions is made available under the terms of the [LGPL, version 3.0](https://spdx.org/licenses/LGPL-3.0.html#licenseText).
