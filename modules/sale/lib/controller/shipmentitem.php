<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale;
use Bitrix\Sale\Result;
use Bitrix\Sale\ShipmentCollection;
use Bitrix\Sale\ShipmentItemCollection;

class ShipmentItem extends Controller
{
	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			Sale\ShipmentItem::class,
			'shipmentItem',
			function($className, $id) {

				$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

				/** @var Sale\ShipmentItem $shipmentItemClass */
				$shipmentItemClass = $registry->getShipmentItemClassName();

				$si = $shipmentItemClass::getList([
					'select'=>['ORDER_DELIVERY_ID'],
					'filter'=>['ID'=>$id]
				]);

				if($siRow = $si->fetch())
				{
					/** @var Sale\Shipment $shipmentClass */
					$shipmentClass = $registry->getShipmentClassName();

					$r = $shipmentClass::getList([
						'select'=>['ORDER_ID'],
						'filter'=>['ID'=>$siRow['ORDER_DELIVERY_ID']]
					]);

					if($row = $r->fetch())
					{
						/** @var Sale\Order $orderClass */
						$orderClass = $registry->getOrderClassName();

						$order = $orderClass::load($row['ORDER_ID']);
						/** @var \Bitrix\Sale\Shipment $shipment */
						$shipment = $order->getShipmentCollection()->getItemById($siRow['ORDER_DELIVERY_ID']);
						$shipmentItem = $shipment->getShipmentItemCollection()->getItemById($id);

						if ($shipmentItem)
						{
							return $shipmentItem;
						}
					}
				}

				$this->addError(new Error('shipment item is not exists', 201240400001));
				return null;
			}
		);
	}

	//region Actions
	public function getFieldsAction()
	{
		$entity = new \Bitrix\Sale\Rest\Entity\ShipmentItem();
		return ['SHIPMENT_ITEM'=>$entity->prepareFieldInfos(
			$entity->getFields()
		)];
	}

	public function getAction(\Bitrix\Sale\ShipmentItem $shipmentItem)
	{
		return ['SHIPMENT_ITEM'=>$this->get($shipmentItem)];
	}

	public function listAction($select=[], $filter=[], $order=[], PageNavigation $pageNavigation)
	{
		$select = empty($select)? ['*']:$select;
		$order = empty($order)? ['ID'=>'ASC']:$order;

		$shipmentItems = \Bitrix\Sale\ShipmentItem::getList(
			[
				'select'=>$select,
				'filter'=>$filter,
				'order'=>$order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit()
			]
		)->fetchAll();

		return new Page('SHIPMENT_ITEMS', $shipmentItems, function() use ($select, $filter)
		{
			return count(
				\Bitrix\Sale\ShipmentItem::getList(['select'=>$select, 'filter'=>$filter])->fetchAll()
			);
		});
	}

	public function addAction(array $fields)
	{
		$result = new Result();

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);


		$basketId = $fields['BASKET_ID'];
		$shipmentId = $fields['ORDER_DELIVERY_ID'];

		unset($fields['ORDER_DELIVERY_ID'], $fields['BASKET_ID']);

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
			if($basketItem instanceof BasketItem)
			{
				/** @var ShipmentCollection $collection */
				$collection = $order->getShipmentCollection();
				$shipment = $collection->getItemById($shipmentId);
				if($shipment instanceof \Bitrix\Sale\Shipment)
				{
					$shipmentItemCollection = $shipment->getShipmentItemCollection();
					if($shipmentItemCollection->isExistBasketItem($basketItem) == false)
					{
						/** @var \Bitrix\Sale\ShipmentItem $shipmentItem */
						$shipmentItem = $shipmentItemCollection->createItem($basketItem);
						$result = $shipmentItem->setFields($fields);
						if($result->isSuccess() && $result->hasWarnings() == false)
						{
							$r = $this->save($shipmentItem);
							if(!$r->isSuccess())
							{
								$result->addErrors($r->getErrors());
							}
						}
					}
					else
					{
						$result->addError(new Error('Duplicate entry for key [basketId, orderDeliveryId]', 201250000001));
					}
				}
				else
				{
					$result->addError(new Error('shipment not exists', 201240400002));
				}
			}
		}
		else
		{
			$result->addError(new Error('shipment not exists', 201240400003));
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
			return ['SHIPMENT_ITEM'=>$this->get($shipmentItem)];
		}
	}

	public function updateAction(\Bitrix\Sale\ShipmentItem $shipmentItem, array $fields)
	{
		$r = $shipmentItem->setFields($fields);

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
			$r = $this->save($shipmentItem);
			if(!$r->isSuccess())
			{
				$this->addErrors($r->getErrors());
				return null;
			}
			else
			{
				return ['SHIPMENT_ITEM'=>$this->get($shipmentItem)];
			}
		}
	}

	public function deleteAction(\Bitrix\Sale\ShipmentItem $shipmentItem)
	{
		$r = $shipmentItem->delete();

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
			$r = $this->save($shipmentItem);
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

	protected function get(\Bitrix\Sale\ShipmentItem $shipmentItem, array $fields=[])
	{
		/** @var ShipmentItemCollection $collectionShipmentItems */
		$collectionShipmentItems = $shipmentItem->getCollection();
		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = $collectionShipmentItems->getShipment();
		/** @var ShipmentCollection $collectionShipments */
		$collectionShipments = $shipment->getCollection();
		$order = $collectionShipments->getOrder();

		$shipments = $this->toArray($order, $fields)['ORDER']['SHIPMENTS'];
		foreach ($shipments as $shipment)
		{
			foreach ($shipment['SHIPMENT_ITEMS'] as $item)
			{
				if($item['ID']==$shipmentItem->getId())
				{
					return $item;
				}
			}
		}
		return [];
	}

	private function save(\Bitrix\Sale\ShipmentItem $shipmentItem)
	{
		$result = new Result();
		/** @var ShipmentItemCollection $collectionShipmentItems */
		$collectionShipmentItems = $shipmentItem->getCollection();
		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = $collectionShipmentItems->getShipment();
		/** @var ShipmentCollection $collectionShipments */
		$collectionShipments = $shipment->getCollection();
		$order = $collectionShipments->getOrder();

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