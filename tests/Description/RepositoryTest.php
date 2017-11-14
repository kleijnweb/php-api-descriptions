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

    public function testWillFailWhenKeyIsEmpty()
    {
        self::expectException(\InvalidArgumentException::class);

        $this->repository->get('');
    }

    public function testWillFailWhenPathDoesNotExist()
    {
        self::expectException(ResourceNotReadableException::class);
        $this->repository->get('/this/is/total/bogus');
    }

    public function testGettingDocumentThatDoestExistWillConstructIt()
    {
        $document = $this->repository->get('tests/definitions/openapi/petstore.yml');
        self::assertInstanceOf(Description::class, $document);
    }

    public function testWillCache()
    {
        $path       = 'tests/definitions/openapi/petstore.yml';
        $cache      = $this->getMockBuilder(ArrayCache::class)->disableOriginalConstructor()->getMock();
        $repository = new Repository(null, $cache);

        $cache->expects(self::exactly(1))->method('fetch')->with($path);
        $cache->expects(self::exactly(1))->method('save')->with($path, self::isType('object'));
        $repository->get($path);
    }

    public function testWillFetchFromCache()
    {
        $path        = 'tests/definitions/openapi/petstore.yml';
        $cache       = $this->getMockBuilder(ArrayCache::class)->disableOriginalConstructor()->getMock();
        $document    = $this->getMockBuilder(Document::class)->disableOriginalConstructor()->getMock();
        $description = new Description([], [], '', [], [], $document);

        $repository = new Repository(null, $cache);

        $cache->expects(self::exactly(1))->method('fetch')->with($path)->willReturn($description);
        self::assertInstanceOf(Description::class, $repository->get($path));
    }

    public function testCanUsePathPrefix()
    {
        $this->repository = new Repository('tests/definitions/');
        $document         = $this->repository->get('openapi/petstore.yml');

        self::assertInstanceOf(Description::class, $document);
    }
}
