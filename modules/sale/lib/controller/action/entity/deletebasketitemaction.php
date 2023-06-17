<?php

namespace Bitrix\Sale\Controller\Action\Entity;

use Bitrix\Main;
use Bitrix\Sale;

/**
 * Class DeleteBasketItemAction
 * @package Bitrix\Sale\Controller\Action\Entity
 * @example BX.ajax.runAction("sale.entity.deleteBasketItem", { data: { id: 1 }});
 * @internal
 */
final class DeleteBasketItemAction extends Sale\Controller\Action\BaseAction
{
	public function run(int $id)
	{
		$deleteBasketItemResult = $this->deleteBasketItem($id);
		if (!$deleteBasketItemResult->isSuccess())
		{
			$this->addErrors($deleteBasketItemResult->getErrors());
		}
	}

	public function deleteBasketItem(int $id): Sale\Result
	{
		$result = new Sale\Result();

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var Sale\Basket $basketClass */
		$basketClass = $registry->getBasketClassName();

		$basketIterator = $basketClass::getList([
			'select' => ['ORDER_ID', 'FUSER_ID', 'LID'],
			'filter' => [
				'=ID' => $id,
			]
		]);
		if ($basketItemData = $basketIterator->fetch())
		{
			if (empty($basketItemData['ORDER_ID']))
			{
				$basket = $basketClass::loadItemsForFUser($basketItemData['FUSER_ID'], $basketItemData['LID']);
				if ($basket && !$basket->isEmpty())
				{
					$basket->getItemByBasketCode($id)->delete();

					$saveResult = $basket->save();
					if ($saveResult->isSuccess())
					{
						$result->setData(['basket' => $basket]);
					}
					else
					{
						/** @var Main\Error $error */
						foreach ($saveResult->getErrors() as $error)
						{
							// save basket error
							$result->addError(
								new Main\Error(
									$error->getMessage(),
									Sale\Controller\ErrorEnumeration::DELETE_BASKET_ITEM_ACTION_SAVE_BASKET
								)
							);
						}
					}
				}
				else
				{
					$result->addError(
						new Main\Error(
							'basket load error',
							Sale\Controller\ErrorEnumeration::DELETE_BASKET_ITEM_ACTION_BASKET_LOAD
						)
					);
				}
			}
			else
			{
				$result->addError(
					new Main\Error(
						'there is order with this basket item',
						Sale\Controller\ErrorEnumeration::DELETE_BASKET_ITEM_ACTION_ORDER_EXIST
					)
				);
			}
		}
		else
		{
			$result->addError(
				new Main\Error(
					'basket item with id '.$id.' is not exists',
					Sale\Controller\ErrorEnumeration::DELETE_BASKET_ITEM_ACTION_BASKET_ITEM_NOT_EXIST
				)
			);
		}

		return $result;
	}
}
