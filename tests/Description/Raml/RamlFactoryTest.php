<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\ApiDescriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\ApiDescriptions\Tests\Description\Standard\Raml;

use KleijnWeb\ApiDescriptions\Description\Standard\Raml\RamlFactory;
use KleijnWeb\ApiDescriptions\Document\Definition\Validator\DefinitionValidator;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RamlFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function willValidate()
    {
        $validator = $this->getMockBuilder(DefinitionValidator::class)->disableOriginalConstructor()->getMock();
        $validator->expects($this->exactly(1))->method('validate')->with($this->isInstanceOf(\stdClass::class));

        $factory = new RamlFactory($validator);
        $factory->build('/foo', (object)[]);
    }
}
