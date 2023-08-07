<?php

namespace Bitrix\Sale\Exchange\Entity;


use Bitrix\Sale\Exchange\EntityType;

class PaymentCashLessInvoice extends PaymentInvoiceBase
{
	public function getOwnerTypeId()
	{
		return EntityType::INVOICE_PAYMENT_CASH_LESS;
	}
}