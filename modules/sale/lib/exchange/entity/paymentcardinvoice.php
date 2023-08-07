<?php

namespace Bitrix\Sale\Exchange\Entity;


use Bitrix\Sale\Exchange\EntityType;

class PaymentCardInvoice extends PaymentInvoiceBase
{
	public function getOwnerTypeId()
	{
		return EntityType::INVOICE_PAYMENT_CARD_TRANSACTION;
	}
}