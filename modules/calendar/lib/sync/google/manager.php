<?php

namespace Bitrix\Calendar\Sync\Google;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Managers\ServiceBase;
use Bitrix\Calendar\Sync\Util\Helper;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\HttpClient;

abstract class Manager extends ServiceBase
{
	/**
	 * @var HttpQuery
	 */
	protected HttpQuery $httpClient;
	protected int $userId;

	public function __construct(Connection $connection, int $userId)
	{
		parent::__construct($connection);
		$this->initHttpClient($userId);
		$this->userId = $userId;
	}

	/**
	 * @param int $userId
	 *
	 * @throws SystemException	
	 * @throws  LoaderException
	 */
	private function initHttpClient(int $userId): void
	{
		if (!Loader::includeModule('socialservices'))
		{
			throw new SystemException('Module Socialservices not found');
		}

		$httpClient = new HttpClient();
		if (\CSocServGoogleProxyOAuth::isProxyAuth())
		{
			$oAuth = new \CSocServGoogleProxyOAuth($userId);
		}
		else
		{
			$oAuth = new \CSocServGoogleOAuth($userId);
		}

		$oAuth->getEntityOAuth()->addScope(
			[
				'https://www.googleapis.com/auth/calendar',
				'https://www.googleapis.com/auth/calendar.readonly',
			]
		);

		$oAuth->getEntityOAuth()->setUser($userId);
		if ($oAuth->getEntityOAuth()->GetAccessToken())
		{
			$httpClient->setHeader('Authorization', 'Bearer ' . $oAuth->getEntityOAuth()->getToken());
			$httpClient->setHeader('Content-Type', 'application/json');
			$httpClient->setHeader('Referer', Helper::getDomain());
			unset($oAuth);
		}

		$this->httpClient = new HttpQuery($httpClient, $userId);
	}

	/**
	 * @return bool
	 */
	protected function isRequestSuccess(): bool
	{
		return $this->httpClient->getStatus() === 200;
	}

	protected function isRequestDeleteSuccess(): bool
	{
		$acceptedCodes = [200, 201, 204, 404];

		return in_array($this->httpClient->getStatus(), $acceptedCodes);
	}
}
