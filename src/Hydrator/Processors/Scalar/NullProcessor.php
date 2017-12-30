<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions\Hydrator package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\Descriptions\Hydrator\Processors\Scalar;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class NullProcessor extends ScalarProcessor
{
    /**
     * @param $value
     * @return null
     */
    public function hydrate($value)
    {
        return null;
    }
}
