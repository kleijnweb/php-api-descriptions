<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Tests\Description\Document\Parser;

use KleijnWeb\ApiDescriptions\Description\Document\Parser\JsonParser;
use KleijnWeb\ApiDescriptions\Description\Document\Parser\ParseException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class JsonParserTest extends \PHPUnit_Framework_TestCase
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
        $this->setExpectedException(ParseException::class);
        $this->parser->parse('NOT VALID JSON');
    }

    /**
     * @test
     */
    public function canLoadValidJson()
    {
        $this->parser->parse(json_encode(['valid' => true]));
    }

    /**
     * @test
     * @dataProvider yamlContentTypeProvider
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

    /**
     * @test
     */
    public function yamlContentTypeProvider()
    {
        return [
            ['text/json'],
            ['application/json'],
            ['application/vn.error+json']
        ];
    }

}