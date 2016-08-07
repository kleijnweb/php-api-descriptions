<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Document\Parser;

use Symfony\Component\Yaml\Parser as SymfonyYamlParser;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class YamlParser implements Parser
{
    /**
     * @var Parser
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
            // Hashmap support is broken in a lot of versions, so disable it and attempt fix afterwards
            $data = $this->parser->parse($string, true, false, false);
        } catch (\Throwable $e) {
            throw new ParseException("Failed to parse as YAML");
        }

        return $this->fixHashMaps($data);
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
