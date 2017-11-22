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

    /**
     * @test
     */
    public function willFailWhenYamlIsNotDecodable()
    {
        $yaml = <<<YAML
foo:
  bar: 1
 foo:
  bar: 1
YAML;

        $this->expectException(ParseException::class);

        $this->parser->parse($yaml);
    }

    /**
     * @test
     */
    public function canLoadValidYaml()
    {
        $yaml = <<<YAML
foo:
  bar: 1
YAML;
        $this->assertSame(1, $this->parser->parse($yaml)->foo->bar);
    }

    /**
     * Check Symfony\Yaml bug
     *
     * @see https://github.com/symfony/symfony/issues/17709
     *
     * @test
     */
    public function canParseNumericMap()
    {
        $yaml   = <<<YAML
map:
  1: one
  2: two
YAML;
        $parser = new  YamlParser();
        $actual = $parser->parse($yaml);
        $this->assertInternalType('object', $actual);
        $this->assertInternalType('object', $actual->map);
        $this->assertTrue(property_exists($actual->map, '1'));
        $this->assertTrue(property_exists($actual->map, '2'));
        $this->assertSame('one', $actual->map->{'1'});
        $this->assertSame('two', $actual->map->{'2'});
    }

    /**
     * Check Symfony\Yaml bug
     *
     * @see https://github.com/symfony/symfony/pull/17711
     *
     * @test
     */
    public function willParseArrayAsArrayAndObjectAsObject()
    {
        $yaml = <<<YAML
array:
  - key: one
  - key: two
YAML;

        $parser = new  YamlParser();
        $actual = $parser->parse($yaml);
        $this->assertInternalType('object', $actual);

        $this->assertInternalType('array', $actual->array);
        $this->assertInternalType('object', $actual->array[0]);
        $this->assertInternalType('object', $actual->array[1]);
        $this->assertSame('one', $actual->array[0]->key);
        $this->assertSame('two', $actual->array[1]->key);
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
            ['text/yml'],
            ['text/yaml'],
            ['application/yml'],
            ['application/yaml'],
            ['application/vn.error+yml']
        ];
    }
}
