<?php

namespace Bitrix\Catalog\v2\Tests\Unit;

use Bitrix\Catalog\v2\IoC\Dependency;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Catalog\v2\Tests\BaseTest;

class SkuTest extends BaseTest
{
	/** @var \Bitrix\Catalog\v2\Sku\SkuFactory */
	private static $factory;
	/** @var \Bitrix\Catalog\v2\Sku\SkuRepositoryContract */
	private static $repository;

	public static function setUpBeforeClass() : void
	{
		parent::setUpBeforeClass();
		static::$factory = static::$container->make(Dependency::SKU_FACTORY, ['iblockId' => 8]);
		static::$repository = static::$container->make(Dependency::SKU_REPOSITORY, ['iblockId' => 8]);
	}

	protected function setUp() : void
	{
	}

	private function createNewSku(): BaseSku
	{
		return static::$factory->createEntity(static::$factory::SKU);
	}

	private function loadExistingSku(): BaseSku
	{
		// ToDo move it to mocks
		return static::$repository->getEntityById(1078);
	}

	public function testLoadPartialSkuCollection(): void
	{
		$sku = $this->loadExistingSku();
		$newSku = $this->createNewSku();

		$skuCollection = $sku->getParentCollection();

		$this->assertFalse($skuCollection->isChanged());
		$skuCollection->add($newSku);
		$this->assertTrue($skuCollection->isChanged());
	}
}
