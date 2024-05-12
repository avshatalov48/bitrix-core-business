<?php

namespace Bitrix\Im\V2\Call;

use Bitrix\Main\Result;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Service\MicroService\BaseSender;

class ControllerClient extends BaseSender
{
	private const SERVICE_MAP = [
		'ru' => 'https://videocalls.bitrix.info',
		'eu' => 'https://videocalls-de.bitrix.info',
		'us' => 'https://videocalls-us.bitrix.info',
	];
	private const REGION_RU = ['ru', 'by', 'kz'];
	private const REGION_EU = ['de', 'eu', 'fr', 'it', 'pl', 'tr', 'uk'];

	/**
	 * Returns controller service endpoint url.
	 *
	 * @return string
	 * @param string $region Portal region.
	 */
	public function getEndpoint(string $region): string
	{
		$endpoint = Option::get('im', 'call_server_url');

		if (empty($endpoint))
		{
			if (in_array($region, self::REGION_RU, true))
			{
				$endpoint = self::SERVICE_MAP['ru'];
			}
			elseif (in_array($region, self::REGION_EU, true))
			{
				$endpoint = self::SERVICE_MAP['eu'];
			}
			else
			{
				$endpoint = self::SERVICE_MAP['us'];
			}
		}
		elseif (!(mb_strpos($endpoint, 'https://') === 0 || mb_strpos($endpoint, 'http://') === 0))
		{
			$endpoint = 'https://' . $endpoint;
		}

		return $endpoint;
	}


	/**
	 * Returns API endpoint for the service.
	 *
	 * @return string
	 */
	protected function getServiceUrl(): string
	{
		$region = \Bitrix\Main\Application::getInstance()->getLicense()->getRegion() ?: 'ru';

		return $this->getEndpoint($region);
	}

	/**
	 * @see \Bitrix\CallController\Controller\InternalApi::createCallAction
	 * @param string $callUuid
	 * @param string $secretKey
	 * @param int $initiatorId
	 * @param int $callId
	 * @return Result
	 */
	public function createCall(string $callUuid, string $secretKey, int $initiatorId, int $callId): Result
	{
		return $this->performRequest(
			'callcontroller.Controller.InternalApi.createCall',
			[
				'uuid' => $callUuid,
				'secretKey' => $secretKey,
				'initiatorUserId' => $initiatorId,
				'callId' => $callId,
			]
		);
	}
}