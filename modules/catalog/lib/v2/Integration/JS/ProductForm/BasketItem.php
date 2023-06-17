<?php

namespace Bitrix\Catalog\v2\Integration\JS\ProductForm;

use Bitrix\Catalog\v2\Price\BasePrice;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Catalog\Component\ImageInput;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Property\Property;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Url\ShopBuilder;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader;
use Bitrix\Sale\Internals;
use Bitrix\Iblock\Url\AdminPage\BuilderManager;
use Bitrix\Main\Web\Json;

class BasketItem
{
	private const DISCOUNT_TYPE_MONETARY = 1;
	private const DISCOUNT_TYPE_PERCENTAGE = 2;
	private const BRAND_PROPERTY_CODE = 'BRAND_FOR_FACEBOOK';

	private $fields;
	private $detailUrlType;
	private $id;
	private $priceGroupId;

	/** @var BaseSku $sku */
	private $sku;

	/** @var BasePrice $priceItem */
	private $priceItem;

	public function __construct()
	{
		$this->id = uniqid('', true);
		$this->fields = [
			'innerId' => $this->id,
			'productId' => 0,
			'skuId' => 0,
			'type' => null,
			'code' => '',
			'name' => '',
			'sort' => 0,
			'module' => '',
			'catalogPrice' => null,
			'basePrice' => 0,
			'price' => 0,
			'priceExclusive' => 0,
			'isCustomPrice' => 'Y',
			'discountType' => self::DISCOUNT_TYPE_PERCENTAGE,
			'quantity' => 1,
			'measureCode' => 0,
			'measureName' => '',
			'measureRatio' => 1,
			'discountRate' => 0,
			'discount' => 0,
			'taxId' => 0,
			'taxIncluded' => 'N',
			'additionalFields' => [],
			'properties' => [],
			'brands' => '',
			'weight' => 0,
			'dimensions' => [],
		];

		$this->setDetailUrlManagerType(ShopBuilder::TYPE_ID);

		$basePriceGroup = \CCatalogGroup::GetBaseGroup();
		if ($basePriceGroup)
		{
			$this->setPriceGroupId((int)$basePriceGroup['ID']);
		}
	}

	private function getField($name)
	{
		return $this->fields[$name] ?? '';
	}

	private function getEncodedSkuTree(): string
	{
		if (!$this->sku || $this->sku->isSimple())
		{
			return '';
		}

		/** @var BaseProduct $product */
		$product = $this->sku->getParent();
		$skuTree = ServiceContainer::make('sku.tree', ['iblockId' => $product->getIblockId()]);

		if (!$skuTree)
		{
			return '';
		}

		$skuTreeItems = $skuTree->loadJsonOffers([
			$product->getId() => $this->sku->getId(),
		]);

		if (!$skuTreeItems[$product->getId()][$this->sku->getId()])
		{
			return '';
		}

		return Json::encode($skuTreeItems[$product->getId()][$this->sku->getId()]);
	}

	private function getImageInputField(): ?array
	{
		if (!$this->sku)
		{
			return null;
		}

		$variationImageField = new ImageInput($this->sku);

		return $variationImageField->getFormattedField();
	}

	private function getSum(): float
	{
		return (float)$this->getField('priceExclusive') * (float)$this->getField('quantity');
	}

	private function getDetailUrl(): string
	{
		if (!$this->sku || $this->sku->isNew())
		{
			return '';
		}

		$parent = $this->sku->getParent();
		$urlBuilder = BuilderManager::getInstance()->getBuilder($this->detailUrlType);
		if (!$urlBuilder || !$parent)
		{
			return '';
		}

		$urlBuilder->setIblockId($parent->getIblockId());

		return $urlBuilder->getElementDetailUrl($parent->getId());
	}

	public function getFields(): array
	{
		return $this->fields;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getSkuId(): ?int
	{
		if ($this->sku)
		{
			return $this->sku->getId();
		}

		return null;
	}

	public function getPriceItem(): ?BasePrice
	{
		return $this->priceItem;
	}

	public function setSku(BaseSku $sku): self
	{
		$this->sku = $sku;
		if ($sku->getParent())
		{
			$this->fields['productId'] = $sku->getParent()->getId();
		}
		$this->fields['skuId'] = $sku->getId();
		$this->fields['module'] = 'catalog';

		$this->fillFieldsFromSku();

		return $this;
	}

	public function removeSku(): self
	{
		$this->sku = null;
		$this->priceItem = null;
		$this->fields['productId'] = '';
		$this->fields['skuId'] = '';
		$this->fields['module'] = '';
		$this->fields['properties'] = [];

		return $this;
	}

	private function fillFieldsFromSku(): void
	{
		$this->setName($this->sku->getName());
		$this->setType($this->sku->getType());
		$this->fillProperties();
		$this->fillBrands();
		$this->fillMeasureFields();
		$this->fillTaxFields();
		$this->fillPriceFields();
		$this->fillDeliveryFields();
	}

	private function fillProperties(): void
	{
		$properties = [];
		foreach ($this->sku->getPropertyCollection() as $property)
		{
			$formattedValues = $this->getFormattedProperty($property);
			if ($formattedValues === null)
			{
				continue;
			}
			$properties[] = $formattedValues;
		}

		$this->fields['properties'] = $properties;
	}

	private function fillBrands(): void
	{
		/** @var BaseProduct $product */
		$product = $this->sku->getParent();
		if (!$product)
		{
			return;
		}

		$property = $product->getPropertyCollection()->findByCode(self::BRAND_PROPERTY_CODE);
		if (!$property)
		{
			return;
		}

		$userType = \CIBlockProperty::GetUserType($property->getUserType());
		$userTypeMethod = $userType['GetUIEntityEditorProperty'];
		$propertySettings = $property->getSettings();
		$propertyValues = $property->getPropertyValueCollection()->getValues();
		$description = $userTypeMethod($propertySettings, $propertyValues);
		$propertyBrandItems = $description['data']['items'];

		$selectedBrandItems = [];

		foreach ($propertyBrandItems as $propertyBrandItem)
		{
			if (in_array($propertyBrandItem['VALUE'], $propertyValues, true))
			{
				$selectedBrandItems[] = $propertyBrandItem;
			}
		}

		$this->fields['brands'] = $selectedBrandItems;
	}

	private function getFormattedProperty(Property $property): ?array
	{
		$propertyKeys = array_flip(['ID', 'NAME', 'CODE', 'SORT', 'XML_ID']);
		$formattedValues = $property->getPropertyValueCollection()->toArray();
		if (empty($formattedValues))
		{
			return null;
		}

		$enumValueMap = [];
		if ($property->getPropertyType() === PropertyTable::TYPE_LIST)
		{
			$enumIds = array_column($formattedValues, 'VALUE');
			$enumSettings = PropertyEnumerationTable::getList([
				'select' => ['ID', 'VALUE'],
				'filter' => [
					'=ID' => $enumIds,
				],
			])
				->fetchAll()
			;

			$enumValueMap = array_column($enumSettings, 'VALUE', 'ID');
		}

		$propertySettings = $property->getSettings();
		foreach ($formattedValues as $propertyValueId => $valueInfo)
		{
			$value = $valueInfo['VALUE'];

			if ($property->getPropertyType() === PropertyTable::TYPE_LIST)
			{
				$value = $enumValueMap[$value] ?? $value;
			}

			$displayProperty = array_merge(
				$propertySettings,
				[
					'DESCRIPTION' => $valueInfo['DESCRIPTION'] ?? null,
					'~DESCRIPTION' => $valueInfo['DESCRIPTION'] ?? null,
					'VALUE' => $value,
					'~VALUE' => $value,
				]
			);

			$displayProperty = \CIBlockFormatProperties::GetDisplayValue([], $displayProperty);

			$formattedValues[$propertyValueId]['DISPLAY_VALUE'] = $displayProperty['DISPLAY_VALUE'];
		}

		$propertySettings = array_intersect_key($propertySettings, $propertyKeys);
		$propertySettings['PROPERTY_VALUES'] = $formattedValues;

		return $propertySettings;
	}

	private function fillMeasureFields(): void
	{
		$measureId = (int)$this->sku->getField('MEASURE');
		$filter =
			$measureId > 0
				? ['=ID' => $this->sku->getField('MEASURE')]
				: ['=IS_DEFAULT' => 'Y']
		;

		$measureRow = \CCatalogMeasure::getList(
			['CODE' => 'ASC'],
			$filter,
			false,
			['nTopCount' => 1],
			['CODE', 'SYMBOL', 'SYMBOL_INTL']
		);

		if ($measure = $measureRow->Fetch())
		{
			$name = $measure['SYMBOL'] ?? $measure['SYMBOL_INTL'];
			$this
				->setMeasureCode((int)$measure['CODE'])
				->setMeasureName($name)
			;
		}

		$ratioItem = $this->sku->getMeasureRatioCollection()->findDefault();
		if ($ratioItem)
		{
			$this->setMeasureRatio((float)$ratioItem->getRatio());
		}
	}

	private function fillTaxFields(): void
	{
		$taxId = $this->sku->getField('VAT_ID');
		if (empty($taxId))
		{
			$taxId = $this->sku->getIblockInfo()->getVatId();
		}

		$this
			->setTaxId((int)$taxId)
			->setTaxIncluded($this->sku->getField('VAT_INCLUDED'))
		;
	}

	private function fillPriceFields(): void
	{
		if (!$this->priceGroupId)
		{
			return;
		}

		$this->priceItem = $this->sku->getPriceCollection()->findByGroupId($this->priceGroupId);
		if ($this->priceItem)
		{
			$price = (float)$this->priceItem->getPrice();
			$this
				->setPrice($price)
				->setBasePrice($price)
				->setPriceExclusive($price)
			;
		}
	}

	private function fillDeliveryFields(): void
	{
		$this->fields['weight'] = $this->sku->getField('WEIGHT');

		$this->fields['dimensions'] = [
			'LENGTH' => $this->sku->getField('LENGTH'),
			'WIDTH' => $this->sku->getField('WIDTH'),
			'HEIGHT' => $this->sku->getField('HEIGHT'),
		];
	}

	private function hasEditRights(): bool
	{
		global $USER;

		if (!$this->sku || !$USER instanceof \CUser)
		{
			return false;
		}

		return
			\CIBlockElementRights::UserHasRightTo($this->sku->getIblockId(), $this->sku->getId(), 'element_edit')
			&& \CIBlockElementRights::UserHasRightTo($this->sku->getIblockId(), $this->sku->getId(), 'element_edit_price')
			&& !AccessController::getCurrent()->check(ActionDictionary::ACTION_PRICE_EDIT)
		;
	}

	public function getCatalogPrice(): ?float
	{
		if (!$this->priceItem)
		{
			return null;
		}

		return (float)$this->priceItem->getPrice();
	}

	public function setQuantity(float $value): self
	{
		$this->fields['quantity'] = $value;

		return $this;
	}

	public function setId(string $value): self
	{
		$this->id = $value;
		$this->fields['innerId'] = $value;

		return $this;
	}

	public function setName(string $value = null): self
	{
		$this->fields['name'] = $value;

		return $this;
	}

	public function setCode(string $value): self
	{
		$this->fields['code'] = $value;

		return $this;
	}

	public function setType(?int $value): self
	{
		$this->fields['type'] = $value;

		return $this;
	}

	public function setSort(int $value): self
	{
		$this->fields['sort'] = $value;

		return $this;
	}

	public function setDiscountType(int $value): self
	{
		$this->fields['discountType'] =
			$value === self::DISCOUNT_TYPE_MONETARY
				? self::DISCOUNT_TYPE_MONETARY
				: self::DISCOUNT_TYPE_PERCENTAGE;

		return $this;
	}

	public function setCustomPriceType(string $value = null): self
	{
		$this->fields['isCustomPrice'] = ($value === 'N') ? 'N' : 'Y';

		return $this;
	}

	public function setBasePrice(float $value): self
	{
		$this->fields['basePrice'] = $value > 0 ? $value : 0;

		return $this;
	}

	public function setPrice(float $value): self
	{
		$this->fields['price'] = $value > 0 ? $value : 0;

		return $this;
	}

	public function setPriceExclusive(float $value): self
	{
		$this->fields['priceExclusive'] = $value > 0 ? $value : 0;

		return $this;
	}

	public function setMeasureCode(int $code): self
	{
		if ($code > 0)
		{
			$this->fields['measureCode'] = $code;
		}

		return $this;
	}

	public function setMeasureName($name = null): self
	{
		$this->fields['measureName'] = $name;

		return $this;
	}

	public function setMeasureRatio(float $ratio): self
	{
		$this->fields['measureRatio'] = $ratio > 0 ? $ratio : 1;

		return $this;
	}

	public function setDiscountRate(float $value): self
	{
		$this->fields['discountRate'] = $value;

		return $this;
	}

	public function setDiscountValue(float $value): self
	{
		$this->fields['discount'] = $value;

		return $this;
	}

	public function addAdditionalField(string $name, $value): self
	{
		$this->fields['additionalFields'][$name] = $value;

		return $this;
	}

	public function setTaxIncluded(string $value = null): self
	{
		$this->fields['taxIncluded'] = ($value === 'N') ? 'N' : 'Y';

		return $this;
	}

	public function setTaxId(int $value): self
	{
		$this->fields['taxId'] = $value;

		return $this;
	}

	public function clearAdditionalFields(): self
	{
		$this->fields['additionalFields'] = [];

		return $this;
	}

	public function setPriceGroupId(int $groupId): self
	{
		if ($groupId > 0)
		{
			$this->priceGroupId = $groupId;
		}

		return $this;
	}

	public function setDetailUrlManagerType(string $type): self
	{
		$this->detailUrlType = $type;

		return $this;
	}

	public function getResult(): array
	{
		return [
			'selectorId' => $this->id,
			'offerId' => $this->sku ? $this->sku->getId() : null,
			'fields' => $this->fields,
			'skuTree' => $this->getEncodedSkuTree(),
			'showDiscount' => !empty($this->getField('discount')) ? 'Y' : 'N',
			'image' => $this->getImageInputField(),
			'sum' => $this->getSum(),
			'catalogPrice' => $this->getCatalogPrice(),
			'detailUrl' => $this->getDetailUrl(),
			'discountSum' => $this->getField('discountSum'),
			'hasEditRights' => $this->hasEditRights(),
		];
	}
}
