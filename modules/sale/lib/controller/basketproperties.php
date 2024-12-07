<?php

namespace Bitrix\Sale\Controller;

use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale;
use Bitrix\Sale\BasketPropertiesCollection;
use Bitrix\Sale\BasketPropertyItem;
use Bitrix\Sale\Result;

class BasketProperties extends Controller
{
	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			BasketPropertyItem::class,
			'basketProperty',
			function($className, $id)
			{
				$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

				/** @var BasketPropertyItem $basketPropertyClass */
				$basketPropertyClass = $registry->getBasketPropertyItemClassName();

				$b = $basketPropertyClass::getList([
					'select'=>[
						'BASKET_ID',
					],
					'filter'=>[
						'=ID' => $id,
					],
				]);

				if ($bRow = $b->fetch())
				{
					/** @var Sale\Basket $basketClass */
					$basketClass = $registry->getBasketClassName();

					$r = $basketClass::getList([
						'select'=>[
							'ORDER_ID',
						],
						'filter'=>[
							'=ID' => (int)$bRow['BASKET_ID'],
						],
					]);

					if ($row = $r->fetch())
					{
						/** @var Sale\Order $orderClass */
						$orderClass = $registry->getOrderClassName();

						$order = $orderClass::load($row['ORDER_ID']);
						$basket = $order->getBasket()->getItemByBasketCode($bRow['BASKET_ID']);
						if ($basket)
						{
							$property = $basket->getPropertyCollection()->getItemById($id);
							if ($property)
							{
								return $property;
							}
						}
					}
				}

				$this->addError(new Error('basket property is not exists', 200240400003));

				return null;
			}
		);
	}

	//region Actions
	public function getFieldsAction()
	{
		$entity = new Sale\Rest\Entity\BasketProperties();

		return [
			'BASKET_PROPERTIES' => $entity->prepareFieldInfos($entity->getFields()),
		];
	}

	public function listAction(
		PageNavigation $pageNavigation,
		array $select = [],
		array $filter = [],
		array $order = [],
		bool $__calculateTotalCount = true
	): Page
	{
		$select = empty($select) ? ['*'] : $select;
		$order = empty($order) ? ['ID' => 'ASC'] : $order;

		$iterator = Sale\Internals\BasketPropertyTable::getList([
			'select' => $select,
			'filter' => $filter,
			'order' => $order,
			'offset' => $pageNavigation->getOffset(),
			'limit' => $pageNavigation->getLimit(),
			'count_total' => $__calculateTotalCount,
		]);
		$items = $iterator->fetchAll();
		$totalCount = $__calculateTotalCount ? $iterator->getCount(): 0;
		unset($iterator);

		return new Page('BASKET_PROPERTIES', $items, $totalCount);
	}

	public function getAction(BasketPropertyItem $basketProperty)
	{
		return [
			'BASKET_PROPERTY' => $this->get($basketProperty),
		];
	}

	public function addAction(array $fields)
	{
		if (!isset($fields['BASKET_ID']))
		{
			$this->addError(new Error('Basket item id is absent', 200240400004));

			return null;
		}

		$basketId = (int)$fields['BASKET_ID'];
		if ($basketId <= 0)
		{
			$this->addError(new Error('Basket item id is bad', 200240400005));

			return null;
		}
		unset($fields['BASKET_ID']);

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var Sale\Basket $basketClass */
		$basketClass = $registry->getBasketClassName();

		$iterator = $basketClass::getList([
			'select'=>[
				'ORDER_ID',
			],
			'filter'=>[
				'=ID' => $basketId,
			],
		]);
		$row = $iterator->fetch();
		unset($iterator);

		if (empty($row))
		{
			$this->addError(new Error('Basket item not exists', 200240400002));

			return null;
		}

		/** @var Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		$order = $orderClass::load($row['ORDER_ID']);
		$basketItem = $order->getBasket()->getItemByBasketCode($basketId);

		if (!($basketItem instanceof Sale\BasketItem))
		{
			$this->addError(new Error('Basket item not exists', 200240400001));

			return null;
		}

		/** @var BasketPropertiesCollection $propertyCollection */
		$propertyCollection = $basketItem->getPropertyCollection();
		$basketProperty = $propertyCollection->createItem();
		$result = $basketProperty->setFields($fields);

		if ($result->isSuccess() && !$result->hasWarnings())
		{
			$r = $this->save($basketProperty);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}
		elseif ($result->hasWarnings())
		{
			$this->addErrors($result->getWarnings());

			return null;
		}
		else
		{
			return [
				'BASKET_PROPERTY' => $this->get($basketProperty),
			];
		}
	}

	public function updateAction(BasketPropertyItem $basketProperty, array $fields)
	{
		$r = $basketProperty->setFields($fields);

		if (!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());

			return null;
		}

		if ($r->hasWarnings())
		{
			$this->addErrors($r->getWarnings());

			return null;
		}

		$r = $this->save($basketProperty);
		if (!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());

			return null;
		}

		return [
			'BASKET_PROPERTY' => $this->get($basketProperty),
		];
	}

	public function deleteAction(BasketPropertyItem $basketProperty)
	{
		$r = $basketProperty->delete();

		if (!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());

			return null;
		}

		if ($r->hasWarnings())
		{
			$this->addErrors($r->getWarnings());

			return null;
		}

		$r = $this->save($basketProperty);
		if (!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());

			return null;
		}

		return true;
	}
	//endregion

	protected function get(BasketPropertyItem $basketProperty, array $fields = [])
	{
		/** @var BasketPropertiesCollection $properties */
		$properties = $basketProperty->getCollection();
		$basketItem = $properties->getBasketItem();
		/** @var Sale\Basket $basket */
		$basket = $basketItem->getCollection();
		/** @var Sale\Order $order */
		$order = $basket->getOrder();

		$basketItems = $this->toArray($order, $fields)['ORDER']['BASKET_ITEMS'];
		foreach ($basketItems as $item)
		{
			foreach ($item['PROPERTIES'] as $property)
			{
				if ($property['ID'] == $basketProperty->getId())
				{
					return $property;
				}
			}
		}

		return [];
	}

	private function save(BasketPropertyItem $basketProperty): Result
	{
		$result = new Result();
		/** @var BasketPropertiesCollection $properties */
		$properties = $basketProperty->getCollection();
		$basketItem = $properties->getBasketItem();
		/** @var Sale\Basket $basket */
		$basket = $basketItem->getCollection();
		/** @var Sale\Order $order */
		$order = $basket->getOrder();

		$r = $order->save();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}
		elseif ($r->hasWarnings())
		{
			$result->addErrors($r->getWarnings());
		}

		return $result;
	}
}
