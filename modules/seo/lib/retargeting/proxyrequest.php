<?php

namespace Bitrix\Seo\Retargeting;

use Bitrix\Main\InvalidOperationException;
use Bitrix\Seo\Engine;

class ProxyRequest extends Request
{
	const REST_METHOD_PREFIX = '';

	/**
	 * Request through cloud-adv service
	 *
	 * @param array $params Request params.
	 * @return array|bool
	 * @throws \Bitrix\Main\SystemException
	 */
	public function query(array $params = array())
	{
		if ($this->useDirectQuery)
		{
			return $this->directQuery($params);
		}

		$methodName = static::REST_METHOD_PREFIX . '.' . $params['methodName'];
		$parameters = $params['parameters'];
		$engine = new Engine\Bitrix();
		if (!$engine->isRegistered())
		{
			return false;
		}
		$parameters['proxy_client_id'] = $this->getAuthAdapter()->getClientId();
		$parameters['lang'] = LANGUAGE_ID;

		if (!$engine->getInterface())
		{
			return false;
		}

		$transport = $engine->getInterface()->getTransport();
		if (isset($params['timeout']))
		{
			$transport->setTimeout($params['timeout']);
		}

		if (isset($params['streamTimeout']))
		{
			$transport->setStreamTimeout((int)$params['streamTimeout']);
		}

		$response = $transport->call($methodName, $parameters);
		if ($response['result']['RESULT'])
		{
			return $response['result']['RESULT'];
		}
		if ($response['error'])
		{
			throw new InvalidOperationException($response['error_description'] ? $response['error_description'] : $response['error']);
		}
		return [];
	}
}