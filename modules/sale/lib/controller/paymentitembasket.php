<?php

namespace Bitrix\Sale\Controller;

use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Internals\CollectionFilterIterator;
use Bitrix\Sale\PayableBasketItem;
use Bitrix\Sale\PayableItem;
use Bitrix\Sale\PayableItemCollection;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\Registry;

class PaymentItemBasket extends ControllerBase
{
	public function getPrimaryAutoWiredParameter(): ExactParameter
	{
		return new ExactParameter(
			PayableBasketItem::class,
			'paymentItem',
			function($className, $id) {

				$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);

				/** @var PayableItemCollection $payableItemCollection */
				$payableItemCollection = $registry->get(Registry::ENTITY_PAYABLE_ITEM_COLLECTION);
				$pi = $payableItemCollection::getList([
					'select'=>['PAYMENT_ID'],
					'filter'=>[
						'ID'=>$id,
						'ENTITY_TYPE'=>PayableBasketItem::getEntityType()
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

		return ['PAYMENT_ITEM_BASKET'=>$view->prepareFieldInfos(
			$view->getFields()
		)];
	}

	public function getAction(PayableBasketItem $paymentItem): array
	{
		return ['PAYMENT_ITEM_BASKET'=>$paymentItem->toArray()];
	}

	public function listAction(PageNavigation $pageNavigation, array $select = [], array $filter = [], array $order = []): Page
	{
		$select = empty($select) ? ['*'] : $select;
		$order = empty($order) ? ['ID'=>'ASC'] : $order;

		$filter['ENTITY_TYPE'] = PayableBasketItem::getEntityType();

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		/** @var PayableItemCollection $payableItemCollection */
		$payableItemCollection = $registry->get(Registry::ENTITY_PAYABLE_ITEM_COLLECTION);
		$paymentItems = $payableItemCollection::getList(
			[
				'select' => $select,
				'filter' => $filter,
				'order' => $order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit(),
			]
		)->fetchAll();

		return new Page('PAYMENT_ITEMS_BASKET', $paymentItems, function() use ($filter)
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

		$basketId = $fields['BASKET_ID'];
		$paymentId = $fields['PAYMENT_ID'];

		unset($fields['PAYMENT_ID'], $fields['BASKET_ID']);

		/** @var Basket $basketClass */
		$basketClass = $registry->getBasketClassName();

		$r = $basketClass::getList([
			'select'=>['ORDER_ID'],
			'filter'=>['ID'=>$basketId]
		]);

		if($row = $r->fetch())
		{
			/** @var \Bitrix\Sale\Order $orderClass */
			$orderClass = $registry->getOrderClassName();

			$order = $orderClass::load($row['ORDER_ID']);
			$basketItem = $order->getBasket()->getItemByBasketCode($basketId);
			if($basketItem instanceof BasketItem)
			{
				/** @var PaymentCollection $collection */
				$collection = $order->getPaymentCollection();
				$payment = $collection->getItemById($paymentId);
				if($payment instanceof \Bitrix\Sale\Payment)
				{
					$paymentItems = $payment->getPayableItemCollection()->getBasketItems();
					if($this->isExistBasketItem($paymentItems, $basketItem) == false)
					{
						/** @var PayableBasketItem $paymentItem */
						$paymentItem = $payment->getPayableItemCollection()->createItemByBasketItem($basketItem);
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
						$result->addError(new Error('Duplicate entry for key [basketId, paymentId]', 201250000001));
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
			$result->addError(new Error('basket item not exists', 201240400003));
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
			return ['PAYMENT_ITEM_BASKET'=>$paymentItem->toArray()];
		}
		else
		{
			return ['PAYMENT_ITEM_BASKET'=>$paymentItem];
		}
	}

	public function updateAction(PayableBasketItem $paymentItem, array $fields): ?array
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
				return ['PAYMENT_ITEM_BASKET'=>$paymentItem->toArray()];
			}
		}
	}

	public function deleteAction(PayableBasketItem $paymentItem): ?bool
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

	private function save(PayableBasketItem $paymentItem): Result
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

	protected function isExistBasketItem(CollectionFilterIterator $paymentItems, BasketItem $basketItem): bool
	{
		foreach ($paymentItems as $paymentItem)
		{
			/** @var BasketItem $entityBasketItem */
			$entityBasketItem = $paymentItem->getEntityObject();
			if($entityBasketItem->getBasketCode() === $basketItem->getBasketCode())
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