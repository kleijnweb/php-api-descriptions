<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Description\Document\Definition\Loader;

use KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\Loader\DefinitionLoader;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\Loader\ResourceNotParsableException;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Parser\Parser;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Reader\Reader;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Reader\Response;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class DefinitionLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function willFailWithoutApplicableParser()
    {
        $loader = new DefinitionLoader(
            $this->getMockForAbstractClass(Reader::class),
            $this->getMockForAbstractClass(Parser::class)
        );
        $this->expectException(ResourceNotParsableException::class);
        $loader->load('resource');
    }

    /**
     * @test
     */
    public function willReturnParsedDefinition()
    {
        $uri         = 'resource';
        $definition  = (object)[];
        $reader      = $this->getMockForAbstractClass(Reader::class);
        $contentType = 'x-foo';
        $content     = (string)rand();
        $reader->expects($this->once())->method('read')->with($uri)->willReturn(new Response($contentType, $content));

        $parser = $this->getMockForAbstractClass(Parser::class);
        $parser->expects($this->once())->method('canParse')->with($contentType)->willReturn(true);
        $parser->expects($this->once())->method('parse')->with($content)->willReturn($definition);

        $loader = new DefinitionLoader($reader, $parser);

        $this->assertSame($definition, $loader->load($uri));
    }
}
