<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Document\Reader;

use KleijnWeb\PhpApi\Descriptions\Description\Document\Reader\ResourceNotReadableException;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Reader\SimpleReader;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SimpleReaderTest extends TestCase
{
    public function testWillFailWhenFileDoesNotExist()
    {
        self::expectException(ResourceNotReadableException::class);

        $loader = new SimpleReader();
        $loader->read('does/not/exist.json');
    }

    public function testWillReturnContentTypeYamlIfUriLooksLikeYaml()
    {
        $loader   = new SimpleReader();
        $response = $loader->read('tests/definitions/openapi/petstore.yml');
        self::assertSame(SimpleReader::CONTENT_TYPE_YAML, $response->getContentType());
    }

    public function testWillReturnContentTypeYamlIfUriLooksLikeRaml()
    {
        $loader   = new SimpleReader();
        $response = $loader->read('tests/definitions/raml/mobile-order-api/api.raml');
        self::assertSame(SimpleReader::CONTENT_TYPE_YAML, $response->getContentType());
    }

    public function testWillReturnContentTypeJsonForEverythingElse()
    {
        $loader   = new SimpleReader();
        $response = $loader->read(__FILE__);
        self::assertSame(SimpleReader::CONTENT_TYPE_JSON, $response->getContentType());
    }
}
