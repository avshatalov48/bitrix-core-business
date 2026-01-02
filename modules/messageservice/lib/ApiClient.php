<?php

namespace Bitrix\MessageService;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Result;
use Bitrix\Main\Service\MicroService\BaseSender;

class ApiClient extends BaseSender
{
	protected const SERVICE_ENDPOINT_OPTION = 'service_endpoint';
	protected const ENDPOINTS = [
		'ru' => 'https://unc.bitrix24.tech',
		'en' => 'https://unc-eu.bitrix.info',
	];
	protected const RU_REGIONS = ['ru', 'kz', 'by', 'uz', 'am', 'az', 'ge', 'kg'];

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

		return Option::get('notifications', static::SERVICE_ENDPOINT_OPTION, self::getCurrentEndpoint());
	}

	protected static function getCurrentEndpoint(): string
	{
		$region = \Bitrix\Main\Application::getInstance()->getLicense()->getRegion() ?? 'en';

		if (in_array($region, static::RU_REGIONS))
		{
			return self::ENDPOINTS['ru'];
		}

		return self::ENDPOINTS[$region] ?? self::ENDPOINTS['en'];
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

	public function getFileProxyUrl(string $url): Result
	{
		return $this->performRequest(
			'notificationservice.File.create',
			[
				'url' => $url,
			]
		);
	}
}
