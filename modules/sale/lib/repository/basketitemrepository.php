<?php

namespace Bitrix\Sale\Repository;

use Bitrix\Sale;

/**
 * Class BasketItemRepository
 * @package Bitrix\Sale\Repository
 * @internal
 */
final class BasketItemRepository
{
	/** @var BasketItemRepository */
	private static $instance;

	/**
	 * BasketItemRepository constructor.
	 */
	private function __construct()
	{}

	/**
	 * @return BasketItemRepository
	 */
	public static function getInstance(): BasketItemRepository
	{
		if (is_null(static::$instance))
		{
			static::$instance = new BasketItemRepository();
		}

		return static::$instance;
	}

	/**
	 * @param int $id
	 * @return \Bitrix\Sale\BasketItem|null
	 */
	public function getById(int $id): ?Sale\BasketItem
	{
		/** @var Sale\Basket $basketClass */
		$basketClass = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER)->getBasketClassName();

		$basketRow = $basketClass::getList([
			'select' => ['ID', 'ORDER_ID'],
			'filter' => [
				'=ID' => $id
			]
		])->fetch();
		if (!$basketRow)
		{
			return null;
		}

		return static::getInstance()->getByRow($basketRow);
	}

	/**
	 * @param array $ids
	 * @return array
	 */
	public function getByIds(array $ids): array
	{
		$result = [];

		/** @var Sale\Basket $basketClass */
		$basketClass = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER)->getBasketClassName();

		$basketList = $basketClass::getList([
			'select' => ['ID', 'ORDER_ID'],
			'filter' => [
				'=ID' => $ids
			]
		]);

		while ($basketRow = $basketList->fetch())
		{
			$basket = static::getInstance()->getByRow($basketRow);
			if (is_null($basket))
			{
				continue;
			}

			$result[] = $basket;
		}

		return $result;
	}

	/**
	 * @param array $basketRow
	 * @return Sale\BasketItem|null
	 */
	private function getByRow(array $basketRow): ?Sale\BasketItem
	{
		$orderClassName = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER)->getOrderClassName();

		/** @var Sale\Order $orderClassName */
		$order = $orderClassName::load($basketRow['ORDER_ID']);
		if ($order === null)
		{
			return null;
		}

		$basket = $order->getBasket();

		/** @var \Bitrix\Sale\BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			if ($basketItem->getId() !== (int)$basketRow['ID'])
			{
				continue;
			}

			return $basketItem;
		}

		return null;
	}
}
