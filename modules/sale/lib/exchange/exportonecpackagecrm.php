<?php

namespace Bitrix\Sale\Exchange;


use Bitrix\Sale\Exchange\Entity\Invoice;
use Bitrix\Sale\Exchange\Entity\PaymentInvoiceBase;
use Bitrix\Sale\Exchange\Entity\ShipmentInvoice;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Shipment;

class ExportOneCPackageCRM extends ExportOneCPackageSale
{

	protected function resolveEntityTypeId(\Bitrix\Sale\Internals\Entity $entity)
	{
		$typeId = EntityType::UNDEFINED;

		if($entity instanceof Order)
			$typeId = Invoice::resolveEntityTypeId($entity);
		elseif ($entity instanceof Payment)
			$typeId = PaymentInvoiceBase::resolveEntityTypeId($entity);
		elseif ($entity instanceof Shipment)
			$typeId = ShipmentInvoice::resolveEntityTypeId($entity);

		return $typeId;
	}

	static protected function getParentEntityTypeId()
	{
		return EntityType::INVOICE;
	}

	static protected function getShipmentEntityTypeId()
	{
		return EntityType::INVOICE_SHIPMENT;
	}

	static protected function getPaymentCardEntityTypeId()
	{
		return EntityType::INVOICE_PAYMENT_CARD_TRANSACTION;
	}

	static protected function getPaymentCashEntityTypeId()
	{
		return EntityType::INVOICE_PAYMENT_CASH;
	}

	static protected function getPaymentCashLessEntityTypeId()
	{
		return EntityType::INVOICE_PAYMENT_CASH_LESS;
	}

	/**
	 * @return string
	 */
	protected function getShemVersion()
	{
		return static::SHEM_VERSION_3_1;
	}
}