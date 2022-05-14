<?php

namespace Bitrix\Sale\Controller\Action\Entity;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Catalog;

/**
 * Class AddBasketItemAction
 * @package Bitrix\Sale\Controller\Action\Entity
 * @example BX.ajax.runAction("sale.entity.addBasketItem", { data: { fields: { siteId:'s1', product: {..}}}});
 * @internal
 */
final class AddBasketItemAction extends BaseAction
{
	private function checkParams(array $fields): Sale\Result
	{
		$result = new Sale\Result();

		if (empty($fields['SITE_ID']))
		{
			$result->addError(
				new Main\Error(
					'siteId not found',
					Sale\Controller\ErrorEnumeration::ADD_BASKET_ITEM_ACTION_SITE_ID_NOT_FOUND
				)
			);
		}

		if (empty($fields['FUSER_ID']) || (int)$fields['FUSER_ID'] <= 0)
		{
			$result->addError(
				new Main\Error(
					'fuserId not found',
					Sale\Controller\ErrorEnumeration::ADD_BASKET_ITEM_ACTION_FUSER_ID_NOT_FOUND
				)
			);
		}

		if (empty($fields['PRODUCT']))
		{
			$result->addError(
				new Main\Error(
					'product not found',
					Sale\Controller\ErrorEnumeration::ADD_BASKET_ITEM_ACTION_PRODUCT_NOT_FOUND
				)
			);
		}

		return $result;
	}

	public function run(array $fields)
	{
		$result = [];

		$addBasketItemResult = $this->addBasketItem($fields);
		if (!$addBasketItemResult->isSuccess())
		{
			$this->addErrors($addBasketItemResult->getErrors());
			return $result;
		}

		$addBasketItemData = $addBasketItemResult->getData();
		/** @var Sale\BasketItemBase $basketItem */
		$basketItem = $addBasketItemData['basketItem'];
		return Sale\Helpers\Controller\Action\Entity\Order::getOrderProductByBasketItem($basketItem);
	}

	public function addBasketItem(array $fields): Sale\Result
	{
		$result = new Sale\Result();

		$checkParamsResult = $this->checkParams($fields);
		if (!$checkParamsResult->isSuccess())
		{
			$result->addErrors($checkParamsResult->getErrors());
			return $result;
		}

		$fuserId = $fields['FUSER_ID'];
		$siteId = $fields['SITE_ID'];
		$product = $fields['PRODUCT'];
		$options = [
			'USE_MERGE' => !isset($fields['USE_MERGE']) || $fields['USE_MERGE'] !== 'N' ? 'Y' : 'N',
		];

		$basket = $this->getBasketByFuserId($fuserId, $siteId);

		$addProductToBasketResult = Catalog\Product\Basket::addProductToBasket($basket, $product, ['SITE_ID' => $siteId], $options);
		if ($addProductToBasketResult->isSuccess())
		{
			$saveBasketResult = $basket->save();
			if ($saveBasketResult->isSuccess())
			{
				$addProductToBasketData = $addProductToBasketResult->getData();
				if ($basketItem = $addProductToBasketData['BASKET_ITEM'])
				{
					$result->setData([
						'basket' => $basket,
						'basketItem' => $basketItem,
					]);
				}
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
							Sale\Controller\ErrorEnumeration::ADD_BASKET_ITEM_SAVE_BASKET
						)
					);
				}
			}
		}
		else
		{
			/** @var Main\Error $error */
			foreach ($addProductToBasketResult->getErrors() as $error)
			{
				$result->addError(
					new Main\Error(
						$error->getMessage(),
						Sale\Controller\ErrorEnumeration::ADD_BASKET_ITEM_ADD_PRODUCT_TO_BASKET
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
}
