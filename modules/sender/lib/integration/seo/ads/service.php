<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Seo\Ads;

use Bitrix\Main\Loader;
use Bitrix\Sender\Integration;

use Bitrix\Seo\Retargeting;

/**
 * Class Service
 * @package Bitrix\Sender\Integration\Seo\Ads
 */
class Service
{
	/**
	 * Can use.
	 *
	 * @return bool
	 */
	public static function canUse()
	{
		if (!Loader::includeModule('seo'))
		{
			return false;
		}

		return Retargeting\AdsAudience::canUse();
	}

	/**
	 * Service can be used, but is not available because of plan.
	 *
	 * @return bool
	 */
	public static function isAvailable()
	{
		return self::canUse() && Integration\Bitrix24\Service::isAdAvailable();
	}

	/**
	 * Get type map.
	 *
	 * @return array
	 */
	public static function getTypeMap()
	{
		return array(
			MessageBase::CODE_ADS_FB => Retargeting\Service::TYPE_FACEBOOK,
			MessageBase::CODE_ADS_VK => Retargeting\Service::TYPE_VKONTAKTE,
			MessageBase::CODE_ADS_YA => Retargeting\Service::TYPE_YANDEX,
			MessageBase::CODE_ADS_GA => Retargeting\Service::TYPE_GOOGLE,
			MessageBase::CODE_ADS_LOOKALIKE_FB => Retargeting\Service::TYPE_FACEBOOK,
			MessageBase::CODE_ADS_LOOKALIKE_VK => Retargeting\Service::TYPE_VKONTAKTE,
		);
	}

	/**
	 * Get ads provider.
	 *
	 * @param string $adsType Ads type.
	 * @return array
	 */
	public static function getAdsProvider($adsType, $clientId = null)
	{
		$service = Retargeting\AdsAudience::getService();
		$service->setClientId($clientId);
		$providers = Retargeting\AdsAudience::getProviders([$adsType]);
		$isFound = false;
		$provider = array();
		foreach ($providers as $type => $provider)
		{
			if ($type == $adsType)
			{
				$isFound = true;
				break;
			}
		}

		if (!$isFound)
		{
			return null;
		}

		return $provider;
	}

	/**
	 * Send.
	 *
	 * @param \stdClass $config Config.
	 * @param array $contacts.
	 * @return bool
	 */
	public static function send(\stdClass $config, array $contacts)
	{
		if (!static::canUse())
		{
			return false;
		}

		$audience = Retargeting\Service::getAudience($config->type);
		$audience->setAccountId($config->accountId);
		$audience->enableQueueMode();

		if ($config->autoRemoveDayNumber)
		{
			$audience->enableQueueAutoRemove($config->autoRemoveDayNumber);
		}
		else
		{
			$audience->disableQueueAutoRemove();
		}

		$audienceImportResult = $audience->addContacts(
			$config->audienceId,
			$contacts,
			array(
				'type' => $config->contactType
			)
		);

		return $audienceImportResult->isSuccess();
	}
}