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
			Sale\BasketPropertyItem::class,
			'basketProperty',
			function($className, $id) {
				$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

				/** @var BasketPropertyItem $basketPropertyClass */
				$basketPropertyClass = $registry->getBasketPropertyItemClassName();

				$b = $basketPropertyClass::getList([
					'select'=>['BASKET_ID'],
					'filter'=>['ID'=>$id]
				]);

				if($bRow = $b->fetch())
				{
					/** @var Sale\Basket $basketClass */
					$basketClass = $registry->getBasketClassName();

					$r = $basketClass::getList([
						'select'=>['ORDER_ID'],
						'filter'=>['ID'=>$bRow['BASKET_ID']]
					]);

					if($row = $r->fetch())
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
		$entity = new \Bitrix\Sale\Rest\Entity\BasketProperties();
		return ['BASKET_PROPERTIES'=>$entity->prepareFieldInfos(
			$entity->getFields()
		)];
	}

	public function listAction(PageNavigation $pageNavigation, array $select = [], array $filter = [], array $order = []): Page
	{
		$select = empty($select) ? ['*'] : $select;
		$order = empty($order) ? ['ID' => 'ASC'] : $order;

		$items = \Bitrix\Sale\Internals\BasketPropertyTable::getList(
			[
				'select' => $select,
				'filter' => $filter,
				'order' => $order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit(),
			]
		)->fetchAll();

		return new Page('BASKET_PROPERTIES', $items, function() use ($filter)
		{
			return count(
				\Bitrix\Sale\Internals\BasketPropertyTable::getList(['filter'=>$filter])->fetchAll()
			);
		});
	}

	public function getAction(\Bitrix\Sale\BasketPropertyItem $basketProperty)
	{
		return ['BASKET_PROPERTY'=>$this->get($basketProperty)];
	}

	public function addAction(array $fields)
	{
		$result = new Result();

		$basketId = $fields['BASKET_ID'];

		unset($fields['BASKET_ID']);

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var Sale\Basket $basketClass */
		$basketClass = $registry->getBasketClassName();

		$r = $basketClass::getList([
			'select'=>['ORDER_ID'],
			'filter'=>['ID'=>$basketId]
		]);

		if($row = $r->fetch())
		{
			/** @var Sale\Order $orderClass */
			$orderClass = $registry->getOrderClassName();

			$order = $orderClass::load($row['ORDER_ID']);
			$basketItem = $order->getBasket()->getItemByBasketCode($basketId);
			if($basketItem instanceof \Bitrix\Sale\BasketItem)
			{
				/** @var BasketPropertiesCollection $propertyCollection */
				$propertyCollection = $basketItem->getPropertyCollection();
				/** @var BasketPropertyItem $basketProperty */
				$basketProperty = $propertyCollection->createItem();
				$result = $basketProperty->setFields($fields);

				if($result->isSuccess() && $result->hasWarnings() == false)
				{
					$r = $this->save($basketProperty);
					if(!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}
				}
			}
			else
			{
				$result->addError(new Error('basket item not exists', 200240400001));
			}
		}
		else
		{
			$result->addError(new Error('basket item not exists', 200240400002));
		}

		if(!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
			return null;
		}
		elseif($result->hasWarnings())
		{
			$this->addErrors($result->getWarnings());
			return null;
		}
		else
		{
			return ['BASKET_PROPERTY'=>$this->get($basketProperty)];
		}
	}

	public function updateAction(\Bitrix\Sale\BasketPropertyItem $basketProperty, array $fields)
	{
		$r = $basketProperty->setFields($fields);

		if($r->isSuccess() == false)
		{
			$this->addErrors($r->getErrors());
			return null;
		}
		elseif($r->hasWarnings())
		{
			$this->addErrors($r->getWarnings());
			return null;
		}
		else
		{
			$r = $this->save($basketProperty);
			if(!$r->isSuccess())
			{
				$this->addErrors($r->getErrors());
				return null;
			}
			else
			{
				return ['BASKET_PROPERTY'=>$this->get($basketProperty)];
			}
		}
	}

	public function deleteAction(\Bitrix\Sale\BasketPropertyItem $basketProperty)
	{
		$r = $basketProperty->delete();

		if($r->isSuccess() == false)
		{
			$this->addErrors($r->getErrors());
			return null;
		}
		elseif($r->hasWarnings())
		{
			$this->addErrors($r->getWarnings());
			return null;
		}
		else
		{
			$r = $this->save($basketProperty);
			if(!$r->isSuccess())
			{
				$this->addErrors($r->getErrors());
				return null;
			}
			else
			{
				return true;
			}
		}
	}
	//endregion

	protected function get(\Bitrix\Sale\BasketPropertyItem $basketProperty, array $fields=[])
	{
		/** @var BasketPropertiesCollection $properties */
		$properties = $basketProperty->getCollection();
		$basketItem = $properties->getBasketItem();
		/** @var Basket $basket */
		$basket = $basketItem->getCollection();
		/** @var \Bitrix\Sale\Order $order */
		$order = $basket->getOrder();

		$basketItems = $this->toArray($order, $fields)['ORDER']['BASKET_ITEMS'];
		foreach ($basketItems as $item)
		{
			foreach ($item['PROPERTIES'] as $property)
			{
				if($property['ID']==$basketProperty->getId())
				{
					return $property;
				}
			}
		}
		return [];
	}

	private function save(\Bitrix\Sale\BasketPropertyItem $basketProperty)
	{
		$result = new Result();
		/** @var BasketPropertiesCollection $properties */
		$properties = $basketProperty->getCollection();
		$basketItem = $properties->getBasketItem();
		/** @var Basket $basket */
		$basket = $basketItem->getCollection();
		/** @var \Bitrix\Sale\Order $order */
		$order = $basket->getOrder();

		$r = $order->save();
		if(!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}
		elseif($r->hasWarnings())
		{
			$result->addErrors($r->getWarnings());
		}
		return $result;
	}
}