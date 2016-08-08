<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description\Document\Definition\Loader;

use KleijnWeb\PhpApi\Descriptions\Description\Document\Parser\JsonParser;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Parser\Parser;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Parser\YamlParser;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Reader\Reader;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Reader\SimpleReader;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class DefinitionLoader
{
    /**
     * @var Parser[]
     */
    private $parsers;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * DefinitionLoader constructor.
     *
     * @param Reader   $reader
     * @param Parser[] $parsers
     */
    public function __construct(Reader $reader = null, Parser ...$parsers)
    {
        $this->reader  = $reader ?: new SimpleReader();
        $this->parsers = $parsers ?: [new JsonParser(), new YamlParser()];
    }

    /**
     * @param string $uri
     *
     * @return \stdClass
     */
    public function load(string $uri): \stdClass
    {
        $response = $this->reader->read($uri);

        foreach ($this->parsers as $parser) {
            if (!$parser->canParse($response->getContentType())) {
                continue;
            }

            return $parser->parse($response->getContent());
        }

        throw new ResourceNotParsableException("No parser for response of '$uri''");
    }
}
