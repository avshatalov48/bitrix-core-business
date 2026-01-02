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

		$engine = \Bitrix\Seo\Service::getEngine($this);
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

		if (isset($params['listenHttpErrors']) && $params['listenHttpErrors'])
		{
			$transport->listenHttpErrors();
		}

		if (isset($params['streamTimeout']))
		{
			$transport->setStreamTimeout((int)$params['streamTimeout']);
		}

		$response = $transport->call($methodName, $parameters);
		if (isset($response['result']['RESULT']))
		{
			return $response['result']['RESULT'];
		}

		if (isset($response['error']))
		{
			throw new InvalidOperationException($response['error_description'] ? $response['error_description'] : $response['error']);
		}
		return [];
	}

	public function getServiceUrl(string $sourceUrl): string
	{
		$domains = [
			'tech' => 'bitrix24.tech',
			'info' => 'bitrix.info',
		];

		$domain = \Bitrix\Main\Application::getInstance()->getLicense()->getRegion() == 'ru'
			? $domains['tech']
			: $domains['info']
		;

		return str_replace(
			array_values($domains),
			[$domain, $domain],
			$sourceUrl,
		);
	}
}