<?php

declare(strict_types=1);

namespace Yireo\CatalogUtils\Test\Unit\Repository;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yireo\CatalogUtils\Repository\ProductRepository;
use Yireo\CatalogUtils\Repository\ProductSearchCriteriaBuilder;
use Yireo\CatalogUtils\Repository\ProductSearchCriteriaBuilderFactory;

class ProductRepositoryTest extends TestCase
{
    private ProductRepositoryInterface&MockObject $innerRepository;
    private ProductSearchCriteriaBuilderFactory&MockObject $searchCriteriaBuilderFactory;
    private ProductInterfaceFactory&MockObject $productFactory;
    private ProductRepository $repository;

    protected function setUp(): void
    {
        $this->innerRepository = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderFactory = $this->getMockBuilder(ProductSearchCriteriaBuilderFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->productFactory = $this->getMockBuilder(ProductInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->repository = new ProductRepository(
            $this->innerRepository,
            $this->searchCriteriaBuilderFactory,
            $this->productFactory
        );
    }

    public function testGetForwardsAllArguments(): void
    {
        $product = $this->createProduct();
        $this->innerRepository->expects($this->once())
            ->method('get')
            ->with('foo', true, 2, true)
            ->willReturn($product);

        $this->assertSame($product, $this->repository->get('foo', true, 2, true));
    }

    public function testGetBySku(): void
    {
        $product = $this->createProduct();
        $this->innerRepository->expects($this->once())
            ->method('get')
            ->with('foo')
            ->willReturn($product);

        $this->assertSame($product, $this->repository->getBySku('foo'));
    }

    public function testGetByIdForwardsAllArguments(): void
    {
        $product = $this->createProduct();
        $this->innerRepository->expects($this->once())
            ->method('getById')
            ->with(42, true, 2, true)
            ->willReturn($product);

        $this->assertSame($product, $this->repository->getById(42, true, 2, true));
    }

    public function testSaveDropsSaveOptions(): void
    {
        $product = $this->createProduct();
        $this->innerRepository->expects($this->once())
            ->method('save')
            ->with($this->identicalTo($product), false)
            ->willReturn($product);

        $this->assertSame($product, $this->repository->save($product, true));
    }

    public function testDelete(): void
    {
        $product = $this->createProduct();
        $this->innerRepository->expects($this->once())
            ->method('delete')
            ->with($this->identicalTo($product))
            ->willReturn(true);

        $this->assertTrue($this->repository->delete($product));
    }

    public function testDeleteById(): void
    {
        $this->innerRepository->expects($this->once())
            ->method('deleteById')
            ->with('foo')
            ->willReturn(true);

        $this->assertTrue($this->repository->deleteById('foo'));
    }

    public function testGetList(): void
    {
        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)->getMock();
        $searchResults = $this->createSearchResults([]);
        $this->innerRepository->expects($this->once())
            ->method('getList')
            ->with($this->identicalTo($searchCriteria))
            ->willReturn($searchResults);

        $this->assertSame($searchResults, $this->repository->getList($searchCriteria));
    }

    public function testGetSearchCriteriaBuilder(): void
    {
        $builder = $this->getMockBuilder(ProductSearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderFactory->expects($this->once())
            ->method('create')
            ->willReturn($builder);

        $this->assertSame($builder, $this->repository->getSearchCriteriaBuilder());
    }

    public function testCreateNewProduct(): void
    {
        $data = ['sku' => 'foo'];
        $product = $this->createProduct();
        $this->productFactory->expects($this->once())
            ->method('create')
            ->with($data)
            ->willReturn($product);

        $this->assertSame($product, $this->repository->createNewProduct($data));
    }

    public function testGetFirstProductInListingReturnsFirstItem(): void
    {
        $firstProduct = $this->createProduct();
        $secondProduct = $this->createProduct();
        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)->getMock();

        $searchCriteriaBuilder = $this->createSearchCriteriaBuilder($searchCriteria);
        $this->innerRepository->method('getList')
            ->with($this->identicalTo($searchCriteria))
            ->willReturn($this->createSearchResults([$firstProduct, $secondProduct]));

        $this->assertSame($firstProduct, $this->repository->getFirstProductInListing($searchCriteriaBuilder));
    }

    public function testGetFirstProductInListingThrowsExceptionWhenEmpty(): void
    {
        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)->getMock();
        $searchCriteriaBuilder = $this->createSearchCriteriaBuilder($searchCriteria);
        $this->innerRepository->method('getList')->willReturn($this->createSearchResults([]));

        $this->expectException(NotFoundException::class);
        $this->repository->getFirstProductInListing($searchCriteriaBuilder);
    }

    private function createSearchCriteriaBuilder(SearchCriteriaInterface $searchCriteria): SearchCriteriaBuilder
    {
        $searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilder->expects($this->once())->method('setPageSize')->with(1)->willReturnSelf();
        $searchCriteriaBuilder->expects($this->once())->method('setCurrentPage')->with(0)->willReturnSelf();
        $searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);

        return $searchCriteriaBuilder;
    }

    /**
     * @param ProductInterface[] $items
     */
    private function createSearchResults(array $items): ProductSearchResultsInterface
    {
        $searchResults = $this->getMockBuilder(ProductSearchResultsInterface::class)->getMock();
        $searchResults->method('getItems')->willReturn($items);

        return $searchResults;
    }

    private function createProduct(): ProductInterface
    {
        return $this->getMockBuilder(ProductInterface::class)->getMock();
    }
}
