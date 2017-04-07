# KleijnWeb\PhpApi\Descriptions 
[![Build Status](https://travis-ci.org/kleijnweb/php-api-descriptions.svg?branch=master)](https://travis-ci.org/kleijnweb/php-api-descriptions)
[![Coverage Status](https://coveralls.io/repos/github/kleijnweb/php-api-descriptions/badge.svg?branch=master)](https://coveralls.io/github/kleijnweb/php-api-descriptions?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kleijnweb/php-api-descriptions/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kleijnweb/php-api-descriptions/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/kleijnweb/php-api-descriptions/v/stable)](https://packagist.org/packages/kleijnweb/php-api-descriptions)

A PHP7 library for loading api descriptions and using them to validate PSR7 messages. Enables object hydration in combination with [KleijnWeb\PhpApi\Hydrator](https://github.com/kleijnweb/php-api-hydrator).

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

# Use Case: Building an API

If you want OpenAPI support in combination with Symfony, you should check out [SwaggerBundle](https://github.com/kleijnweb/swagger-bundle). But you can plug `KleijnWeb\PhpApi\Descriptions` into any framework, or even with just a couple of extra libraries.

So let's create a fully fledged app in a single page. For fun we'll also do a little object hydration using `php-api-hydrator`. We'll have to do couple of things (in order):

1. Determine which Operation object a request maps to (routing), or returning 404/405 respectively
2. Parse/coerce the operation parameters from the request
3. Make sure the request is valid for the target operation, and returning a 400 response when it is not
4. Hydrate operation parameters from the request
5. Actually handle the request by passing the object to a controller or command object
6. Return a response with the serialized result

We'll use this super simple API definition, being a stripped version of the standard "petstore example":

```yml
swagger: '2.0'
info: { version: '1.0.0', title: Swagger Petstore (Super Simple) }
paths:
  /pets:
    post:
      parameters: [{ name: pet, in: body, required: true, schema: { $ref: '#/definitions/Pet' } }]
      responses:
        '200':
          description: The created pet
          schema:
            $ref: '#/definitions/Pet'
  /pets/{id}:
    get:
      parameters: [{ name: id, in: path, type: integer, required: true}]
      responses:
        '200':
          description: pet response
          schema:
            $ref: '#/definitions/Pet'
        default:
          description: unexpected error
          schema:
            $ref: '#/definitions/Error'
definitions:
  Pet:
    type: object
    required: [ name ]
    properties:
      id:  { type: integer}
      name: { type: string}
      tag: { type: string}
  Error:
    type: object
    required:  [ code, message ]
    properties:
      code: { type: integer}
      message: { type: string}
```

Below is a crude but fully functional example, annotated with the steps above. It requires the following additional packages:
 
 - `zendframework/zend-diactoros`: A PSR-7 implementation
 - `aura/router`: For matching incoming requests to operations
 - `kleijnweb/php-api-hydrator`: For (de-)hydrating the `Pet` objects
 
```php
<?php
namespace {

    require __DIR__.'/vendor/autoload.php';
}

namespace Dispatcher {

    use Aura\Router\Route;
    use Aura\Router\RouterContainer;
    use Aura\Router\Rule\Allows;
    use Doctrine\Common\Cache\ApcuCache;
    use KleijnWeb\PhpApi\Descriptions\Description\Description;
    use KleijnWeb\PhpApi\Descriptions\Description\Operation;
    use KleijnWeb\PhpApi\Descriptions\Description\Path;
    use KleijnWeb\PhpApi\Descriptions\Description\Repository;
    use KleijnWeb\PhpApi\Descriptions\MessageValidator;
    use KleijnWeb\PhpApi\Descriptions\Request\RequestParameterAssembler;
    use KleijnWeb\PhpApi\Hydrator\ClassNameResolver;
    use KleijnWeb\PhpApi\Hydrator\ObjectHydrator;
    use Psr\Http\Message\ServerRequestInterface;
    use Zend\Diactoros\Response;
    use Zend\Diactoros\Response\JsonResponse;
    use Zend\Diactoros\Response\TextResponse;

    class Dispatcher
    {
        private $repository;
        private $uris;
        private $hydrator;
        private $commands;

        public function __construct(array $uris, array $commands, array $entityNamespaces)
        {
            $this->repository = new Repository(null, new ApcuCache());
            $this->hydrator   = new ObjectHydrator(new ClassNameResolver($entityNamespaces));
            $this->commands   = $commands;
            $this->uris       = $uris;
        }

        public function dispatch(ServerRequestInterface $request): Response
        {
            $routerContainer = new RouterContainer();
            $routerMap       = $routerContainer->getMap();

            /**
             * 1. Determine which Operation object a request maps to (routing), or returning 404/405 respectively
             */
            {
                foreach ($this->uris as $uri) {
                    $description = $this->repository->get($uri);

                    foreach ($description->getPaths() as $path) {
                        foreach ($path->getOperations() as $operation) {
                            $route = (new Route())
                                ->path($path->getPath())
                                ->name($operation->getId())
                                ->allows(strtoupper($operation->getMethod()))
                                ->defaults([
                                    'path'        => $path,
                                    'operation'   => $operation,
                                    'description' => $description,
                                ]);
                            $routerMap->addRoute($route);
                        }
                    }
                }

                $matcher = $routerContainer->getMatcher();

                if (!$route = $matcher->match($request)) {
                    $failedRoute = $matcher->getFailedRoute();
                    switch ($failedRoute->failedRule) {
                        case Allows::class:
                            return new TextResponse('', 405);
                        default:
                            return new TextResponse('', 404);
                    }
                }

                /** @var Description $description */
                $description = $route->attributes['description'];
                /** @var Path $path */
                $path = $route->attributes['path'];
                /** @var Operation $operation */
                $operation = $route->attributes['operation'];
            }

            /**
             * 2. Parse/coerce the operation parameters from the request
             */
            {
                if ($contents = $request->getBody()->getContents()) {
                    $body = json_decode($contents);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return new TextResponse(json_last_error_msg(), 400);
                    }
                    $request = $request->withParsedBody($body);
                }

                foreach ((new RequestParameterAssembler())->getRequestParameters($request,
                    $operation) as $name => $value) {
                    $request = $request->withAttribute($name, $value);
                }
            }


            /**
             * 3. Make sure the request is valid for the target operation, and returning a 400 response when it is not
             */
            {
                $result = (new MessageValidator($description))->validateRequest($request, $path->getPath());

                if (!$result->isValid()) {
                    return new JsonResponse(['errors' => $result->getErrorMessages()], 400);
                }
            }

            /**
             * 4. Hydrate operation parameters from the request
             */
            {
                foreach ($request->getAttributes() as $name => $value) {
                    $request = $request->withAttribute(
                        $name,
                        $this->hydrator->hydrate($value, $operation->getParameter($name)->getSchema())
                    );
                }
            }

            /**
             * 5. Actually handle the request by passing the object to a controller or command object
             */
            {
                $arguments = [];
                foreach ($operation->getParameters() as $parameter) {
                    $arguments[] = $request->getAttribute($parameter->getName());
                }
                $result = call_user_func_array($this->commands[$operation->getId()], $arguments);
            }

            /**
             * 6. Return a response with the serialized result
             */
            return new JsonResponse(
                $this->hydrator->dehydrate($result, $operation->getResponse(200)->getSchema())
            );
        }
    }
}
namespace App {

    use Doctrine\Common\Cache\ApcuCache;
    use Dispatcher\Dispatcher;
    use Zend\Diactoros\Response\SapiEmitter;
    use Zend\Diactoros\ServerRequestFactory;

    class Pet
    {
        private $id;
        private $name;

        public function setId($id)
        {
            $this->id = $id;
        }
    }

    $cache = new ApcuCache();

    $commands = [
        '/pets/{id}:get' => function (int $id) use ($cache) {
            return unserialize($cache->fetch($id));
        },
        '/pets:post'     => function (Pet $pet) use ($cache) {
            $count = $cache->fetch('count');
            $pet->setId($id = $count + 1);
            $cache->save($id, serialize($pet));
            $cache->save('count', $id);

            return $pet;
        },
    ];

    (new SapiEmitter())->emit(
        (new Dispatcher(['petstore.yml'], $commands, ['App']))
            ->dispatch(ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES))
    );
}

```
   
Verify that it works properly by starting a dev server and issuing some cURL requests:

```bash
php -S localhost:1234 test.php
curl -vH "Content-Type: application/json" -d '{"name":"doggie"}' http://localhost:1234/pets
*   Trying 127.0.0.1...
* Connected to localhost (127.0.0.1) port 1234 (#0)
> POST /pets HTTP/1.1
> Host: localhost:1234
> User-Agent: curl/7.47.0
> Accept: */*
> Content-Type: application/json
> Content-Length: 17
> 
* upload completely sent off: 17 out of 17 bytes
< HTTP/1.1 200 OK
< Host: localhost:1234
< Connection: close
< X-Powered-By: PHP/7.0.15-0ubuntu0.16.04.4
< Content-Type: application/json
< Content-Length: 24
< 
* Closing connection 0
{"id":1,"name":"doggie"}

$ curl -vH "Content-Type: application/json" -d '{}' http://localhost:1234/pets
*   Trying 127.0.0.1...
* Connected to localhost (127.0.0.1) port 1234 (#0)
> POST /pets HTTP/1.1
> Host: localhost:1234
> User-Agent: curl/7.47.0
> Accept: */*
> Content-Type: application/json
> Content-Length: 2
> 
* upload completely sent off: 2 out of 2 bytes
< HTTP/1.1 400 Bad Request
< Host: localhost:1234
< Connection: close
< X-Powered-By: PHP/7.0.15-0ubuntu0.16.04.4
< Content-Type: application/json
< Content-Length: 55
< 
* Closing connection 0
{"errors":{"pet.name":"The property name is required"}}

 curl -v  http://localhost:1234/pets/1
*   Trying 127.0.0.1...
* Connected to localhost (127.0.0.1) port 1234 (#0)
> GET /pets/1 HTTP/1.1
> Host: localhost:1234
> User-Agent: curl/7.47.0
> Accept: */*
> 
< HTTP/1.1 200 OK
< Host: localhost:1234
< Connection: close
< X-Powered-By: PHP/7.0.15-0ubuntu0.16.04.4
< Content-Type: application/json
< Content-Length: 24
< 
* Closing connection 0
{"id":1,"name":"doggie"}
```

## Limitations

- Very limited RAML support
- Does not work with form data
- Requires a router to determine the matching path
- If the request has a body, it will have to be deserialized using objects, not as an associative array 
- Requires the response body to be passed unserialized
- Response validation does not validate headers and content-types (yet)

# Contributing

Pull requests are *very* welcome, but the code has to be PSR2 compliant, follow used conventions concerning parameter and return type declarations, and the coverage can not go below **100%**. 

## License

KleijnWeb\PhpApi\Descriptions is made available under the terms of the [LGPL, version 3.0](https://spdx.org/licenses/LGPL-3.0.html#licenseText).
