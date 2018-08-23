<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Tests\Hydrator\Types\Polymorphism;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class DiamondBType extends DiamondAType
{
    /**
     * @var string
     */
    private $typeBProperty;

    /**
     * @return string
     */
    public function getTypeBProperty(): string
    {
        return $this->typeBProperty;
    }

    /**
     * @param string $typeBProperty
     *
     * @return DiamondBType
     */
    public function setTypeBProperty(string $typeBProperty): DiamondBType
    {
        $this->typeBProperty = $typeBProperty;
        return $this;
    }
}
