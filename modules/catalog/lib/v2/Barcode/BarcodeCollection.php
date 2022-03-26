<?php

namespace Bitrix\Catalog\v2\Barcode;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\IoC\ContainerContract;
use Bitrix\Catalog\v2\IoC\Dependency;

/**
 * Class BarcodeCollection
 *
 * @package Bitrix\Catalog\v2\Barcode
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class BarcodeCollection extends BaseCollection
{
	/** @var ContainerContract */
	protected $container;
	/** @var \Bitrix\Catalog\v2\StoreProduct\BarcodeFactory */
	protected $factory;

	public function __construct(ContainerContract $container, BarcodeFactory $factory)
	{
		$this->container = $container;
		$this->factory = $factory;
	}

	public function create(): Barcode
	{
		$barcode = $this->factory->createEntity();
		$this->add($barcode);

		return $barcode;
	}

	public function getItemByBarcode(string $barcode): ?Barcode
	{
		/** @var Barcode $item */
		foreach ($this->items as $item)
		{
			if ($barcode === $item->getBarcode())
			{
				return $item;
			}
		}

		return null;
	}

	public function setSimpleBarcodeValue(string $value = ''): self
	{
		$barcodeItem = $this->getFirst();
		if ($value !== '')
		{
			if (!$barcodeItem)
			{
				$barcodeItem = $this->create();
				$this->add($barcodeItem);
			}

			$barcodeItem->setBarcode($value);

		}
		elseif ($barcodeItem)
		{
			$barcodeItem->remove();
		}

		return $this;
	}
}
