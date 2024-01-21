<?php

namespace Bitrix\Calendar\Core\Oauth;

use Bitrix\Calendar\Sync\Office365\Helper;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\HttpApplication;

class Office365 extends Base
{
	/**
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	protected function __construct($userId)
	{
		/** @var Helper $helper */
		$helper = ServiceLocator::getInstance()->get('calendar.service.office365.helper');

		$this->oauthClient = new \CSocServOffice365OAuth($userId);
		$this->oauthClient->getEntityOAuth()->addScope($helper::NEED_SCOPE);
	}

	/**
	 * @return bool
	 */
	protected function checkService(): bool
	{
		return \CCalendar::IsCalDAVEnabled() && \CCalendar::isOffice365ApiEnabled();
	}

	/**
	 * @return string
	 */
	public static function getServiceName(): string
	{
		return 'office365';
	}

	/**
	 * @return string
	 */
	public function getUrl(): string
	{
		$isMobile = HttpApplication::getInstance()->getSession()->get('MOBILE_OAUTH');
		$mode = $isMobile ? 'bx_mobile' : 'opener';
		$backUrl = $isMobile ? null : '#office365AuthSuccess';

		return $this->oauthClient->getUrl(
			$mode,
			null,
			[
				'BACKURL' => $backUrl,
			]
		);
	}
}