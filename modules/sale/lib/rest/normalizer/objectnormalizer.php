<?php


namespace Bitrix\Sale\Rest\Normalizer;

use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\ShipmentItem;
use Bitrix\Sale\ShipmentItemStore;

/**
 * Class ObjectNormalizer
 * @package Bitrix\Sale\Rest\Normalizer
 * нотация всех ключей в результате должна быть SNAKE_CASE
 */
class ObjectNormalizer
{
	protected $externalFields;
	protected $fields;
	protected $order;

	public function __construct(array $data=[])
	{
		$this->externalFields = $data;
	}

	public function init(\Bitrix\Sale\Order $order)
	{
		$this->order = $order;
		return $this;
	}

	public function orderNormalize()
	{
		$externalFields = isset($this->externalFields['ORDER'][$this->getOrder()->getInternalId()])?
			$this->externalFields['ORDER'][$this->getOrder()->getInternalId()]:[];

		$this->fields['ORDER'] = array_merge(
			$externalFields,
			$this->getOrder()->getFieldValues()
		);
		return $this;
	}
	public function basketNormalize()
	{
		$r=[];
		/** @var BasketItem $item */
		foreach ($this->getOrder()->getBasket() as $item)
		{
			$fields = $item->getFieldValues();
			$externalFields = isset($this->externalFields['BASKET']['ITEMS'][$item->getInternalIndex()])?
				$this->externalFields['BASKET']['ITEMS'][$item->getInternalIndex()]:[];

			$props = [];
			foreach ($item->getPropertyCollection() as $property)
			{
				$props[] = array_merge(
					$property->getFieldValues()
				);
			}

			$r[$item->getInternalIndex()] = array_merge(
					$externalFields,
					$fields,
					['PROPERTIES'=>$props]
			);
		}
		$this->fields['ORDER']['BASKET_ITEMS']=$r;
		return $this;
	}
	public function propertiesValueNormalize()
	{
		$r=[];
		$propertyCollection = $this->getOrder()->getPropertyCollection();
		/** @var PropertyValue $property */
		foreach ($propertyCollection as $property)
		{
			$externalFields = isset($this->externalFields['PROPERTIES'][$property->getInternalIndex()])?
				$this->externalFields['PROPERTIES'][$property->getInternalIndex()]:[];

			$r[$property->getInternalIndex()] = array_merge(
					$externalFields,
					$property->getFieldValues()
			);
		}
		$this->fields['ORDER']['PROPERTY_VALUES']=$r;
		return $this;
	}
	public function paymentsNormalize()
	{
		$r=[];
		/** @var Payment $payment */
		foreach($this->getOrder()->getPaymentCollection() as $payment)
		{
			$externalFields = isset($this->externalFields['PAYMENTS'][$payment->getInternalIndex()])?
				$this->externalFields['PAYMENTS'][$payment->getInternalIndex()]:[];

			$r[$payment->getInternalIndex()] = array_merge(
					$externalFields,
					$payment->getFieldValues()
			);
		}
		$this->fields['ORDER']['PAYMENTS']=$r;
		return $this;
	}
	public function shipmentsNormalize()
	{
		$r=[];
		/** @var Shipment $shipment */
		foreach ($this->getOrder()->getShipmentCollection() as $shipment)
		{
			// рест магазина не оперирует системными отгрузками
			if($shipment->isSystem())
				continue;

			$basketItems = [];
			/** @var ShipmentItem$shipmentItem */
			foreach ($shipment->getShipmentItemCollection() as $shipmentItem)
			{
				$stores=[];
				/** @var ShipmentItemStore $shipmentItemStore */
				foreach ($shipmentItem->getShipmentItemStoreCollection() as $shipmentItemStore)
				{
					$externalFieldsSIS = isset($this->externalFields['SHIPMENTS'][$shipment->getInternalIndex()]['SHIPMENT_ITEMS'][$shipmentItem->getInternalIndex()]['STORE'][$shipmentItemStore->getInternalIndex()])?
						$this->externalFields['SHIPMENTS'][$shipment->getInternalIndex()]['SHIPMENT_ITEMS'][$shipmentItem->getInternalIndex()]['STORE'][$shipmentItemStore->getInternalIndex()]:[];

					$stores[] = array_merge(
						$externalFieldsSIS,
						$shipmentItemStore->getFieldValues()
					);
				}

				$externalFieldsSI = isset($this->externalFields['SHIPMENTS'][$shipment->getInternalIndex()]['SHIPMENT_ITEMS'][$shipmentItem->getInternalIndex()])?
					$this->externalFields['SHIPMENTS'][$shipment->getInternalIndex()]['SHIPMENT_ITEMS'][$shipmentItem->getInternalIndex()]:[];

				$basketItems[] = array_merge(
						$externalFieldsSI,
						$shipmentItem->getFieldValues(),
						['STORES'=>$stores]
				);

			}

			$externalFields = isset($this->externalFields['SHIPMENTS'][$shipment->getInternalIndex()])?
				$this->externalFields['SHIPMENTS'][$shipment->getInternalIndex()]:[];

			$r[$shipment->getInternalIndex()] = array_merge(
					$externalFields,
					$shipment->getFieldValues(),
					['SHIPMENT_ITEMS'=>$basketItems]
			);
		}
		$this->fields['ORDER']['SHIPMENTS']= $r;
		return $this;
	}
	public function applyDiscountNormalize()
	{
		$list = $this->getOrder()
			->getDiscount()
			->getApplyResult(true);
		if(is_array($list) && !empty($list))
		{
			$this->fields['ORDER']['DISCOUNTS'] = $list;
		}
		return $this;
	}
	public function taxNormalize()
	{
		$list = $this->getOrder()
			->getTax()
			->getTaxList();
		if(is_array($list) && !empty($list))
		{
			foreach ($list as $tax)
				$this->fields['ORDER']['TAXES'][] = $tax;
		}
		return $this;
	}
	public function tradeBindingsNormalize()
	{
		$r=[];
		/** @var \Bitrix\Sale\TradeBindingEntity $item */
		foreach($this->getOrder()->getTradeBindingCollection() as $item)
		{
			$externalFields = isset($this->externalFields['TRADE_BINDINGS'][$item->getInternalIndex()])?
				$this->externalFields['TRADE_BINDINGS'][$item->getInternalIndex()]:[];

			$r[] = array_merge(
				$externalFields,
				$item->getFieldValues()
			);
		}

		$this->fields['ORDER']['TRADE_BINDINGS']=$r;
		return $this;
	}

	public function getFields()
	{
		return $this->fields;
	}
	/**
	 * @return \Bitrix\Sale\Order
	 */
	protected function getOrder()
	{
		return $this->order;
	}
}