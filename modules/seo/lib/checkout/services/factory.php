<?php

namespace Bitrix\Seo\Checkout\Services;

use Bitrix\Main;
use Bitrix\Seo;

/**
 * Class Factory
 * @package Bitrix\Seo\Checkout\Services
 */
class Factory
{
	/**
	 * @param string $type
	 * @return AccountYandex|AccountYookassa
	 * @throws Main\SystemException
	 */
	public static function createService(string $type)
	{
		$oauthService = null;

		if ($type === AccountYandex::TYPE_CODE)
		{
			$oauthService = new AccountYandex();
		}

		if ($type === AccountYookassa::TYPE_CODE)
		{
			$oauthService = new AccountYookassa();
		}

		if ($oauthService)
		{
			$oauthService->setService(Seo\Checkout\Service::getInstance());
			return $oauthService;
		}

		throw new Main\SystemException("Service with type: \"{$type}\" not found");
	}
}