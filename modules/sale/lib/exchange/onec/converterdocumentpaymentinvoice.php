<?php


namespace Bitrix\Sale\Exchange\OneC;


class ConverterDocumentPaymentInvoice extends ConverterDocumentPayment
{
	public function getPaySystemId($fields)
	{
		$paySystemId = 0;
		if(isset($fields['PAY_SYSTEM_ID']))
		{
			$paySystemId = $fields['PAY_SYSTEM_ID'];
		}

		return $paySystemId;
	}
}