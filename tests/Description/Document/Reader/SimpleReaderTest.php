<?php declare(strict_types=1);
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
        $this->expectException(ResourceNotReadableException::class);
        $reader = new SimpleReader();
        $reader->read('does/not/exist.json');
    }

    /**
     * @test
     */
    public function willReturnContentTypeYamlIfUriLooksLikeYaml()
    {
        $reader   = new SimpleReader();
        $response = $reader->read('tests/definitions/openapi/petstore.yml');
        $this->assertSame(SimpleReader::CONTENT_TYPE_YAML, $response->getContentType());
    }

    /**
     * @test
     */
    public function willReturnContentTypeYamlIfUriLooksLikeRaml()
    {
        $reader   = new SimpleReader();
        $response = $reader->read('tests/definitions/raml/mobile-order-api/api.raml');
        $this->assertSame(SimpleReader::CONTENT_TYPE_YAML, $response->getContentType());
    }

    /**
     * @test
     */
    public function willReturnContentTypeJsonForEverythingElse()
    {
        $reader   = new SimpleReader();
        $response = $reader->read(__FILE__);
        $this->assertSame(SimpleReader::CONTENT_TYPE_JSON, $response->getContentType());
    }
}
