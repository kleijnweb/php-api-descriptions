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
class DiamondAType
{
    /**
     * @var string
     */
    private $typeAProperty;

    /**
     * @return string
     */
    public function getTypeAProperty(): string
    {
        return $this->typeAProperty;
    }

    /**
     * @param string $typeAProperty
     *
     * @return DiamondAType
     */
    public function setTypeAProperty(string $typeAProperty): DiamondAType
    {
        $this->typeAProperty = $typeAProperty;

        return $this;
    }
}
