<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Description\Document\Parser;

use Symfony\Component\Yaml\Parser as SymfonyYamlParser;
use Symfony\Component\Yaml\Yaml;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class YamlParser implements Parser
{
    /**
     * @var SymfonyYamlParser
     */
    private $parser;

    /**
     * Construct the wrapper
     */
    public function __construct()
    {
        $this->parser = new SymfonyYamlParser();
    }

    /**
     * @param string $string
     *
     * @return mixed
     * @throws ParseException
     */
    public function parse(string $string)
    {
        try {
            if (defined('\Symfony\Component\Yaml\Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE')) {
                return $this->parser->parse(
                    $string,
                    Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE | Yaml::PARSE_OBJECT_FOR_MAP
                );
            } else {
                return $this->fixHashMaps($this->parser->parse($string, true, false, false));                
            }        
        } catch (\Throwable $e) {
            throw new ParseException("Failed to parse as YAML", 0, $e);
        }
    }

    /**
     * @see https://github.com/symfony/symfony/pull/17711
     *
     * @param mixed $data
     *
     * @return mixed
     */
    private function fixHashMaps(&$data)
    {
        if (is_array($data)) {
            $shouldBeObject = false;
            $object         = (object)[];
            $index          = 0;
            foreach ($data as $key => &$value) {
                $object->$key = $this->fixHashMaps($value);
                if ($index++ !== $key) {
                    $shouldBeObject = true;
                }
            }
            if ($shouldBeObject) {
                $data = $object;
            }
        }

        return $data;
    }

    /**
     * @param string $contentType
     *
     * @return bool
     */
    public function canParse(string $contentType): bool
    {
        return strpos($contentType, 'yaml') == strlen($contentType) - 4
        || strpos($contentType, 'yml') == strlen($contentType) - 3;
    }
}
