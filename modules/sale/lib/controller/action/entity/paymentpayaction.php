<?php


namespace Bitrix\Sale\Controller\Action\Entity;

use Bitrix\Main;
use Bitrix\Sale;

class PaymentPayAction extends BaseAction
{
	private function checkParams(array $fields): Sale\Result
	{
		$result = new Sale\Result();

		if (empty($fields['ORDER_ID']) || (int)$fields['ORDER_ID'] <= 0)
		{
			$this->addError(new Main\Error('orderId not found', 202440400001));
		}

		if (empty($fields['ACCESS_CODE']))
		{
			$this->addError(new Main\Error('accessCode not found', 202440400002));
		}

		return $result;
	}

	public function run(array $fields)
	{
		$checkParamsResult = $this->checkParams($fields);
		if (!$checkParamsResult->isSuccess())
		{
			$this->addErrors($checkParamsResult->getErrors());
			return null;
		}

		$params = [
			'ORDER_ID' => (int)$fields['ORDER_ID'],
			'RETURN_URL' => $fields['RETURN_URL'],
			'ACCESS_CODE' => $fields['ACCESS_CODE']
		];

		return new Main\Engine\Response\Component('bitrix:salescenter.payment.pay', 'checkout_form', $params);
	}
}
