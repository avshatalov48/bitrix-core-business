<?php

namespace Bitrix\Catalog\v2\Tests\Unit;

use Bitrix\Catalog\v2\IoC\Dependency;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\Tests\BaseTest;

class ProductTest extends BaseTest
{
	/** @var \Bitrix\Catalog\v2\Product\ProductFactory */
	private static $factory;
	/** @var \Bitrix\Catalog\v2\Product\ProductRepositoryContract */
	private static $repository;

	public static function setUpBeforeClass() : void
	{
		parent::setUpBeforeClass();
		static::$factory = static::$container->make(Dependency::PRODUCT_FACTORY, ['iblockId' => 8]);
		static::$repository = static::$container->make(Dependency::PRODUCT_REPOSITORY, ['iblockId' => 8]);
	}

	protected function setUp() : void
	{
	}

	private function createNewProduct(): BaseProduct
	{
		return static::$factory->createEntity(static::$factory::PRODUCT);
	}

	private function loadExistingProduct(): BaseProduct
	{
		// ToDo mock it
		return static::$repository->getEntityById(509);
	}

	// ToDo remove temporary test for qcachegrind profiler
	public function testPerformanceOfCreation(): void
	{
		$products = [];

		foreach (range(0, 100) as $item)
		{
			$products[] = $this->createNewProduct();
		}

		$this->assertCount(101, $products);
	}

	public function testChangeExistingProduct(): void
	{
		$product = $this->loadExistingProduct();

		$previousValue = $product->getField('NAME');
		$this->assertFalse($product->isChanged());

		$product->setField('NAME', 'TEST');
		$this->assertTrue($product->isChanged());

		$product->setField('NAME', $previousValue);
		$this->assertFalse($product->isChanged());
	}

	public function testChangeExistingProductAndSaveProduct(): void
	{
		$product = $this->loadExistingProduct();

		$product->setField('NAME', 'TEST');
		$product->save();

		$this->assertFalse($product->isChanged());
		$this->assertEquals('TEST', $product->getField('NAME'));
	}

	public function testChangeExistingProductVariantAndSaveProduct(): void
	{
		$product = $this->loadExistingProduct();

		foreach ($product->getSkuCollection() as $sku)
		{
			$sku->setField('NAME', 'TEST');
			$this->assertTrue($sku->isChanged());
			$this->assertEquals('TEST', $sku->getField('NAME'));
		}

		$this->assertTrue($product->isChanged());
		$this->assertTrue($product->getSkuCollection()->isChanged());

		$product->save();

		$this->assertFalse($product->isChanged());
		$this->assertFalse($product->getSkuCollection()->isChanged());

		foreach ($product->getSkuCollection() as $sku)
		{
			$this->assertFalse($sku->isChanged());
			$this->assertEquals('TEST', $sku->getField('NAME'));
		}
	}

	public function testChangeExistingProductVariantAndSaveVariant(): void
	{
		$product = $this->loadExistingProduct();

		foreach ($product->getSkuCollection() as $sku)
		{
			$sku->setField('NAME', 'TEST');
			$sku->save();

			$this->assertFalse($sku->isChanged());
			$this->assertEquals('TEST', $sku->getField('NAME'));
		}

		$this->assertFalse($product->isChanged());
		$this->assertFalse($product->getSkuCollection()->isChanged());
	}
}
