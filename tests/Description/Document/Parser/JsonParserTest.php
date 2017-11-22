<?php declare(strict_types=1);
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

    /**
     * @test
     */
    public function willFailWhenJsonIsNotDecodable()
    {
        $this->expectException(ParseException::class);
        $this->parser->parse('NOT VALID JSON');
    }

    /**
     * @test
     */
    public function canLoadValidJson()
    {
        $data = (object)['valid' => true];
        $this->assertEquals($data, $this->parser->parse(json_encode($data)));
    }

    /**
     * @test
     * @dataProvider jsonContentTypeProvider
     */
    public function willAcceptYamlContentTypes(string $contentType)
    {
        $this->assertTrue($this->parser->canParse($contentType));
    }

    /**
     * @test
     */
    public function willNotAcceptOtherContentType()
    {
        $this->assertFalse($this->parser->canParse('text/html'));
    }

    public function jsonContentTypeProvider()
    {
        return [
            ['text/json'],
            ['application/json'],
            ['application/vn.error+json'],
        ];
    }

}
