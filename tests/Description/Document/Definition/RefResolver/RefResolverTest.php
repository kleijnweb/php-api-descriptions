<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Document\Definition\RefResolver;

use KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\Loader\DefinitionLoader;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\RefResolver\InvalidReferenceException;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\RefResolver\RefResolver;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Parser\YamlParser;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RefResolverTest extends TestCase
{
    public function testWillThrowInvalidReferenceException()
    {
        $object   = (object)[
            'type'       => 'object',
            'properties' => (object)[
                'foo' => (object)[
                    '$ref' => '#/does/not/exist'
                ]
            ]
        ];
        $resolver = new RefResolver($object, '/foo');

        self::expectException(InvalidReferenceException::class);
        $resolver->resolve();
    }

    public function testCanResolveResourceSchemaReferences()
    {
        $resolver = $this->factory('petstore.yml');
        $resolver->resolve();

        $schemas        = $resolver->getDefinition()->definitions;
        $propertySchema = $schemas->Pet->properties->category;

        self::assertObjectNotHasAttribute('$ref', $propertySchema);
        self::assertObjectHasAttribute('x-ref-id', $propertySchema);
        self::assertSame('object', $propertySchema->type);
    }

    public function testCanResolveParameterSchemaReferences()
    {
        $resolver        = $this->factory('instagram.yml');
        $pathDefinitions = $resolver->getDefinition()->paths;
        $pathDefinition  = $pathDefinitions->{'/users/{user-id}'};

        self::assertInternalType('array', $pathDefinition->parameters);
        $pathDefinition = $pathDefinitions->{'/users/{user-id}'};

        $resolver->resolve();

        self::assertInternalType('array', $pathDefinition->parameters);
        $argumentPseudoSchema = $pathDefinition->parameters[0];

        self::assertObjectNotHasAttribute('$ref', $argumentPseudoSchema);
        self::assertObjectHasAttribute('in', $argumentPseudoSchema);
        self::assertSame('user-id', $argumentPseudoSchema->name);
    }

    public function testCanResolveReferencesWithSlashed()
    {
        $resolver = $this->factory('partials/slashes.yml');
        self::assertSame('thevalue', $resolver->resolve()->Foo->bar);
    }

    public function testCanResolveExternalReferencesInExample()
    {
        $resolver = $this->factory('composite.yml');
        $document = $resolver->resolve();

        self::assertObjectHasAttribute('schema', $document->responses->Created);

        $response = $document->paths->{'/pet'}->post->responses->{'500'};

        self::assertObjectHasAttribute('description', $response);
    }

    public function testCanUnResolve()
    {
        $resolver = $this->factory('composite.yml');

        $expected = clone $resolver->getDefinition();
        $resolver->resolve();
        $document = $resolver->unresolve();

        self::assertObjectNotHasAttribute('schema', $document->responses->Created);
        self::assertEquals($expected, $document);
    }

    /**
     * @dataProvider externalReferenceProvider
     *
     * @param mixed     $expected
     * @param string    $fileUrl
     * @param string    $uri
     * @param \stdClass $content
     */
    public function testWillProperlyResolveExternalReferences($expected, string $fileUrl, string $uri, \stdClass $content)
    {
        $mockLoader = $this
            ->getMockBuilder(DefinitionLoader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $object   = (object)['$ref' => $uri];
        $resolver = new RefResolver($object, '/somedir/faux', $mockLoader);

        $mockLoader
            ->expects($this->once())
            ->method('load')
            ->with($fileUrl)
            ->willReturn($content);

        $value = $resolver->resolve();

        self::assertSame($expected, $value);
    }

    /**
     * @return array
     */
    public static function externalReferenceProvider()
    {
        return [
            [
                'foo',
                '/somedir/entities.json',
                'entities.json#/definitions/SomeType',
                (object)['definitions' => (object)['SomeType' => 'foo']]
            ],
            [
                'bar',
                '/somedir/entities.yaml',
                'entities.yaml#/definitions/SomeType',
                (object)['definitions' => (object)['SomeType' => 'bar']]
            ],
            [
                'mary',
                '/somedir/entities/had/a/little.yaml',
                'entities/had/a/little.yaml#/Lamb',
                (object)['Lamb' => 'mary']
            ],
            [
                'wow',
                'wss://many:external@references.com:8080/so.yml?much',
                'wss://many:external@references.com:8080/so.yml?much#/flexibility',
                (object)['flexibility' => 'wow']
            ],
            [
                'local',
                '/somedir/local.yml',
                'file://local.yml#/definitions/SomeType',
                (object)['definitions' => (object)['SomeType' => 'local']]
            ]
        ];
    }

    /**
     * @param string $path
     *
     * @return RefResolver
     */
    private function factory($path): RefResolver
    {
        $filePath = "tests/definitions/openapi/$path";
        $contents = file_get_contents($filePath);
        $parser   = new YamlParser();
        /** @var object $object */
        $object   = $parser->parse($contents);
        $resolver = new RefResolver($object, $filePath);

        return $resolver;
    }
}
