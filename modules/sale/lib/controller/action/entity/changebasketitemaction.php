<?php

namespace Bitrix\Sale\Controller\Action\Entity;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Iblock;
use Bitrix\Catalog;

/**
 * Class ChangeBasketItemAction
 * @package Bitrix\Sale\Controller\Action\Entity
 * @example BX.ajax.runAction("sale.entity.changeBasketItem", { data: { fields: { siteId:'s1', fuserId:1, basketId:1, productId:1 }}});
 * @internal
 */
final class ChangeBasketItemAction extends BaseAction
{
	private function checkParams(array $fields): Sale\Result
	{
		$result = new Sale\Result();

		if (empty($fields['SITE_ID']))
		{
			$result->addError(
				new Main\Error(
					'siteId not found',
					Sale\Controller\ErrorEnumeration::CHANGE_BASKET_ITEM_ACTION_SITE_ID_NOT_FOUND
				)
			);
		}

		if (empty($fields['FUSER_ID']) || (int)$fields['FUSER_ID'] <= 0)
		{
			$result->addError(
				new Main\Error(
					'fuserId not found',
					Sale\Controller\ErrorEnumeration::CHANGE_BASKET_ITEM_ACTION_FUSER_ID_NOT_FOUND
				)
			);
		}

		if (empty($fields['BASKET_ID']) || (int)$fields['BASKET_ID'] <= 0)
		{
			$result->addError(
				new Main\Error(
					'basketId not found',
					Sale\Controller\ErrorEnumeration::CHANGE_BASKET_ITEM_ACTION_BASKET_ID_NOT_FOUND
				)
			);
		}

		if (empty($fields['PRODUCT_ID']) || (int)$fields['PRODUCT_ID'] <= 0)
		{
			$result->addError(
				new Main\Error(
					'productId not found',
					Sale\Controller\ErrorEnumeration::CHANGE_BASKET_ITEM_ACTION_PRODUCT_ID_NOT_FOUND
				)
			);
		}

		return $result;
	}

	public function run(array $fields)
	{
		$result = [];

		$changeBasketItemResult = $this->changeBasketItem($fields);
		if (!$changeBasketItemResult->isSuccess())
		{
			$this->addErrors($changeBasketItemResult->getErrors());
			return $result;
		}

		$changeBasketItemData = $changeBasketItemResult->getData();
		/** @var Sale\BasketItemBase $basketItem */
		$basketItem = $changeBasketItemData['basketItem'];
		return Sale\Helpers\Controller\Action\Entity\Order::getOrderProductByBasketItem($basketItem);
	}

	public function changeBasketItem(array $fields): Sale\Result
	{
		$result = new Sale\Result();

		$checkParamsResult = $this->checkParams($fields);
		if (!$checkParamsResult->isSuccess())
		{
			$result->addErrors($checkParamsResult->getErrors());
			return $result;
		}

		$basketId = $fields['BASKET_ID'];
		$productId = $fields['PRODUCT_ID'];
		$fuserId = $fields['FUSER_ID'];
		$siteId = $fields['SITE_ID'];

		$basket = $this->getBasketByFuserId($fuserId, $siteId);
		/** @var Sale\BasketItem $currentBasketItem */
		$currentBasketItem = $basket->getItemByBasketCode($basketId);
		if (!$currentBasketItem)
		{
			$result->addError(
				new Main\Error(
					'basket item load error',
					Sale\Controller\ErrorEnumeration::CHANGE_BASKET_ITEM_ACTION_BASKET_ITEM_LOAD
				)
			);
			return $result;
		}

		$currentOfferId = $currentBasketItem->getProductId();
		$parent = \CCatalogSku::getProductList($currentOfferId, 0);
		if (empty($parent[$currentOfferId]))
		{
			$result->addError(
				new Main\Error(
					'parent product load error',
					Sale\Controller\ErrorEnumeration::CHANGE_BASKET_ITEM_ACTION_PARENT_PRODUCT_LOAD
				)
			);
			return $result;
		}

		$parent = $parent[$currentOfferId];

		$offerPropertyCodeList = self::getOfferPropertyCodeList();

		$newProduct = self::selectOfferById($parent['IBLOCK_ID'], $parent['ID'], $productId, $offerPropertyCodeList);
		if (!$newProduct)
		{
			$result->addError(
				new Main\Error(
					'product load error',
					Sale\Controller\ErrorEnumeration::CHANGE_BASKET_ITEM_ACTION_PRODUCT_LOAD
				)
			);
			return $result;
		}

		$setFieldsResult = $currentBasketItem->setFields([
			'PRODUCT_ID' => $newProduct['ID'],
			'NAME' => $newProduct['NAME'],
			'PRODUCT_XML_ID' => $newProduct['XML_ID'],
		]);
		if (!$setFieldsResult->isSuccess())
		{
			foreach ($setFieldsResult->getErrors() as $error)
			{
				$result->addError(
					new Main\Error(
						$error->getMessage(),
						Sale\Controller\ErrorEnumeration::CHANGE_BASKET_ITEM_ACTION_SET_FIELD
					)
				);
			}
			return $result;
		}

		$refreshBasketResult = $basket->refresh(
			Sale\Basket\RefreshFactory::createSingle($currentBasketItem->getBasketCode())
		);
		if (!$refreshBasketResult->isSuccess())
		{
			foreach ($refreshBasketResult->getErrors() as $error)
			{
				$result->addError(
					new Main\Error(
						$error->getMessage(),
						Sale\Controller\ErrorEnumeration::CHANGE_BASKET_ITEM_ACTION_REFRESH_BASKET
					)
				);
			}
			return $result;
		}

		$basketProperties = self::getBasketProperties($parent['IBLOCK_ID'], $newProduct['ID'], $offerPropertyCodeList);
		$basketProperties['PRODUCT.XML_ID'] = [
			'NAME' => 'Product XML_ID',
			'CODE' => 'PRODUCT.XML_ID',
			'VALUE' => $currentBasketItem->getField('PRODUCT_XML_ID'),
		];

		self::setBasketProperties($currentBasketItem, $basketProperties);

		$saveBasketResult = $basket->save();
		if ($saveBasketResult->isSuccess())
		{
			$result->setData([
				'basket' => $basket,
				'basketItem' => $currentBasketItem,
			]);
		}
		else
		{
			/** @var Main\Error $error */
			foreach ($saveBasketResult->getErrors() as $error)
			{
				// save basket error
				$result->addError(
					new Main\Error(
						$error->getMessage(),
						Sale\Controller\ErrorEnumeration::CHANGE_BASKET_ITEM_ACTION_SAVE_BASKET
					)
				);
			}
		}

		return $result;
	}

	private function getBasketByFuserId($fuserId, $siteId): Sale\BasketBase
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var Sale\Basket $basketClassName */
		$basketClassName = $registry->getBasketClassName();
		return $basketClassName::loadItemsForFUser($fuserId, $siteId);
	}

	private static function getOfferPropertyCodeList(): array
	{
		$result = [];

		if (Main\Loader::includeModule('iblock') && Iblock\Model\PropertyFeature::isEnabledFeatures())
		{
			$iterator = Catalog\CatalogIblockTable::getList([
				'select' => ['IBLOCK_ID'],
				'filter' => ['!=PRODUCT_IBLOCK_ID' => 0],
			]);
			while ($row = $iterator->fetch())
			{
				$list = Catalog\Product\PropertyCatalogFeature::getOfferTreePropertyCodes(
					$row['IBLOCK_ID'],
					['CODE' => 'Y']
				);

				if (!empty($list) && is_array($list))
				{
					$result[] = $list;
				}
			}

			if ($result)
			{
				$result = array_merge(...$result);
			}
		}

		return $result;
	}

	private static function selectOfferById(int $iblockId, int $parentId, int $productId, array $offerPropertyCodeList = [])
	{
		$offers = \CCatalogSku::getOffersList(
			$parentId,
			$iblockId,
			[
				'ACTIVE' => 'Y',
				'ACTIVE_DATE' => 'Y',
				'CATALOG_AVAILABLE' => 'Y',
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => 'R',
			],
			['ID', 'IBLOCK_ID', 'XML_ID', 'NAME'],
			['CODE' => $offerPropertyCodeList]
		);

		if (empty($offers[$parentId][$productId]))
		{
			return null;
		}

		$result = [
			'ID' => $offers[$parentId][$productId]['ID'],
			'IBLOCK_ID' => $offers[$parentId][$productId]['IBLOCK_ID'],
			'NAME' => $offers[$parentId][$productId]['NAME'],
			'XML_ID' => $offers[$parentId][$productId]['XML_ID'],
			'PROPERTIES' => $offers[$parentId][$productId]['PROPERTIES'],
		];

		if (mb_strpos($result['XML_ID'], '#') === false)
		{
			$parentData = Iblock\ElementTable::getList([
				'select' => ['ID', 'XML_ID'],
				'filter' => ['ID' => $parentId],
			])->fetch();
			if (!empty($parentData))
			{
				$result['XML_ID'] = $parentData['XML_ID'].'#'.$result['XML_ID'];
			}
		}

		return $result;
	}

	private static function getBasketProperties(int $iblockId, int $productId, array $offerPropertyCodeList)
	{
		$newProperties = \CIBlockPriceTools::GetOfferProperties(
			$productId,
			$iblockId,
			$offerPropertyCodeList
		);

		$basketProperties = [];
		foreach ($newProperties as $row)
		{
			$codeExist = false;
			foreach ($offerPropertyCodeList as $code)
			{
				if ($code === $row['CODE'])
				{
					$codeExist = true;
					break;
				}
			}

			if (!$codeExist)
			{
				continue;
			}

			$basketProperties[$row['CODE']] = [
				'NAME' => $row['NAME'],
				'CODE' => $row['CODE'],
				'VALUE' => $row['VALUE'],
				'SORT' => $row['SORT'],
			];
		}

		return $basketProperties;
	}

	private static function setBasketProperties(Sale\BasketItem $basketItem, array $basketProperties)
	{
		$properties = $basketItem->getPropertyCollection();
		if ($properties)
		{
			$oldProperties = $properties->getPropertyValues();
			if (empty($oldProperties))
			{
				$oldProperties = $basketProperties;
			}
			else
			{
				$oldProperties = self::updateOffersProperties($oldProperties, $basketProperties);
			}

			$properties->redefine($oldProperties);
		}
	}

	private static function updateOffersProperties($oldProps, $newProps): array
	{
		if (!is_array($oldProps) || !is_array($newProps))
		{
			return [];
		}

		$result = [];

		if (empty($newProps))
		{
			return $oldProps;
		}

		if (empty($oldProps))
		{
			return $newProps;
		}

		foreach (array_keys($oldProps) as $code)
		{
			$oldValue = $oldProps[$code];
			$found = false;
			$key = false;
			$propId = (isset($oldValue['CODE']) ? (string)$oldValue['CODE'] : '').':'.$oldValue['NAME'];

			foreach ($newProps as $newKey => $newValue)
			{
				$newId = (isset($newValue['CODE']) ? (string)$newValue['CODE'] : '').':'.$newValue['NAME'];
				if ($newId === $propId)
				{
					$key = $newKey;
					$found = true;
					break;
				}
			}

			if ($found)
			{
				$oldValue['VALUE'] = $newProps[$key]['VALUE'];
				unset($newProps[$key]);
			}

			$result[$code] = $oldValue;
			unset($oldValue);
		}

		if (!empty($newProps))
		{
			foreach (array_keys($newProps) as $code)
			{
				$result[$code] = $newProps[$code];
			}
		}

		return $result;
	}
}
