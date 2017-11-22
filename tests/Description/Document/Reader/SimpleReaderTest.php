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
    /**
     * @test
     */
    public function willFailWhenFileDoesNotExist()
    {
        try {
            $loader = new SimpleReader();
            $loader->read('does/not/exist.json');
        } catch (ResourceNotReadableException $e) {
            return;
        }
        $this->fail("Expected ResourceNotReadableException");
    }

    /**
     * @test
     */
    public function willReturnContentTypeYamlIfUriLooksLikeYaml()
    {
        $loader   = new SimpleReader();
        $response = $loader->read('tests/definitions/openapi/petstore.yml');
        $this->assertSame(SimpleReader::CONTENT_TYPE_YAML, $response->getContentType());
    }

    /**
     * @test
     */
    public function willReturnContentTypeYamlIfUriLooksLikeRaml()
    {
        $loader   = new SimpleReader();
        $response = $loader->read('tests/definitions/raml/mobile-order-api/api.raml');
        $this->assertSame(SimpleReader::CONTENT_TYPE_YAML, $response->getContentType());
    }

    /**
     * @test
     */
    public function willReturnContentTypeJsonForEverythingElse()
    {
        $loader   = new SimpleReader();
        $response = $loader->read(__FILE__);
        $this->assertSame(SimpleReader::CONTENT_TYPE_JSON, $response->getContentType());
    }
}
