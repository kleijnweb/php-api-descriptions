<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\PhpApi\Descriptions package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\PhpApi\Descriptions\Tests\Description;

use Doctrine\Common\Cache\ArrayCache;
use KleijnWeb\PhpApi\Descriptions\Description\Description;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Document;
use KleijnWeb\PhpApi\Descriptions\Description\Document\Reader\ResourceNotReadableException;
use KleijnWeb\PhpApi\Descriptions\Description\Repository;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RepositoryTest extends TestCase
{
    /**
     * @var Repository
     */
    private $repository;

    protected function setUp()
    {
        $this->repository = new Repository();
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function willFailWhenKeyIsEmpty()
    {
        $this->repository->get('');
    }

    /**
     * @test
     */
    public function willFailWhenPathDoesNotExist()
    {
        $this->expectException(ResourceNotReadableException::class);
        $this->repository->get('/this/is/total/bogus');
    }

    /**
     * @test
     */
    public function gettingDocumentThatDoestExistWillConstructIt()
    {
        $document = $this->repository->get('tests/definitions/openapi/petstore.yml');
        $this->assertInstanceOf(Description::class, $document);
    }

    /**
     * @test
     */
    public function willCache()
    {
        $path       = 'tests/definitions/openapi/petstore.yml';
        $cache      = $this->getMockBuilder(ArrayCache::class)->disableOriginalConstructor()->getMock();
        $repository = new Repository(null, $cache);

        $cache->expects($this->exactly(1))->method('fetch')->with($path);
        $cache->expects($this->exactly(1))->method('save')->with($path, $this->isType('object'));
        $repository->get($path);
    }

    /**
     * @test
     */
    public function willFetchFromCache()
    {
        $path        = 'tests/definitions/openapi/petstore.yml';
        $cache       = $this->getMockBuilder(ArrayCache::class)->disableOriginalConstructor()->getMock();
        $document    = $this->getMockBuilder(Document::class)->disableOriginalConstructor()->getMock();
        $description = new Description([], [], '', [], [], $document);

        $repository = new Repository(null, $cache);

        $cache->expects($this->exactly(1))->method('fetch')->with($path)->willReturn($description);
        $this->assertInstanceOf(Description::class, $repository->get($path));
    }

    /**
     * @test
     */
    public function canUsePathPrefix()
    {
        $this->repository = new Repository('tests/definitions/');
        $document         = $this->repository->get('openapi/petstore.yml');

        $this->assertInstanceOf(Description::class, $document);
    }
}
