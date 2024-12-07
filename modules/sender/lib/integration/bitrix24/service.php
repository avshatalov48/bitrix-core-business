<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Bitrix24;

use Bitrix\Bitrix24\Feature;
use Bitrix\Main\Config\Option;
use Bitrix\Main\IO\File;
use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\SiteTable;
use Bitrix\Sender\Dispatch\Semantics;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Integration\Seo;
use Bitrix\Sender\Internals\Model;
use Bitrix\Sender\Message;
use Bitrix\Sender\Message\Tracker;

/**
 * Class Service
 * @package Bitrix\Sender\Integration\Bitrix24
 */
class Service
{
	/**
	 * Return true if installation is portal.
	 *
	 * @return bool
	 */
	public static function isPortal()
	{
		return (ModuleManager::isModuleInstalled('bitrix24') || ModuleManager::isModuleInstalled('intranet'));
	}

	/**
	 * Return true if some instrument is available.
	 *
	 * @return bool
	 */
	public static function isAvailable()
	{
		return
			self::isRcAvailable()
			||
			self::isMailingsAvailable()
			||
			self::isAdAvailable()
			||
			self::isEmailAvailable()
			||
			self::isTolokaAvailable()
			||
			self::isFbAdAvailable()
		;
	}

	/**
	 * Return true if Ad is available.
	 *
	 * @return bool
	 */
	public static function isAdAvailable()
	{
		return !self::isCloud() || Feature::isFeatureEnabled('sender_ad');
	}

	/**
	 * Return true if Fb Ad is available.
	 *
	 * @return bool
	 */
	public static function isFbAdAvailable()
	{
		return !self::isCloud() || Feature::isFeatureEnabled('sender_fb_ads');
	}

	/**
	 * Return true if Toloka is available.
	 *
	 * @return bool
	 */
	public static function isTolokaAvailable()
	{
		return !self::isCloud() || Feature::isFeatureEnabled('sender_toloka');
	}

	/**
	 * Return true if Rc is available.
	 *
	 * @return bool
	 */
	public static function isRcAvailable()
	{
		return !self::isCloud() || Feature::isFeatureEnabled('sender_rc');
	}

	/**
	 * Return true if Security is available.
	 *
	 * @return bool
	 */
	public static function isPermissionEnabled()
	{
		return !self::isCloud() || Feature::isFeatureEnabled('sender_security');
	}

	/**
	 * Return true if Campaigns is available.
	 *
	 * @return bool
	 */
	public static function isCampaignsAvailable()
	{
		return !self::isCloud() && !self::isPortal();
	}

	/**
	 * Return true if region of portal is Russian.
	 *
	 * @param bool $onlyRu Check only ru region.
	 * @return bool
	 */
	public static function isRegionRussian(bool $onlyRu = false): bool
	{
		$regions = $onlyRu ? ['ru'] : ['ru', 'kz', 'by'];

		$region = \Bitrix\Main\Application::getInstance()->getLicense()->getRegion() ?: 'ru';
		return in_array($region, $regions);
	}

	/**
	 * Return true if region of cloud portal is Russian.
	 *
	 * @param bool $onlyRu Check only ru region.
	 * @return bool
	 */
	public static function isCloudRegionRussian(bool $onlyRu = false): bool
	{
		$regions = $onlyRu ? ['ru'] : ['ru', 'kz', 'by'];
		return self::isCloud() && in_array(\CBitrix24::getPortalZone(), $regions);
	}

	/**
	 * Return true if region of cloud portal is Russian.
	 *
	 * @return bool
	 */
	public static function isCloudRegionMayTrackMails()
	{
		return self::isCloud() && in_array(
			\CBitrix24::getPortalZone(), [
					'de',
					'eu',
					'it',
					'pl',
					'fr',
				]
			);
	}

	/**
	 * Return true if Ad provider is available in region.
	 *
	 * @param string $code Service message code.
	 * @return bool
	 */
	public static function isAdVisibleInRegion($code)
	{
		$codes = [
			Seo\Ads\MessageBase::CODE_ADS_VK,
			Seo\Ads\MessageBase::CODE_ADS_YA,
			Seo\Ads\MessageBase::CODE_ADS_LOOKALIKE_VK,
			Seo\Ads\MessageBase::CODE_ADS_LOOKALIKE_YANDEX
		];

		if (in_array($code, $codes))
		{
			if (self::isCloud())
			{
				return self::isCloudRegionRussian();
			}
			elseif (Loader::includeModule('intranet'))
			{
				return in_array(\CIntranetUtils::getPortalZone(), ['ru', 'kz', 'by']);
			}

			return true;
		}

		if (in_array(
			$code,
			[
				Seo\Ads\MessageBase::CODE_ADS_FB,
				Seo\Ads\MessageBase::CODE_ADS_LOOKALIKE_FB,
				Message\iMarketing::CODE_FACEBOOK,
				Message\iMarketing::CODE_INSTAGRAM,
			]
		))
		{
			return !self::isRegionRussian(true);
		}

		return true;
	}

	/**
	 * Return true if master yandex is available.
	 *
	 * @return bool
	 */
	public static function isMasterYandexVisibleInRegion(): bool
	{
		$isLanguageAcceptable = (LANGUAGE_ID ?? 'ru') === 'ru';

		if (!self::isCloud())
		{
			return false;
		}
		return self::isCloudRegionRussian(true) && $isLanguageAcceptable;
	}

	/**
	 * Return true if toloka is available.
	 *
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function isTolokaVisibleInRegion(): bool
	{
		$isLanguageAcceptable = (LANGUAGE_ID ?? 'ru') === 'ru';

		if (self::isCloud())
		{
			return self::isCloudRegionRussian() && $isLanguageAcceptable ;
		}
		elseif (Loader::includeModule('intranet'))
		{
			return in_array(\CIntranetUtils::getPortalZone(), ['ru', 'kz', 'by']) && $isLanguageAcceptable;
		}

		return $isLanguageAcceptable;
	}

	/**
	 * Return true if sms, call, web hook is available.
	 *
	 * @return bool
	 */
	public static function isMailingsAvailable()
	{
		return !self::isCloud() || Feature::isFeatureEnabled('sender_mailing');
	}

	/**
	 * Return true if email is available.
	 *
	 * @return bool
	 */
	public static function isEmailAvailable()
	{
		$dateCreate = Option::get("main", "~controller_date_create", "");

		return !self::isCloud()
			||
			Feature::isFeatureEnabled('sender_email')
			||
			(
				empty($dateCreate)
				||
				$dateCreate <= mktime(
					0, 0, 0,
					1, 9, 2019
				)
			);
	}

	/**
	 * Get available mailing codes.
	 *
	 * @return array
	 */
	public static function getAvailableMailingCodes()
	{
		if (self::isMailingsAvailable())
		{
			return Message\Factory::getMailingMessageCodes();
		}

		if (self::isEmailAvailable())
		{
			return [Message\iBase::CODE_MAIL];
		}

		return [];
	}

	/**
	 * Return true if portal is cloud.
	 *
	 * @return bool
	 */
	public static function isCloud()
	{
		return Loader::includeModule('bitrix24');
	}

	/**
	 * Return tracking uri.
	 *
	 * @param int $type Tracker type.
	 * @param null|string $siteId Site id.
	 * @return string|null
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getTrackingUri(int $type, ?string $siteId = null): ?string
	{
		switch ($type)
		{
			case Tracker::TYPE_READ:
				$code = 'read';
				break;

			case Tracker::TYPE_CLICK:
				$code = 'click';
				break;

			case Tracker::TYPE_UNSUB:
			default:
				$code = 'unsubscribe';
				break;
		}

		$uri = "/pub/mail/$code.php";
		if ($siteId)
		{
			if (!File::isFileExists(SiteTable::getDocumentRoot($siteId) . DIRECTORY_SEPARATOR . $uri))
			{
				return null;
			}
		}

		return static::replaceTrackingDomainIfNeed($uri);
	}

	public static function replaceTrackingDomainIfNeed(string $uri): string
	{
		// exclude com.br & com.de domains
		if (
			self::isCloud()
			&& defined('BX24_HOST_NAME')
			&& !in_array(mb_substr(BX24_HOST_NAME, -7), ['.com.br', '.com.de'])
		)
		{
			$domain = BX24_HOST_NAME;

			if (!\CBitrix24::isCustomDomain())
			{
				$queryDomain = preg_replace('/^([-\.\w]+)\.bitrix24\.([-\.\w]+)/', '$2.$1', $domain);
				$subdomain = rtrim(mb_substr(str_replace('.','-', $queryDomain), 0, 63), '-');
				$domain = "$subdomain.mailinetservice.net/$queryDomain";
			}

			$uri = "https://$domain$uri";
		}

		return $uri;
	}

	/**
	 * Return true if installation is portal.
	 *
	 * @return void
	 */
	public static function initLicensePopup()
	{
		if (!self::isCloud())
		{
			return;
		}

		\CBitrix24::initLicenseInfoPopupJS();
		\CJSCore::init('sender_b24_license');
	}

	/**
	 * Return true if plan is top.
	 *
	 * @return bool
	 */
	public static function isLicenceTop()
	{
		if (!self::isCloud())
		{
			return true;
		}

		return \CBitrix24::getLicenseType() === 'company';
	}

	/**
	 * Lock additional services.
	 *
	 * @return void
	 */
	public static function lockServices()
	{
		if (!self::isCloud())
		{
			return;
		}

		$letters = Model\LetterTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=STATUS' => Semantics::getWorkStates(),
				'!MESSAGE_CODE' => Message\iBase::CODE_MAIL
			)
		));

		$letter = new Entity\Letter();
		foreach ($letters as $letterData)
		{
			$letter->load($letterData['ID']);
			if (!$letter->getId())
			{
				continue;
			}

			$state = $letter->getState();
			if ($state->canReady())
			{
				$state->ready();
			}
			else if ($state->canStop())
			{
				$state->stop();
			}
		}
	}

	public static function isMasterYandexAvailable(): bool
	{
		return static::isCloud();
	}
}
