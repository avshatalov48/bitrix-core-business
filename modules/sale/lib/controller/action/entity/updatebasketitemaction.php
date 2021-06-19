<?php

namespace Bitrix\Sale\Controller\Action\Entity;

use Bitrix\Main;
use Bitrix\Sale;

/**
 * Class UpdateBasketItemAction
 * @package Bitrix\Sale\Controller\Action\Entity
 * @example BX.ajax.runAction("sale.entity.updateBasketItem", { data: { id: 1, fields: { quantity: 2 }}});
 */
class UpdateBasketItemAction extends BaseAction
{
	public function run(int $id, array $fields)
	{
		$result = [];

		$updateBasketItemResult = $this->updateBasketItem($id, $fields);
		if (!$updateBasketItemResult->isSuccess())
		{
			$this->addErrors($updateBasketItemResult->getErrors());
			return $result;
		}

		$updateBasketItemData = $updateBasketItemResult->getData();
		/** @var Sale\BasketItemBase $basketItem */
		$basketItem = $updateBasketItemData['basketItem'];
		return Sale\Helpers\Controller\Action\Entity\Order::getOrderProductByBasketItem($basketItem);
	}

	public function updateBasketItem(int $id, array $fields): Sale\Result
	{
		$result = new Sale\Result();

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var Sale\Basket $basketClass */
		$basketClass = $registry->getBasketClassName();

		$basketIterator = $basketClass::getList([
			'select' => ['ORDER_ID', 'FUSER_ID', 'LID'],
			'filter' => [
				'=ID' => $id
			]
		]);
		if ($basketItemData = $basketIterator->fetch())
		{
			if (empty($basketItemData['ORDER_ID']))
			{
				$basket = $this->getBasket($basketItemData['FUSER_ID'], $basketItemData['LID']);
				if ($basket && !$basket->isEmpty())
				{
					$basketItem = $basket->getItemByBasketCode($id);
					foreach ($fields as $fieldName => $fieldValue)
					{
						$setFieldResult = $basketItem->setField($fieldName, $fieldValue);
						if (!$setFieldResult->isSuccess())
						{
							/** @var Main\Error $error */
							foreach ($setFieldResult->getErrors() as $error)
							{
								// set field error
								$result->addError(new Main\Error($error->getMessage(), 202150010000));
							}
						}
					}

					$checkQuantityResult = $this->checkQuantity($basket);
					if (!$checkQuantityResult->isSuccess())
					{
						foreach ($checkQuantityResult->getErrors() as $error)
						{
							// check quantity error
							$result->addError(new Main\Error($error->getMessage(), 202150030000));
						}
					}

					if ($result->isSuccess())
					{
						$saveResult = $basket->save();
						if ($saveResult->isSuccess())
						{
							$result->setData([
								'basket' => $basket,
								'basketItem' => $basketItem,
							]);
						}
						else
						{
							/** @var Main\Error $error */
							foreach ($saveResult->getErrors() as $error)
							{
								// save basket error
								$result->addError(new Main\Error($error->getMessage(), 202150020000));
							}
						}
					}
				}
				else
				{
					$result->addError(new Main\Error('basket item load error', 202150000002));
				}
			}
			else
			{
				$result->addError(new Main\Error('there is order with this basket item', 202150000001));
			}
		}
		else
		{
			$result->addError(new Main\Error('basket item with id '.$id.' is not exists', 202140400001));
		}

		return $result;
	}

	private function getBasket(int $fuserId, string $siteId): Sale\Basket
	{
		/** @var Sale\Basket\Storage $basketStorage */
		$basketStorage = Sale\Basket\Storage::getInstance($fuserId, $siteId);
		return $basketStorage->getBasket();
	}

	private function checkQuantity(Sale\Basket $basket): Sale\Result
	{
		$result = new Sale\Result();

		$actualQuantityList = $this->getActualQuantityList($basket);
		Sale\BasketComponentHelper::correctQuantityRatio($basket);

		$updatedQuantityList = $this->getActualQuantityList($basket);
		foreach ($updatedQuantityList as $basketCode => $itemQuantity)
		{
			if (!isset($actualQuantityList[$basketCode]) || $itemQuantity !== $actualQuantityList[$basketCode])
			{
				$result->addError(new Main\Error('quantity is incorrect'));
			}
		}

		return $result;
	}

	private function getActualQuantityList(Sale\Basket $basket): array
	{
		$quantityList = [];

		if (!$basket->isEmpty())
		{
			/** @var Sale\BasketItemBase $basketItem */
			foreach ($basket as $basketItem)
			{
				if ($basketItem->canBuy() && !$basketItem->isDelay())
				{
					$quantityList[$basketItem->getBasketCode()] = $basketItem->getQuantity();
				}
			}
		}

		return $quantityList;
	}
}
