<?php

namespace Bitrix\Sale\Helpers\Admin\Blocks\Archive\View1;

use Bitrix\Sale\Helpers\Admin\Blocks\OrderPayment as Block,
	Bitrix\Sale\Helpers\Admin\Blocks\Archive\Template;

class OrderPayment extends Template
{
	protected $name = "payment";
	
	/**
	 * @return string $result
	 */
	public function buildBlock()
	{
		$result = "";
		$index = 0;
		$paymentCollection = $this->order->getPaymentCollection();

		foreach ($paymentCollection as $payment)
		{
			$result .= Block::getView($payment, $index++, "archive");
		}

		return $result;
	}
}