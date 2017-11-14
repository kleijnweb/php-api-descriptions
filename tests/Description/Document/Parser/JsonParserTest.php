<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Document\Parser;

use KleijnWeb\PhpApi\Descriptions\Description\Document\Parser\JsonParser;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Parser\ParseException;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class JsonParserTest extends TestCase
{
    /**
     * @var JsonParser
     */
    private $parser;

    protected function setUp()
    {
        $this->parser = new JsonParser();
    }

    public function testWillFailWhenJsonIsNotDecodable()
    {
        self::expectException(ParseException::class);
        $this->parser->parse('NOT VALID JSON');
    }

    public function testCanLoadValidJson()
    {
        $object = $this->parser->parse(json_encode(['valid' => true]));

        self::assertObjectHasAttribute('valid', $object);
        self::assertTrue($object->valid);
    }

    /**
     * @dataProvider jsonContentTypeProvider
     */
    public function testWillAcceptJsonContentTypes(string $contentType)
    {
        self::assertTrue($this->parser->canParse($contentType));
    }

    public function testWillNotAcceptOtherContentType()
    {
        self::assertFalse($this->parser->canParse('text/html'));
    }

    public function jsonContentTypeProvider()
    {
        return [
            ['text/json'],
            ['application/json'],
            ['application/vn.error+json']
        ];
    }

}
