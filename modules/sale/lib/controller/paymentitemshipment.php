<?php

namespace Bitrix\Sale\Controller;

use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Internals\CollectionFilterIterator;
use Bitrix\Sale\PayableItem;
use Bitrix\Sale\PayableItemCollection;
use Bitrix\Sale\PayableShipmentItem;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\Registry;

class PaymentItemShipment extends ControllerBase
{
	public function getPrimaryAutoWiredParameter(): ExactParameter
	{
		return new ExactParameter(
			PayableShipmentItem::class,
			'paymentItem',
			function($className, $id) {

				$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);

				/** @var PayableItemCollection $payableItemCollection */
				$payableItemCollection = $registry->get(Registry::ENTITY_PAYABLE_ITEM_COLLECTION);
				$pi = $payableItemCollection::getList([
					'select'=>['PAYMENT_ID'],
					'filter'=>[
						'ID'=>$id,
						'ENTITY_TYPE'=>PayableShipmentItem::getEntityType()
					],
				]);

				if($piRow = $pi->fetch())
				{
					$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
					/** @var \Bitrix\Sale\Payment $paymentClass */
					$paymentClass = $registry->getPaymentClassName();

					$r = $paymentClass::getList([
						'select'=>['ORDER_ID'],
						'filter'=>['ID'=>$piRow['PAYMENT_ID']]
					]);

					if($row = $r->fetch())
					{
						/** @var \Bitrix\Sale\Order $orderClass */
						$orderClass = $registry->getOrderClassName();

						$order = $orderClass::load($row['ORDER_ID']);
						$payment = $order->getPaymentCollection()->getItemById($piRow['PAYMENT_ID']);
						$payableItemCollection = $payment->getPayableItemCollection();
						/** @var PayableItem $item */
						foreach ($payableItemCollection as $item)
						{
							if($item->getId() == $id)
							{
								return $item;
							}
						}
					}
					else
					{
						$this->addError(new Error('payment is not exists', 200640400001));
					}
				}

				$this->addError(new Error('payment item is not exists', 201240400001));
				return null;
			}
		);
	}

	//region Actions
	public function getFieldsAction(): array
	{
		$view = $this->getViewManager()
			->getView($this);

		return ['PAYMENT_ITEM_SHIPMENT'=>$view->prepareFieldInfos(
			$view->getFields()
		)];
	}

	public function getAction(PayableShipmentItem $paymentItem): array
	{
		return ['PAYMENT_ITEM_SHIPMENT'=>$paymentItem->toArray()];
	}

	public function listAction($select=[], $filter=[], $order=[], PageNavigation $pageNavigation): Page
	{
		$select = empty($select)? ['*']:$select;
		$order = empty($order)? ['ID'=>'ASC']:$order;

		$filter['ENTITY_TYPE'] = PayableShipmentItem::getEntityType();

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		/** @var PayableItemCollection $payableItemCollection */
		$payableItemCollection = $registry->get(Registry::ENTITY_PAYABLE_ITEM_COLLECTION);
		$paymentItems = $payableItemCollection::getList(
			[
				'select'=>$select,
				'filter'=>$filter,
				'order'=>$order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit()
			]
		)->fetchAll();

		return new Page('PAYMENT_ITEMS_SHIPMENT', $paymentItems, function() use ($filter)
		{
			$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
			/** @var PayableItemCollection $payableItemCollection */
			$payableItemCollection = $registry->get(Registry::ENTITY_PAYABLE_ITEM_COLLECTION);

			return (int) $payableItemCollection::getList([
				'select' => ['CNT'],
				'filter'=>$filter,
				'runtime' => [
					new ExpressionField('CNT', 'COUNT(ID)')
				]
			])->fetch()['CNT'];
		});
	}

	public function addAction(array $fields): ?array
	{
		$result = new Result();
		$paymentItem = null;

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);

		$shipmentId = $fields['SHIPMENT_ID'];
		$paymentId = $fields['PAYMENT_ID'];

		unset($fields['PAYMENT_ID'], $fields['SHIPMENT_ID']);

		/** @var \Bitrix\Sale\Shipment $shipmentClass */
		$shipmentClass = $registry->getShipmentClassName();

		$r = $shipmentClass::getList([
			'select'=>['ORDER_ID'],
			'filter'=>['ID'=>$shipmentId],
		]);

		if($row = $r->fetch())
		{
			/** @var \Bitrix\Sale\Order $orderClass */
			$orderClass = $registry->getOrderClassName();

			$order = $orderClass::load($row['ORDER_ID']);
			$shipment = $order->getShipmentCollection()->getItemById($shipmentId);
			if($shipment instanceof \Bitrix\Sale\Shipment)
			{
				/** @var PaymentCollection $collection */
				$collection = $order->getPaymentCollection();
				$payment = $collection->getItemById($paymentId);
				if($payment instanceof \Bitrix\Sale\Payment)
				{
					$paymentItems = $payment->getPayableItemCollection()->getShipments();
					if($this->isExistShipment($paymentItems, $shipment) == false)
					{
						/** @var PayableShipmentItem $paymentItem */
						$paymentItem = $payment->getPayableItemCollection()->createItemByShipment($shipment);
						$result = $paymentItem->setFields($fields);
						if($result->isSuccess() && $result->hasWarnings() == false)
						{
							$r = $this->save($paymentItem);
							if(!$r->isSuccess())
							{
								$result->addErrors($r->getErrors());
							}
						}
					}
					else
					{
						$result->addError(new Error('Duplicate entry for key [shipmentId, paymentId]', 201250000001));
					}
				}
				else
				{
					$result->addError(new Error('payment not exists', 201240400002));
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
		elseif($paymentItem instanceof PayableItem)
		{
			return ['PAYMENT_ITEM_SHIPMENT'=>$paymentItem->toArray()];
		}
		else
		{
			return ['PAYMENT_ITEM_SHIPMENT'=>$paymentItem];
		}
	}

	public function updateAction(PayableShipmentItem $paymentItem, array $fields): ?array
	{
		$r = $paymentItem->setFields($fields);

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
			$r = $this->save($paymentItem);
			if(!$r->isSuccess())
			{
				$this->addErrors($r->getErrors());
				return null;
			}
			else
			{
				return ['PAYMENT_ITEM_SHIPMENT'=>$paymentItem->toArray()];
			}
		}
	}

	public function deleteAction(PayableShipmentItem $paymentItem): ?bool
	{
		$r = $paymentItem->delete();

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
			$r = $this->save($paymentItem);
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

	private function save(PayableShipmentItem $paymentItem): Result
	{
		$result = new Result();
		/** @var PayableItemCollection $collectionPaymentItems */
		$collectionPaymentItems = $paymentItem->getCollection();
		/** @var \Bitrix\Sale\Payment $payment */
		$payment = $collectionPaymentItems->getPayment();
		/** @var PaymentCollection $collectionPayments */
		$collectionPayments = $payment->getCollection();
		$order = $collectionPayments->getOrder();

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

	protected function isExistShipment(CollectionFilterIterator $paymentItems, \Bitrix\Sale\Shipment $shipment): bool
	{
		foreach ($paymentItems as $paymentItem)
		{
			/** @var \Bitrix\Sale\Shipment $entityShipment */
			$entityShipment = $paymentItem->getEntityObject();
			if ($shipment->getInternalIndex() === $entityShipment->getInternalIndex())
			{
				return true;
			}
		}
		return false;
	}

	protected function checkModifyPermissionEntity(): Result
	{
		$r = new Result();

		$saleModulePermissions = self::getApplication()->GetGroupRight("sale");
		if ($saleModulePermissions  < "W")
		{
			$r->addError(new Error('Access Denied', 200040300020));
		}
		return $r;
	}

	protected function checkReadPermissionEntity(): Result
	{
		$r = new Result();

		$saleModulePermissions = self::getApplication()->GetGroupRight("sale");
		if ($saleModulePermissions  == "D")
		{
			$r->addError(new Error('Access Denied', 200040300010));
		}
		return $r;
	}
}