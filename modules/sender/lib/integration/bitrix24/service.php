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
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

use Bitrix\Sender\Internals\Model;
use Bitrix\Sender\Dispatch\Semantics;
use Bitrix\Sender\Message\Tracker;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Message;
use Bitrix\Sender\Integration\Seo;

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
			self::isMailingsAvailable()
			||
			self::isAdAvailable()
			||
			self::isRcAvailable()
			||
			self::isEmailAvailable()
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
	 * Return true if Rc is available.
	 *
	 * @return bool
	 */
	public static function isRcAvailable()
	{
		return !self::isCloud() || Feature::isFeatureEnabled('sender_rc');
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
	 * Return true if region of cloud portal is Russian.
	 *
	 * @return bool
	 */
	public static function isCloudRegionRussian()
	{
		return self::isCloud() && in_array(\CBitrix24::getPortalZone(), array('ru', 'kz', 'by'));
	}

	/**
	 * Return true if Ad provider is available in region.
	 *
	 * @param string $code Service message code.
	 * @return bool
	 */
	public static function isAdVisibleInRegion($code)
	{
		if (!self::isCloud())
		{
			return true;
		}

		if (!in_array($code, array(Seo\Ads\MessageBase::CODE_ADS_VK, Seo\Ads\MessageBase::CODE_ADS_YA)))
		{
			return true;
		}

		return self::isCloudRegionRussian();
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
	 * @return bool
	 */
	public static function getTrackingUri($type)
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

		if (self::isCloud() && !in_array(substr(BX24_HOST_NAME, -7), ['.com.br', '.com.de'])) // exclude com.br & com.de domains
		{
			Loader::includeModule('bitrix24');
			$domain = BX24_HOST_NAME;

			if (!\CBitrix24::isCustomDomain())
			{
				$domain = preg_replace('/^([-\.\w]+)\.bitrix24\.([-\.\w]+)/', '$2.$1', $domain);
				$domain = "mailinternetsub.com/" . $domain;
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
}