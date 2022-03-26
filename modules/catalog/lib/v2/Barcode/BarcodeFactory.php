<?php

namespace Bitrix\Catalog\v2\Barcode;

use Bitrix\Catalog\v2\IoC\ContainerContract;

/**
 * Class BarcodeFactory
 *
 * @package Bitrix\Catalog\v2\Barcode
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class BarcodeFactory
{
	public const BARCODE = Barcode::class;
	public const BARCODE_COLLECTION = BarcodeCollection::class;

	protected $container;

	/**
	 * StoreFactory constructor.
	 *
	 * @param \Bitrix\Catalog\v2\IoC\ContainerContract $container
	 */
	public function __construct(ContainerContract $container)
	{
		$this->container = $container;
	}

	/**
	 * @return \Bitrix\Catalog\v2\Barcode\Barcode
	 */
	public function createEntity(): Barcode
	{
		return $this->container->make(self::BARCODE);
	}

	/**
	 * @return \Bitrix\Catalog\v2\Barcode\BarcodeCollection
	 */
	public function createCollection(): BarcodeCollection
	{
		return $this->container->make(self::BARCODE_COLLECTION);
	}
}
