<?php

namespace Bitrix\Seo\Checkout\Services;

use Bitrix\Seo\Checkout\Request;
use Bitrix\Seo\Engine\Bitrix as EngineBitrix;

/**
 * Class RequestYookassa
 * @package Bitrix\Seo\Checkout\Services
 */
class RequestYookassa extends Request
{
	const TYPE_CODE = 'yookassa';

	/**
	 * Query.
	 *
	 * @param array $params Parameters.
	 * @return mixed
	 * @throws \Bitrix\Main\SystemException
	 */
	public function query(array $params = array())
	{
		$methodName = 'checkout.yookassa.'.$params['methodName'];
		$parameters = isset($params['parameters']) ? $params['parameters'] : [];
		$engine = new EngineBitrix();
		if (!$engine->isRegistered())
		{
			return false;
		}

		if (!$engine->getInterface())
		{
			return false;
		}

		$response = $engine->getInterface()->getTransport()->call($methodName, $parameters);
		return ((isset($response['result']['RESULT']) && $response['result']['RESULT'])
			? $response['result']['RESULT']
			: []
		);
	}
}