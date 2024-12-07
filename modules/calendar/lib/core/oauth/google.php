<?php

namespace Bitrix\Calendar\Core\Oauth;

use Bitrix\Calendar\Sync\Google\Helper;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\HttpApplication;

class Google extends Base
{
	/**
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected function __construct($userId)
	{
		if (\CSocServGoogleProxyOAuth::isProxyAuth())
		{
			$this->oauthClient = new \CSocServGoogleProxyOAuth($userId);
		}
		else
		{
			$this->oauthClient = new \CSocServGoogleOAuth($userId);
		}

		$this->oauthClient->getEntityOAuth()->addScope([
			'https://www.googleapis.com/auth/calendar',
			'https://www.googleapis.com/auth/calendar.readonly'
		]);

		$this->oauthClient->getEntityOAuth()->removeScope('https://www.googleapis.com/auth/drive');

	}

	/**
	 * @return bool
	 */
	protected function checkService(): bool
	{
		return \CCalendar::isGoogleApiEnabled();
	}

	/**
	 * @return string
	 */
	public static function getServiceName(): string
	{
		return 'google';
	}

	/**
	 * @return string
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	public function getUrl(): string
	{
		/** @var Helper $helper */
		$helper = ServiceLocator::getInstance()->get('calendar.service.google.helper');
		$isMobile = HttpApplication::getInstance()->getSession()->get('MOBILE_OAUTH');
		$mode = $isMobile ? 'bx_mobile' : 'opener';
		$backUrl = $isMobile ? null : '#googleAuthSuccess';

		return $this->oauthClient->getUrl(
			$mode,
			null,
			[
				'BACKURL' => $backUrl,
				'APIKEY' => $helper->getApiKey()
			]
		);
	}
}
