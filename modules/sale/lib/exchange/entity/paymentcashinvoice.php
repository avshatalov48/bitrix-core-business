<?php

namespace Bitrix\Sale\Exchange\Entity;


use Bitrix\Sale\Exchange\EntityType;

class PaymentCashInvoice extends PaymentInvoiceBase
{
	public function getOwnerTypeId()
	{
		return EntityType::INVOICE_PAYMENT_CASH;
	}
}