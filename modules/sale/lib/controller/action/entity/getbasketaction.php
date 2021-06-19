<?php

namespace Bitrix\Sale\Controller\Action\Entity;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Sale\Helpers;

/**
 * Class GetBasketAction
 * @package Bitrix\Sale\Controller\Action\Entity
 * @example BX.ajax.runAction("sale.entity.getBasket", { data: { fields: { siteId:'s1', fuserId:1 }}});
 */
class GetBasketAction extends BaseAction
{
	private function checkParams(array $fields): Sale\Result
	{
		$result = new Sale\Result();

		if (empty($fields['SITE_ID']))
		{
			$this->addError(new Main\Error('siteId not found', 202340400001));
		}

		if (empty($fields['FUSER_ID']) || (int)$fields['FUSER_ID'] <= 0)
		{
			$this->addError(new Main\Error('fuserId not found', 202340400002));
		}

		return $result;
	}

	public function run(array $fields)
	{
		$checkParamsResult = $this->checkParams($fields);
		if (!$checkParamsResult->isSuccess())
		{
			$this->addErrors($checkParamsResult->getErrors());
			return null;
		}

		$fuserId = $fields['FUSER_ID'];
		$basket = $this->getBasketByFuserId($fuserId, $fields['SITE_ID']);

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Order $orderClassName */
		$orderClassName = $registry->getOrderClassName();
		$order = $orderClassName::create($fields['SITE_ID']);
		$order->setBasket($basket);

		return [
			'BASKET_ITEMS' => Helpers\Controller\Action\Entity\Order::getOrderProductsByBasket($basket),
			'ORDER_PRICE_TOTAL' => Helpers\Controller\Action\Entity\Order::getTotal($order),
		];
	}

	private function getBasketByFuserId($fuserId, $siteId): Sale\BasketBase
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var Sale\Basket $basketClassName */
		$basketClassName = $registry->getBasketClassName();
		return $basketClassName::loadItemsForFUser($fuserId, $siteId)->getOrderableItems();
	}
}
