<?php

namespace Bitrix\Sale\Helpers\Controller\Action\Entity;

use Bitrix\Main;
use Bitrix\Main\Config;
use Bitrix\Main\Loader;
use Bitrix\Sale;
use Bitrix\Sale\PriceMaths;
use Bitrix\Catalog;
use Bitrix\Iblock;

/**
 * Class Order
 * @package Bitrix\Sale\Helpers\Controller\Action\Entity
 * @internal
 */
final class Order
{
	/**
	 * @param Sale\Order $order
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getAggregateOrder(Sale\Order $order)
	{
		$profile = self::getProfileList([
			'USER_ID' => $order->getUserId(),
			'PERSON_TYPE_ID' => $order->getPersonTypeId()
		]);

		return [
			'ORDER' => $order->toArray(),
			'PERSON_TYPE' => self::getPersonTypeList([
				'ID'=>$order->getPersonTypeId()
			]),
			'USER_PROFILE' => $profile,
			'USER_PROFILE_VALUES' => self::getProfileListValues([
				'USER_PROPS_ID' => ($profile['ID'] ?? 0),
			]),
			'BASKET_ITEMS' => self::getOrderProducts($order),
			'ORDER_PRICE_TOTAL' => self::getTotal($order),
			'PAY_SYSTEMS' => self::getPaySystemListWithRestrictions($order),
			'DELIVERY_SERVICES' => self::getDeliveryServiceListWithRestrictions($order),
			'PROPERTIES' => self::getOrderProperties($order),
			'VARIANTS' => self::getVariants($order),
			'PAYMENTS' => self::getPayments($order),
			'CHECKS' => self::getChecks($order),
		];
	}

	private static function getProfileList(array $filter = [])
	{
		$result = [];
		$r = \CSaleOrderUserProps::GetList(
			[
				'ID' => 'DESC',
			],
			$filter,
			false,
			false,
			['ID', 'USER_ID', 'NAME', 'PERSON_TYPE_ID']
		);
		if ($profile = $r->fetch())
		{
			$result = $profile;
		}

		return $result;
	}

	private static function getProfileListValues(array $filter = []): array
	{
		$result = [];
		$r = \CSaleOrderUserPropsValue::GetList(
			[
				'ID' => 'DESC',
			],
			$filter,
			false,
			false,
			['ID', 'ORDER_PROPS_ID', 'VALUE', 'SORT']
		);
		while ($profileValue = $r->fetch())
		{
			$result[] = $profileValue;
		}

		return $result;
	}

	private static function getPersonTypeList(array $filter = [])
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\PersonType $personTypeClassName */
		$personTypeClassName = $registry->getPersonTypeClassName();
		return $personTypeClassName::getList([
			'select' => ['*'],
			'filter' => $filter,
			'order' => [
				'SORT' => 'ASC',
				'NAME' => 'ASC',
			],
		])->fetch();
	}

	private static function getPaySystemListWithRestrictions(Sale\Order $order): array
	{
		$result = [];

		$paySystemList = Sale\PaySystem\Manager::getListWithRestrictionsByOrder($order);
		foreach ($paySystemList as $paySystemItem)
		{
			$paySystemItem['LOGOTYPE'] = $paySystemItem['LOGOTIP'];
			$paySystemItem['TARIFF'] = $paySystemItem['TARIF'];

			$paySystemItem['LOGOTYPE_SRC'] = '';
			if (!empty($paySystemItem['LOGOTYPE']))
			{
				$paySystemItem['LOGOTYPE_SRC'] = \CFile::GetPath($paySystemItem['LOGOTYPE']);
			}

			unset(
				$paySystemItem['PAY_SYSTEM_ID'],
				$paySystemItem['PERSON_TYPE_ID'],
				$paySystemItem['PARAMS'],
				$paySystemItem['TARIF'],
				$paySystemItem['LOGOTIP'],
				$paySystemItem['ENTITY_REGISTRY_TYPE']
			);

			$result[] = $paySystemItem;
		}

		return $result;
	}

	private static function getDeliveryServiceListWithRestrictions(Sale\Order $order): array
	{
		$result = [];
		foreach ($order->getShipmentCollection() as $shipment)
		{
			$deliveryList = Sale\Delivery\Services\Manager::getRestrictedObjectsList($shipment);
			foreach ($deliveryList as $deliveryItem)
			{
				$result[] = [
					'ID' => $deliveryItem->getId(),
					'SORT' => $deliveryItem->getSort(),
					'NAME' => $deliveryItem->getName(),
					'DESCRIPTION' => $deliveryItem->getDescription(),
					'LOGOTYPE' => $deliveryItem->getLogotip(),
					'LOGOTYPE_SRC' => \CFile::GetPath($deliveryItem->getLogotip()),
				];
			}
		}

		return $result;
	}

	public static function getTotal(Sale\Order $order)
	{
		/** @var Sale\Basket $basket */
		//$basket = $order->getBasket();

		$calculateBasket = $order->getBasket()->createClone();

		$discounts = $order->getDiscount();
		$showPrices = $discounts->getShowPrices();
		if (!empty($showPrices['BASKET']))
		{
			foreach ($showPrices['BASKET'] as $basketCode => $data)
			{
				$basketItem = $calculateBasket->getItemByBasketCode($basketCode);
				if ($basketItem instanceof Sale\BasketItemBase)
				{
					$basketItem->setFieldNoDemand('BASE_PRICE', $data['SHOW_BASE_PRICE']);
					$basketItem->setFieldNoDemand('PRICE', $data['SHOW_PRICE']);
					$basketItem->setFieldNoDemand('DISCOUNT_PRICE', $data['SHOW_DISCOUNT']);
				}
			}
		}
		unset($showPrices);

		$result = [
			'WEIGHT_UNIT' => Config\Option::get('sale', 'weight_unit', false, $order->getSiteId()),
			'WEIGHT_KOEF' => Config\Option::get('sale', 'weight_koef', 1, $order->getSiteId()),
		];

/*		$result['BASKET_POSITIONS'] = $basket->count();
		$result['ORDER_PRICE'] = PriceMaths::roundPrecision($basket->getPrice());
		$result['ORDER_WEIGHT'] = $basket->getWeight();

		$result['PRICE_WITHOUT_DISCOUNT_VALUE'] = $basket->getBasePrice();
		$result['BASKET_PRICE_DISCOUNT_DIFF_VALUE'] = PriceMaths::roundPrecision(
			$basket->getBasePrice() - $basket->getPrice()
		);
		$result['DISCOUNT_PRICE'] = PriceMaths::roundPrecision(
			$order->getDiscountPrice() + ($result['PRICE_WITHOUT_DISCOUNT_VALUE'] - $result['ORDER_PRICE'])
		); */

		$result['BASKET_POSITIONS'] = $calculateBasket->count();
		$result['ORDER_PRICE'] = PriceMaths::roundPrecision($calculateBasket->getPrice());
		$result['ORDER_WEIGHT'] = $calculateBasket->getWeight();

		$result['PRICE_WITHOUT_DISCOUNT_VALUE'] = $calculateBasket->getBasePrice();
		$result['BASKET_PRICE_DISCOUNT_DIFF_VALUE'] = PriceMaths::roundPrecision(
			$calculateBasket->getBasePrice() - $calculateBasket->getPrice()
		);
		$result['DISCOUNT_PRICE'] = PriceMaths::roundPrecision(
			$order->getDiscountPrice() + ($result['PRICE_WITHOUT_DISCOUNT_VALUE'] - $result['ORDER_PRICE'])
		);

		$result['DELIVERY_PRICE'] = PriceMaths::roundPrecision($order->getDeliveryPrice());
		$result['ORDER_TOTAL_PRICE'] = PriceMaths::roundPrecision($order->getPrice());

		return $result;
	}

	private static function getOrderProducts(Sale\Order $order): array
	{
		$result = [];

		$basket = $order->getBasket();
		if ($basket)
		{
			$result = static::getOrderProductsByBasket($basket);
		}

		return $result;
	}

	public static function getOrderProductsByBasket(Sale\BasketBase $basket): array
	{
		$result = [];

		$basketClone = $basket->createClone();

		$order = $basketClone->getOrder();
		if (!$order)
		{
			$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
			/** @var Sale\Order $orderClassName */
			$orderClassName = $registry->getOrderClassName();
			$order = $orderClassName::create($basketClone->getSiteId());
			$order->setBasket($basketClone);
		}

		if ($order)
		{
			$discounts = $order->getDiscount();
			$showPrices = $discounts->getShowPrices();
			if (!empty($showPrices['BASKET']))
			{
				foreach ($showPrices['BASKET'] as $basketCode => $data)
				{
					$basketItem = $basketClone->getItemByBasketCode($basketCode);
					if ($basketItem instanceof Sale\BasketItemBase)
					{
						$basketItem->setFieldNoDemand('BASE_PRICE', $data['SHOW_BASE_PRICE']);
						$basketItem->setFieldNoDemand('PRICE', $data['SHOW_PRICE']);
						$basketItem->setFieldNoDemand('DISCOUNT_PRICE', $data['SHOW_DISCOUNT']);
					}
				}
			}
		}

		$basketData = static::getBasketProducts($basketClone);
		foreach ($basketClone as $item)
		{
			$result[] = array_merge(
				$basketData[$item->getId()],
				[
					'CATALOG_PRODUCT' => static::getCatalogProduct($basketData[$item->getId()])
				]
			);
		}

		return $result;
	}

	public static function getOrderProductByBasketItem(Sale\BasketItemBase $basketItem): array
	{
		$basket = $basketItem->getBasket();
		$basketClone = $basket->createClone();
		$calculateBasketItem = $basketClone->getItemByBasketCode($basketItem->getBasketCode());

		$order = $basketClone->getOrder();
		if (!$order)
		{
			$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
			/** @var Sale\Order $orderClassName */
			$orderClassName = $registry->getOrderClassName();
			$order = $orderClassName::create($basketClone->getSiteId());
			$order->setBasket($basketClone);
		}

		if ($calculateBasketItem && $order)
		{
			$discounts = $order->getDiscount();
			$showPrices = $discounts->getShowPrices();
			if (!empty($showPrices['BASKET']))
			{
				$data = $showPrices['BASKET'][$calculateBasketItem->getBasketCode()] ?? null;
				if ($data)
				{
					$calculateBasketItem->setFieldNoDemand('BASE_PRICE', $data['SHOW_BASE_PRICE']);
					$calculateBasketItem->setFieldNoDemand('PRICE', $data['SHOW_PRICE']);
					$calculateBasketItem->setFieldNoDemand('DISCOUNT_PRICE', $data['SHOW_DISCOUNT']);
				}
			}
		}

		$basketItemData = static::getBasketProduct($calculateBasketItem);
		return array_merge(
			$basketItemData,
			[
				'CATALOG_PRODUCT' => static::getCatalogProduct($basketItemData)
			]
		);
	}

	private static function getCatalogProduct(array $basketItemData): array
	{
		$result = [];

		$repositoryFacade = Catalog\v2\IoC\ServiceContainer::getRepositoryFacade();
		$product = $repositoryFacade->loadVariation($basketItemData['PRODUCT_ID']);
		if ($product)
		{
			$result = $product->getFields();

			$result['TYPE'] = ($result['TYPE'] === Catalog\ProductTable::TYPE_SERVICE) ? 'service' : 'product';

			$result['PREVIEW_PICTURE'] ??= null;
			if ((int)$result['PREVIEW_PICTURE'] > 0)
			{
				$result['PREVIEW_PICTURE_SRC'] = \CFile::GetPath($result['PREVIEW_PICTURE']);
			}

			$result['DETAIL_PICTURE'] ??= null;
			if ((int)$result['DETAIL_PICTURE'] > 0)
			{
				$result['DETAIL_PICTURE_SRC'] = \CFile::GetPath($result['DETAIL_PICTURE']);
			}

			$result['AVAILABLE_QUANTITY'] = $result['QUANTITY'];
			unset($result['QUANTITY']);

			if ($result['QUANTITY_TRACE'] === Catalog\ProductTable::STATUS_DEFAULT)
			{
				$result['QUANTITY_TRACE'] = (Main\Config\Option::get('catalog', 'default_quantity_trace') === 'Y') ? 'Y' : 'N';
			}
			if ($result['CAN_BUY_ZERO'] === Catalog\ProductTable::STATUS_DEFAULT)
			{
				$result['CAN_BUY_ZERO'] = (Main\Config\Option::get('catalog', 'default_can_buy_zero') === 'Y') ? 'Y' : 'N';
			}

			$checkMaxQuantity = ($result['QUANTITY_TRACE'] === 'Y' && $result['CAN_BUY_ZERO'] === 'N') ? 'Y' : 'N';
			$result['CHECK_MAX_QUANTITY'] = $checkMaxQuantity;

			$result['RATIO'] = 1;
			$ratioItem = $product->getMeasureRatioCollection()->findDefault();
			if (!$ratioItem)
			{
				$ratioItem = $product->getMeasureRatioCollection()->getFirst();
			}

			if ($ratioItem)
			{
				$result['RATIO'] = $ratioItem->getRatio();
			}

			/** @var Catalog\v2\Property\PropertyCollection|Catalog\v2\Property\Property[] $propertyCollection */
			$propertyCollection = $product->getPropertyCollection();
			foreach ($propertyCollection as $propertyItem)
			{
				$values = $propertyItem->getPropertyValueCollection()->getValues();

				if ($propertyItem->getPropertyType() === Iblock\PropertyTable::TYPE_LIST)
				{
					$enumPropData = Iblock\PropertyEnumerationTable::getList([
						'select' => ['ID', 'VALUE', 'SORT', 'XML_ID'],
						'filter' => [
							'=ID' => $values,
							'=PROPERTY_ID' => $propertyItem->getId(),
						],
					])->fetchAll();

					if ($enumPropData)
					{
						if (!$propertyItem->isMultiple())
						{
							$values = reset($enumPropData);
						}
					}
					else
					{
						$values = null;
					}
				}
				elseif ($propertyItem->getPropertyType() === Iblock\PropertyTable::TYPE_FILE)
				{
					$imageSrcValues = null;
					if ($propertyItem->isMultiple())
					{
						$imageSrcValues = [];
						foreach ($values as $value)
						{
							$imageSrcValues[] = [
								'FILE_ID' => $value,
								'SRC' => \CFile::GetPath($value),
							];
						}
					}
					else
					{
						$imageSrcValues = [
							'FILE_ID' => $values,
							'SRC' => \CFile::GetPath($values),
						];
					}

					$values = $imageSrcValues;
				}

				$result['PRODUCT_PROPERTIES'][$propertyItem->getId()] = [
					'TYPE' => $propertyItem->getPropertyType(),
					'CODE' => $propertyItem->getCode(),
					'NAME' => $propertyItem->getName(),
					'VALUES' => $values,
				];
			}

			/** @var Catalog\v2\Image\ImageCollection|Catalog\v2\Image\BaseImage[] $imageCollection */
			$imageCollection = $product->getImageCollection();
			$frontImage = $imageCollection->getFrontImage();

			$frontImageData = null;
			if ($frontImage)
			{
				$frontImageData = $frontImage->getFields();
			}
			else
			{
				/** @var Catalog\v2\Product\Product $parent */
				$parent = $product->getParent();
				if ($parent)
				{
					$imageCollection = $parent->getImageCollection();
					$parentFrontImage = $imageCollection->getFrontImage();
					if ($parentFrontImage)
					{
						$frontImageData = $parentFrontImage->getFields();
					}
				}
			}

			$result['FRONT_IMAGE'] = $frontImageData;

			$result['IMAGE_COLLECTION'] = [];
			foreach ($imageCollection as $imageItem)
			{
				$result['IMAGE_COLLECTION'][] = $imageItem->getFields();
			}

			$result['SKU'] = self::getSkuTree($product->getIblockId(), $product->getId());
		}

		return $result;
	}

	private static function getSkuTree(int $iblockId, int $productId): array
	{
		$result = [];

		$skuRepository = Catalog\v2\IoC\ServiceContainer::getSkuRepository($iblockId);
		if ($skuRepository)
		{
			$sku = $skuRepository->getEntityById($productId);
			if ($sku)
			{
				$parentProduct = $sku->getParent();
				if ($parentProduct)
				{
					/** @var Catalog\Component\SkuTree $skuTree */
					$skuTree = Catalog\v2\IoC\ServiceContainer::make('sku.tree', ['iblockId' => $iblockId]);

					$parentProductId = $parentProduct->getId();
					$skuId = $sku->getId();

					$tree = $skuTree->loadJsonOffers([$parentProductId => $skuId]);
					if (isset($tree[$parentProductId][$skuId]))
					{
						$result = [
							'TREE' => $tree[$parentProductId][$skuId],
							'PARENT_PRODUCT_ID' => $parentProductId,
						];
					}
				}
			}
		}

		return $result;
	}

	private static function getBasketProducts(Sale\BasketBase $basket): array
	{
		$result = [];

		/** @var Sale\BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			$result[$basketItem->getId()] = self::getBasketProduct($basketItem);
		}

		return $result;
	}

	private static function getBasketProduct(Sale\BasketItemBase $basketItem)
	{
		$arBasketItem = $basketItem->getFieldValues();
		if ($basketItem->getVatRate() > 0)
		{
			$arBasketItem['VAT_VALUE'] = PriceMaths::roundPrecision($basketItem->getVat());
		}

		$arBasketItem['QUANTITY'] = $basketItem->getQuantity();
		$arBasketItem['DISCOUNT_PRICE'] = PriceMaths::roundPrecision($basketItem->getDiscountPrice());

		$arBasketItem['DISCOUNT_PRICE_PERCENT'] = 0;
		if ($arBasketItem['CUSTOM_PRICE'] !== 'Y')
		{
			$arBasketItem['DISCOUNT_PRICE_PERCENT'] = Sale\Discount::calculateDiscountPercent(
				$arBasketItem['BASE_PRICE'],
				$arBasketItem['DISCOUNT_PRICE']
			);
			if ($arBasketItem['DISCOUNT_PRICE_PERCENT'] === null)
			{
				$arBasketItem['DISCOUNT_PRICE_PERCENT'] = 0;
			}
			else
			{
				$arBasketItem['DISCOUNT_PRICE_PERCENT'] = PriceMaths::roundPrecision($arBasketItem['DISCOUNT_PRICE_PERCENT']);
			}
		}

		$arBasketItem['PROPS'] = [];
		/** @var Sale\BasketPropertiesCollection $propertyCollection */
		$propertyCollection = $basketItem->getPropertyCollection();
		$propList = $propertyCollection->getPropertyValues();
		foreach ($propList as &$prop)
		{
			if ($prop['CODE'] === 'CATALOG.XML_ID'
				|| $prop['CODE'] === 'PRODUCT.XML_ID'
				|| $prop['CODE'] === 'SUM_OF_CHARGE'
			)
			{
				continue;
			}

			$prop = array_filter($prop, ['CSaleBasketHelper', 'filterFields']);
			$arBasketItem['PROPS'][] = $prop;
		}
		unset($prop);

		$arBasketItem['PRICE'] = PriceMaths::roundPrecision($basketItem->getPrice());
		$arBasketItem['BASE_PRICE'] = PriceMaths::roundPrecision($basketItem->getBasePrice());

		$arBasketItem['SUM'] = PriceMaths::roundPrecision($arBasketItem['PRICE'] * $basketItem->getQuantity());
		$arBasketItem['SUM_BASE'] = PriceMaths::roundPrecision($basketItem->getBasePrice() * $basketItem->getQuantity());

		$arBasketItem['SUM_DISCOUNT_DIFF'] = PriceMaths::roundPrecision($arBasketItem['SUM_BASE'] - $arBasketItem['SUM']);

		$dimension = $basketItem->getField('DIMENSIONS');
		if($dimension && \is_string($dimension) && \CheckSerializedData($dimension))
		{
			$arBasketItem['DIMENSIONS'] = unserialize($dimension, ['allowed_classes' => false]);
		}

		if (!empty($arBasketItem) && static::useCatalog())
		{
			$measure = getMeasures([$basketItem->getId() => $arBasketItem]);
			$arBasketItem = $measure[$basketItem->getId()];
		}

		return $arBasketItem;
	}

	private static function useCatalog()
	{
		return Loader::includeModule('catalog');
	}

	private static function getOrderProperties(Sale\Order $order): array
	{
		$result = [];

		$propertyCollection = $order->getPropertyCollection();
		if ($propertyCollection)
		{
			$propertyCollectionData = $propertyCollection->getArray();
			foreach ($propertyCollectionData['properties'] as $property)
			{
				if ($property['UTIL'] === 'Y')
				{
					continue;
				}

				$result[] = $property;
			}
		}

		return $result;
	}

	private static function getVariants(Sale\Order $order): array
	{
		$propertyCollection = $order->getPropertyCollection();
		if (!$propertyCollection)
		{
			return [];
		}

		$propertyCollectionData = $propertyCollection->getArray();
		$propertyEnumIds = [];
		foreach ($propertyCollectionData['properties'] as $property)
		{
			if ($property['TYPE'] === 'ENUM')
			{
				$propertyEnumIds[] = $property['ID'];
			}
		}

		if (empty($propertyEnumIds))
		{
			return [];
		}

		$variants = Sale\Internals\OrderPropsVariantTable::getList([
			'filter' => [
				'=ORDER_PROPS_ID' => $propertyEnumIds,
			],
			'order' => ['SORT' => 'ASC'],
		])->fetchAll();

		return $variants;
	}

	private static function getPayments(Sale\Order $order): array
	{
		/** @var sale\Order $orderClone */
		$orderClone = $order->createClone();
		return $orderClone->getPaymentCollection()->toArray();
	}

	private static function getChecks(Sale\Order $order): array
	{
		$checks = [];

		/** @var sale\Order $orderClone */
		$orderClone = $order->createClone();

		/** @var Sale\Payment $payment */
		foreach ($orderClone->getPaymentCollection() as $payment)
		{
			$checkList = Sale\Cashbox\CheckManager::getCheckInfo($payment);
			foreach ($checkList as $check)
			{
				$checks[] = $check;
			}
		}

		return $checks;
	}
}