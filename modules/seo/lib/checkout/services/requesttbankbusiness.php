<?php

namespace Bitrix\Seo\Checkout\Services;

use Bitrix\Seo\Checkout\Request;
use Bitrix\Seo\Engine\Bitrix as EngineBitrix;

/**
 * Class RequestTBankBusiness
 * @package Bitrix\Seo\Checkout\Services
 */
class RequestTBankBusiness extends Request
{
	const TYPE_CODE = 'tbankbusiness';

	/**
	 * Query.
	 *
	 * @param array $params Parameters.
	 * @return mixed
	 * @throws \Bitrix\Main\SystemException
	 */
	public function query(array $params = [])
	{
		if (empty($params['methodName']))
		{
			return false;
		}

		$methodName = 'checkout.tbankbusiness.' . $params['methodName'];
		$parameters = $params['parameters'] ?? [];

		$engine = new EngineBitrix();

		if (!$engine->isRegistered())
		{
			return false;
		}

		$engineInterface = $engine->getInterface();

		if (!$engineInterface)
		{
			return false;
		}

		$response = $engineInterface->getTransport()->call($methodName, $parameters);

		return (
			(
				isset($response['result']['RESULT'])
				&& $response['result']['RESULT']
			)
				? $response['result']['RESULT']
				: []
		);
	}
}
