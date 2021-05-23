<?php

namespace Bitrix\Catalog\v2\Integration\JS\ProductForm;

use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Catalog\Component\ImageInput;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Iblock\Url\AdminPage\BuilderManager;
use Bitrix\Main\Web\Json;

class BasketItem
{
	private const DISCOUNT_TYPE_MONETARY = 1;
	private const DISCOUNT_TYPE_PERCENTAGE = 2;
	private const SHOP_DETAIL_URL_TYPE = 'SHOP';

	private $fields;
	private $detailUrlType;
	private $id;
	private $priceGroupId;

	/** @var BaseSku $sku */
	private $sku;

	public function __construct()
	{
		$this->id = uniqid('', true);
		$this->fields = [
			'innerId' => $this->id,
			'productId' => 0,
			'skuId' => 0,
			'code' => '',
			'name' => '',
			'sort' => 0,
			'module' => '',
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
		];

		$this->setDetailUrlManagerType(self::SHOP_DETAIL_URL_TYPE);

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

		$skuTreeItems =  $skuTree->loadWithSelectedOffers([
			$product->getId() => $this->sku->getId()
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
		if ($variationImageField->isEmpty())
		{
			return null;
		}

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

		$urlBuilder->setIblockId($this->sku->getIblockId());
		return $urlBuilder->getElementDetailUrl($parent->getId());
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
		$this->fields['productId'] = '';
		$this->fields['skuId'] = '';
		$this->fields['module'] = '';

		return $this;
	}

	public function fillFieldsFromSku(): self
	{
		if ($this->sku instanceof BaseSku)
		{
			$this
				->setName($this->sku->getName())
				->setMeasureCode((int)$this->sku->getField('MEASURE_CODE'))
				->setMeasureName($this->sku->getField('MEASURE_NAME'))
			;

			$ratioItem = $this->sku->getMeasureRatioCollection()->findDefault();
			if ($ratioItem)
			{
				$this->setMeasureRatio((float)$ratioItem->getRatio());
			}

			if ($this->priceGroupId)
			{
				$priceItem = $this->sku->getPriceCollection()->findByGroupId($this->priceGroupId);
				if ($priceItem)
				{
					$price = (float)$priceItem->getPrice();
					$this
						->setPrice($price)
						->setBasePrice($price)
						->setPriceExclusive($price)
					;
				}
			}
		}

		return $this;
	}

	public function setQuantity(float $value): self
	{
		$this->fields['quantity'] = $value;

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
				: self::DISCOUNT_TYPE_PERCENTAGE
		;

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
			'detailUrl' => $this->getDetailUrl(),
			'discountSum' => $this->getField('discountSum'),
		];
	}
}