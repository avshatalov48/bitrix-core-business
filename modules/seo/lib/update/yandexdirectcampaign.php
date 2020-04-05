<?php

namespace Bitrix\Seo\Update;

use Bitrix\Main\Loader;
use Bitrix\Seo\Engine\YandexDirect;
use Bitrix\Seo\Engine\YandexDirectException;
use Bitrix\Seo\Service;

class YandexDirectCampaign
{
	/**
	 * Agent for updating settings field in Yandex direct campaign table. Just once
	 *
	 * @return void
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function updateSettingsAgent()
	{
		if (Loader::includeModule('seo') && Loader::includeModule('socialservices'))
		{
			$engine = new YandexDirect();
			if (!Service::isRegistered())
			{
				return;
			}
			
			$authInfo = Service::getAuth($engine->getCode());
			if (!is_array($authInfo) || empty($authInfo) || $authInfo['expires_in'] <= time())
			{
				return;
			}
			
			try
			{
				self::clearData();
				$engine->updateCampaignManual();
			}
			catch (YandexDirectException $e)
			{
			}
		}
	}
	
	protected static function clearData()
	{
		global $DB;
		$DB->Query("TRUNCATE TABLE `b_seo_adv_campaign`");
		$DB->Query("TRUNCATE TABLE `b_seo_adv_banner`");
		$DB->Query("TRUNCATE TABLE `b_seo_adv_group`");
	}
}