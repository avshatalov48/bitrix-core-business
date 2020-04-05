<?php

namespace Bitrix\Sale\Exchange\Entity;


use Bitrix\Crm\Invoice\EntityMarker;
use Bitrix\Sale\Exchange\EntityType;
use Bitrix\Sale\Order;

class PaymentInvoiceBase extends PaymentImport
{
	/**
	 * @param $order
	 * @param $entity
	 * @param $result
	 */
	protected function addMarker($invoice, $entity, $result)
	{
		EntityMarker::addMarker($invoice, $entity, $result);
	}

	/**
	 * @param array $fields
	 * @return Order|null
	 */
	protected function loadParentEntity(array $fields)
	{
		$entity = null;

		if(!empty($fields['ID']))
		{
			/** @var Order $entity */
			$entity = \Bitrix\Crm\Invoice\Invoice::load($fields['ID']);
		}
		return $entity;
	}

	/**
	 * @param string $type
	 * @return int
	 */
	static public function resolveEntityTypeIdByCodeType($type)
	{
		switch($type)
		{
			case 'Y':
				$resolveType = EntityType::INVOICE_PAYMENT_CASH;
				break;
			case 'N':
				$resolveType = EntityType::INVOICE_PAYMENT_CASH_LESS;
				break;
			case 'A':
				$resolveType = EntityType::INVOICE_PAYMENT_CARD_TRANSACTION;
				break;
			default;
				$resolveType = EntityType::UNDEFINED;
		}
		return $resolveType;
	}
}