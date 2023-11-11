<?php

namespace Bitrix\Im\V2\Call;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Service\MicroService\BaseSender;
class ControllerClient extends BaseSender
{
	protected $endpoint;
	protected $customEndpoint;

	public function __construct(string $endpoint = null)
	{
		parent::__construct();

		if ($endpoint)
		{
			$this->customEndpoint = $endpoint;
		}
	}

	protected function getEndpoint()
	{
		if (is_null($this->endpoint))
		{
			$settings = Configuration::getValue('im');
			$endpoint  = $settings['call']['beta_server_url'] ?? '';

			if (!empty($endpoint))
			{
				if (!(mb_strpos($endpoint, 'https://') === 0 || mb_strpos($endpoint, 'http://') === 0))
				{
					$endpoint = 'https://' . $endpoint;
				}
				$this->endpoint = $endpoint;
			}
		}

		return $this->endpoint;
	}

	/**
	 * Returns API endpoint for the service.
	 *
	 * @return string
	 */
	protected function getServiceUrl(): string
	{
		return $this->getEndpoint();
	}

	public function createCall($callUuid, $secretKey, $initiatorId)
	{
		return $this->performRequest(
			'callcontroller.Controller.InternalApi.createCall',
			[
				'uuid' => $callUuid,
				'secretKey' => $secretKey,
				'initiatorUserId' => $initiatorId,
			]
		);
	}
}