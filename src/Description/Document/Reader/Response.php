<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Description\Document\Reader;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class Response
{
    /**
     * @var string
     */
    private $contentType;

    /**
     * @var string
     */
    private $content;

    /**
     * Response constructor.
     *
     * @param string $contentType
     * @param string $content
     */
    public function __construct(string $contentType, string $content)
    {
        $this->contentType = $contentType;
        $this->content     = $content;
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}
