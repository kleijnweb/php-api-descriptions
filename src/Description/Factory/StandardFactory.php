<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\ApiDescriptions\Description\Factory;

use KleijnWeb\ApiDescriptions\Description\Description;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
interface StandardFactory
{
    public function build(string $uri, \stdClass $definition): Description;
}
