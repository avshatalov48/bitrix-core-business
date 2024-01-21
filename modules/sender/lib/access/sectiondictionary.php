<?php
namespace Bitrix\Sender\Access;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Access\Permission\PermissionDictionary;
use Bitrix\Sender\Integration\Seo\Ads\MessageBase;
use Bitrix\Sender\Security\Role\Permission;
use Bitrix\Sender\Message;

Loc::loadMessages(__FILE__);
class SectionDictionary
{
	const MAILING   = 1;
	const ADS       = 2;
	const SEGMENT   = 3;
	const RC        = 4;
	const TEMPLATE  = 5;
	const BLACKLIST = 6;
	const START     = 7;
	const SETTINGS  = 8;

	public static function getMap()
	{
		return [
			self::MAILING   => [
				PermissionDictionary::MAILING_VIEW,
				PermissionDictionary::MAILING_EMAIL_EDIT,
				PermissionDictionary::MAILING_SMS_EDIT,
				PermissionDictionary::MAILING_MESSENGER_EDIT,
				PermissionDictionary::MAILING_AUDIO_CALL_EDIT,
				PermissionDictionary::MAILING_INFO_CALL_EDIT,
				PermissionDictionary::MAILING_PAUSE_START_STOP,
				PermissionDictionary::MAILING_CLIENT_VIEW,
			],
			self::ADS       => [
				PermissionDictionary::ADS_VIEW,
				PermissionDictionary::ADS_YANDEX_EDIT,
				PermissionDictionary::ADS_GOOGLE_EDIT_MSGVER_1,
				PermissionDictionary::ADS_VK_EDIT,
				PermissionDictionary::ADS_FB_INSTAGRAM_EDIT,
				PermissionDictionary::ADS_LOOK_ALIKE_VK_EDIT,
				PermissionDictionary::ADS_LOOK_ALIKE_FB_EDIT,
				PermissionDictionary::ADS_PAUSE_START_STOP,
				PermissionDictionary::ADS_CLIENT_VIEW,
				PermissionDictionary::ADS_MARKETING_FB_EDIT,
				PermissionDictionary::ADS_MARKETING_INSTAGRAM_EDIT,
				PermissionDictionary::ADS_MASTER_YANDEX_EDIT,
			],
			self::SEGMENT   => [
				PermissionDictionary::SEGMENT_EDIT,
				PermissionDictionary::SEGMENT_VIEW,
				PermissionDictionary::SEGMENT_CLIENT_EDIT,
				PermissionDictionary::SEGMENT_CLIENT_VIEW,
				PermissionDictionary::SEGMENT_LEAD_EDIT,
				PermissionDictionary::SEGMENT_CLIENT_PERSONAL_EDIT,
				PermissionDictionary::SEGMENT_CLIENT_OWN_CATEGORY,
			],
			self::RC        => [
				PermissionDictionary::RC_EDIT,
				PermissionDictionary::RC_PAUSE_START_STOP,
				PermissionDictionary::RC_VIEW,
			],
			self::TEMPLATE  => [
				PermissionDictionary::TEMPLATE_EDIT,
				PermissionDictionary::TEMPLATE_VIEW,
			],
			self::BLACKLIST => [
				PermissionDictionary::BLACKLIST_EDIT,
				PermissionDictionary::BLACKLIST_VIEW,
			],
			self::START     => [
				PermissionDictionary::START_VIEW,
			],
			self::SETTINGS  => [
				PermissionDictionary::SETTINGS_EDIT,
//				PermissionDictionary::SETTINGS_VIEW,
			],
		];
	}

	/**
	 * Returning the Codes which need to be validated before showing
	 * @return array
	 */
	public static function getAdsAccessMap()
	{
		return [
			PermissionDictionary::ADS_YANDEX_EDIT => MessageBase::CODE_ADS_YA,
			PermissionDictionary::ADS_VK_EDIT => MessageBase::CODE_ADS_VK,
			PermissionDictionary::ADS_LOOK_ALIKE_VK_EDIT => MessageBase::CODE_ADS_LOOKALIKE_VK,
			PermissionDictionary::ADS_FB_INSTAGRAM_EDIT => Message\iMarketing::CODE_FACEBOOK,
			PermissionDictionary::ADS_LOOK_ALIKE_FB_EDIT => MessageBase::CODE_ADS_LOOKALIKE_FB,
			PermissionDictionary::ADS_MARKETING_FB_EDIT => MessageBase::CODE_ADS_FB,
			PermissionDictionary::ADS_MARKETING_INSTAGRAM_EDIT => Message\iMarketing::CODE_FACEBOOK,
			PermissionDictionary::ADS_MASTER_YANDEX_EDIT => MessageBase::CODE_MASTER_YANDEX,
		];
	}

	/**
	 * Returning the map of the legacy permission configuration
	 * @return array
	 */
	public static function getLegacyMap()
	{
		return [
			self::MAILING   => Permission::ENTITY_LETTER,
			self::ADS       => Permission::ENTITY_AD,
			self::SEGMENT   => Permission::ENTITY_SEGMENT,
			self::RC        => Permission::ENTITY_RC,
			self::TEMPLATE  => Permission::ENTITY_SEGMENT,
			self::BLACKLIST => Permission::ENTITY_BLACKLIST,
			self::START     => Permission::ENTITY_LETTER,
			self::SETTINGS  => Permission::ENTITY_SETTINGS,
		];
	}
	protected static function getClassName()
	{
		return __CLASS__;
	}

	/**
	 * Getting a list of the permission settings
	 * @return array
	 */
	public static function getList(): array
	{
		$class = new \ReflectionClass(__CLASS__);
		return array_flip($class->getConstants());
	}

	/**
	 * This method returning Localized title of the sections in Permission settings
	 * @param int $value
	 * @return string
	 */
	public static function getTitle(int $value)
	{
		$sectionsList = self::getList();

		if (!array_key_exists($value, $sectionsList))
		{
			return '';
		}
		$title = $sectionsList[$value];

		return Loc::getMessage("SENDER_CONFIG_SECTIONS_".$title) ?? '';
	}
}
