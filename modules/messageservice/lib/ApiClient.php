<?php

namespace Bitrix\MessageService;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Result;
use Bitrix\Main\Service\MicroService\BaseSender;

class ApiClient extends BaseSender
{
	protected const SERVICE_ENDPOINT_OPTION = 'service_endpoint';
	protected const DEFAULT_ENDPOINT = "https://unc.bitrix.info";

	protected $customEndpoint;

	public function __construct(string $endpoint = null)
	{
		parent::__construct();

		if ($endpoint)
		{
			$this->customEndpoint = $endpoint;
		}
	}

	/**
	 * Returns API endpoint for the service.
	 *
	 * @return string
	 */
	protected function getServiceUrl(): string
	{
		return $this->customEndpoint ?? $this::getDefaultEndpoint();
	}

	protected static function getDefaultEndpoint(): string
	{
		if (defined('NOTIFICATIONS_ENDPOINT'))
		{
			return \NOTIFICATIONS_ENDPOINT;
		}

		return Option::get('notifications', static::SERVICE_ENDPOINT_OPTION, static::DEFAULT_ENDPOINT);
	}

	public function listAutoTemplates(string $langId = ''): Result
	{
		return $this->performRequest(
			"notificationservice.Template.listAuto",
			[
				'languageId' => $langId
			]
		);
	}
}
