<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
use Bitrix\Crm\Integration;

PaySystem\Manager::includeHandler('orderdocument');
Loc::loadMessages(__FILE__);

/**
 * Class InvoiceDocumentHandler
 * @package Sale\Handlers\PaySystem
 */
class InvoiceDocumentHandler extends OrderDocumentHandler
{
	/**
	 * @return string
	 */
	protected static function getDataProviderClass()
	{
		return Integration\DocumentGenerator\DataProvider\Invoice::class;
	}

	/**
	 * @param $payment
	 * @return mixed
	 */
	protected function getInvoiceNumber(Payment $payment)
	{
		$invoice = $payment->getOrder();

		return $invoice->getField('ACCOUNT_NUMBER');
	}

}