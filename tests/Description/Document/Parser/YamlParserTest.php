<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Document\Parser;

use KleijnWeb\PhpApi\Descriptions\Description\Document\Parser\ParseException;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Parser\YamlParser;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class YamlParserTest extends TestCase
{
    /**
     * @var YamlParser
     */
    private $parser;

    protected function setUp()
    {
        $this->parser = new YamlParser();
    }

    public function testWillFailWhenYamlIsNotDecodable()
    {
        $yaml = <<<YAML
foo:
  bar: 1
 foo:
  bar: 1
YAML;

        self::expectException(ParseException::class);

        $this->parser->parse($yaml);
    }

    public function testCanLoadValidYaml()
    {
        $yaml = <<<YAML
foo:
  bar: 1
YAML;
        self::assertSame(1, $this->parser->parse($yaml)->foo->bar);
    }

    /**
     * Check Symfony\Yaml bug
     *
     * @see https://github.com/symfony/symfony/issues/17709
     */
    public function testCanParseNumericMap()
    {
        $yaml   = <<<YAML
map:
  1: one
  2: two
YAML;
        $parser = new  YamlParser();
        $actual = $parser->parse($yaml);
        self::assertInternalType('object', $actual);
        self::assertInternalType('object', $actual->map);
        self::assertTrue(property_exists($actual->map, '1'));
        self::assertTrue(property_exists($actual->map, '2'));
        self::assertSame('one', $actual->map->{'1'});
        self::assertSame('two', $actual->map->{'2'});
    }

    /**
     * Check Symfony\Yaml bug
     *
     * @see https://github.com/symfony/symfony/pull/17711
     */
    public function testWillParseArrayAsArrayAndObjectAsObject()
    {
        $yaml = <<<YAML
array:
  - key: one
  - key: two
YAML;

        $parser = new  YamlParser();
        $actual = $parser->parse($yaml);
        self::assertInternalType('object', $actual);
        self::assertInternalType('array', $actual->array);
        self::assertInternalType('object', $actual->array[0]);
        self::assertInternalType('object', $actual->array[1]);
        self::assertSame('one', $actual->array[0]->key);
        self::assertSame('two', $actual->array[1]->key);
    }

    /**
     * @dataProvider yamlContentTypeProvider
     */
    public function testWillAcceptYamlContentTypes(string $contentType)
    {
        self::assertTrue($this->parser->canParse($contentType));
    }

    public function testWillNotAcceptOtherContentType()
    {
        self::assertFalse($this->parser->canParse('text/html'));
    }

    public function yamlContentTypeProvider()
    {
        return [
            ['text/yml'],
            ['text/yaml'],
            ['application/yml'],
            ['application/yaml'],
            ['application/vn.error+yml']
        ];
    }
}
