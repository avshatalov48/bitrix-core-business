<?php

namespace Bitrix\Catalog\v2\Product;

use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\Product\SystemField;
use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\Iblock\IblockInfo;
use Bitrix\Catalog\v2\Image\ImageRepositoryContract;
use Bitrix\Catalog\v2\Property\PropertyRepositoryContract;
use Bitrix\Catalog\v2\Section\HasSectionCollection;
use Bitrix\Catalog\v2\Section\SectionCollection;
use Bitrix\Catalog\v2\Section\SectionRepositoryContract;
use Bitrix\Catalog\v2\Sku\HasSkuCollection;
use Bitrix\Catalog\v2\Sku\SkuCollection;
use Bitrix\Catalog\v2\Sku\SkuRepositoryContract;
use Bitrix\Main\Event;
use Bitrix\Main\ORM;
use Bitrix\Main\Result;

/**
 * Class BaseProduct
 *
 * @package Bitrix\Catalog\v2\Product
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
abstract class BaseProduct extends BaseIblockElementEntity implements HasSectionCollection, HasSkuCollection
{
	private const EVENT_PREFIX = 'Bitrix\Catalog\Product\Entity::';

	/** @var \Bitrix\Catalog\v2\Section\SectionRepositoryContract */
	protected $sectionRepository;
	/** @var \Bitrix\Catalog\v2\Sku\SkuRepositoryContract */
	protected $skuRepository;

	/** @var \Bitrix\Catalog\v2\Section\SectionCollection|\Bitrix\Catalog\v2\Section\Section[] */
	protected $sectionCollection;
	/** @var \Bitrix\Catalog\v2\Sku\SkuCollection|\Bitrix\Catalog\v2\Sku\Sku[] */
	protected $skuCollection;

	public function __construct(
		IblockInfo $iblockInfo,
		ProductRepositoryContract $productRepository,
		PropertyRepositoryContract $propertyRepository,
		ImageRepositoryContract $imageRepository,
		SectionRepositoryContract $sectionRepository,
		SkuRepositoryContract $skuRepository
	)
	{
		parent::__construct($iblockInfo, $productRepository, $propertyRepository, $imageRepository);
		$this->sectionRepository = $sectionRepository;
		$this->skuRepository = $skuRepository;

		$this->setIblockId($this->iblockInfo->getProductIblockId());
		$this->setType($this->iblockInfo->canHaveSku() ? ProductTable::TYPE_SKU : ProductTable::TYPE_PRODUCT);

		if (SystemField\ProductMapping::isAllowed())
		{
			$userField = SystemField\ProductMapping::load();
			if (!empty($userField))
			{
				$value = (!empty($userField['SETTINGS']['DEFAULT_VALUE']) && is_array($userField['SETTINGS']['DEFAULT_VALUE'])
					? $userField['SETTINGS']['DEFAULT_VALUE']
					: null
				);
				if ($value === null)
				{
					/** @var SystemField\Type\HighloadBlock $className */
					$className = SystemField\ProductMapping::getTypeId();

					$list = $className::getIdByXmlId(
						$userField['SETTINGS']['HLBLOCK_ID'],
						[SystemField\ProductMapping::MAP_LANDING]
					);
					if (isset($list[SystemField\ProductMapping::MAP_LANDING]))
					{
						$value = [
							$list[SystemField\ProductMapping::MAP_LANDING],
						];
					}
				}
				if ($value !== null)
				{
					$this->setField($userField['FIELD_NAME'], $value);
				}
			}
		}
	}

	/**
	 * @return \Bitrix\Catalog\v2\Section\SectionCollection|\Bitrix\Catalog\v2\Section\Section[]
	 */
	public function getSectionCollection(): SectionCollection
	{
		if ($this->sectionCollection === null)
		{
			// ToDo make lazy load like sku collection with iterator callback?
			$this->setSectionCollection($this->loadSectionCollection());
		}

		return $this->sectionCollection;
	}

	/**
	 * @return \Bitrix\Catalog\v2\Section\SectionCollection|\Bitrix\Catalog\v2\Section\Section[]
	 */
	protected function loadSectionCollection(): SectionCollection
	{
		return $this->sectionRepository->getCollectionByProduct($this);
	}

	/**
	 * @param \Bitrix\Catalog\v2\Section\SectionCollection $sectionCollection
	 * @return \Bitrix\Catalog\v2\Product\BaseProduct
	 *
	 * @internal
	 */
	public function setSectionCollection(SectionCollection $sectionCollection): self
	{
		$sectionCollection->setParent($this);

		$this->sectionCollection = $sectionCollection;

		return $this;
	}

	/**
	 * @return \Bitrix\Catalog\v2\Sku\SkuCollection|\Bitrix\Catalog\v2\Sku\BaseSku[]
	 */
	public function getSkuCollection(): SkuCollection
	{
		if ($this->skuCollection === null)
		{
			$this->setSkuCollection(
				$this->skuRepository->getCollectionByProduct($this)
			);
		}

		return $this->skuCollection;
	}

	/**
	 * @return \Bitrix\Catalog\v2\Sku\SkuCollection|\Bitrix\Catalog\v2\Sku\BaseSku[]
	 */
	public function loadSkuCollection(): SkuCollection
	{
		if ($this->skuCollection === null)
		{
			$this->setSkuCollection(
				$this->skuRepository->loadEagerCollectionByProduct($this)
			);
		}

		return $this->skuCollection;
	}

	/**
	 * @param \Bitrix\Catalog\v2\Sku\SkuCollection $skuCollection
	 * @return \Bitrix\Catalog\v2\Product\BaseProduct
	 *
	 * @internal
	 */
	public function setSkuCollection(SkuCollection $skuCollection): self
	{
		$skuCollection->setParent($this);

		$this->skuCollection = $skuCollection;

		return $this;
	}

	public function saveInternal(): Result
	{
		$isNew = $this->isNew();

		$result = parent::saveInternal();
		if ($result->isSuccess())
		{
			if ($isNew)
			{
				$eventId = self::EVENT_PREFIX . ORM\Data\DataManager::EVENT_ON_AFTER_ADD;
			}
			else
			{
				$eventId = self::EVENT_PREFIX . ORM\Data\DataManager::EVENT_ON_AFTER_UPDATE;
			}

			$this->sendOnAfterEvents($eventId);
		}

		return $result;
	}

	public function delete(): Result
	{
		$result = $this->deleteInternal();
		if ($result->isSuccess())
		{
			$this->sendOnAfterEvents(self::EVENT_PREFIX . ORM\Data\DataManager::EVENT_ON_AFTER_DELETE);
		}

		return $result;
	}

	private function sendOnAfterEvents(string $eventId): void
	{
		$eventData = [
			'id' => $this->getId(),
		];

		switch ($eventId)
		{
			case self::EVENT_PREFIX . ORM\Data\DataManager::EVENT_ON_AFTER_ADD:
			case self::EVENT_PREFIX . ORM\Data\DataManager::EVENT_ON_AFTER_UPDATE:
				$eventData['fields'] = $this->getFields();
				$type = $this->getType();
				if (
					$type !== ProductTable::TYPE_SKU
					&& $type !== ProductTable::TYPE_EMPTY_SKU
				)
				{
					/** @var \Bitrix\Catalog\v2\Sku\BaseSku $item */
					$item = $this->getSkuCollection()->getFirst();
					if ($item !== null)
					{
						$eventData['fields']['PRICES'] = $item->getPriceCollection()->toArray();
					}
				}
				break;
		}

		$event = new Event('catalog', $eventId, $eventData);
		$event->send();
	}

	public function setField(string $name, $value): BaseEntity
	{
		if ($name === 'NAME')
		{
			$productName = $this->getName();

			foreach ($this->getSkuCollection() as $sku)
			{
				if ($sku->getName() === $productName)
				{
					$sku->setName($value);
				}
			}
		}

		return parent::setField($name, $value);
	}
}