<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Sender\Access;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Access\Permission\PermissionDictionary;
use Bitrix\Sender\Security\Role\Permission;

Loc::loadMessages(__FILE__);

class ActionDictionary
{
	const ACTION_MAILING_VIEW             = 'mailing_view';
	const ACTION_MAILING_EMAIL_EDIT       = 'mailing_email_edit';
	const ACTION_MAILING_SMS_EDIT         = 'mailing_sms_edit';
	const ACTION_MAILING_MESSENGER_EDIT   = 'mailing_messenger_edit';
	const ACTION_MAILING_INFO_CALL_EDIT   = 'mailing_info_call_edit';
	const ACTION_MAILING_AUDIO_CALL_EDIT  = 'mailing_audio_call_edit';
	const ACTION_MAILING_PAUSE_START_STOP = 'mailing_pause_start_stop';
	const ACTION_MAILING_CLIENT_VIEW      = 'mailing_client_view';

	const ACTION_ADS_VIEW               = 'ads_view';
	const ACTION_ADS_YANDEX_EDIT        = 'ads_yandex_edit';
	const ACTION_ADS_GOOGLE_EDIT        = 'ads_google_edit';
	const ACTION_ADS_VK_EDIT            = 'ads_vk_edit';
	const ACTION_ADS_FB_INSTAGRAM_EDIT  = 'ads_fb_instagram_edit';
	const ACTION_ADS_LOOK_ALIKE_VK_EDIT = 'ads_look_alike_vk_edit';
	const ACTION_ADS_LOOK_ALIKE_FB_EDIT = 'ads_look_alike_fb_edit';
	const ACTION_ADS_PAUSE_START_STOP   = 'ads_pause_start_stop';
	const ACTION_ADS_CLIENT_VIEW        = 'ads_client_view';
	const ACTION_ADS_CONNECT_CABINET    = 'ads_connect_cabinet';

	const ACTION_RC_EDIT  = 'rc_edit';
	const ACTION_RC_VIEW  = 'rc_view';
	const ACTION_RC_PAUSE_START_STOP  = 'rc_pause_start_stop';

	const ACTION_SEGMENT_EDIT  = 'segment_edit';
	const ACTION_SEGMENT_VIEW  = 'segment_view ';
	const ACTION_SEGMENT_CLIENT_EDIT          = 'segment_client_edit';
	const ACTION_SEGMENT_CLIENT_VIEW          = 'segment_client_view';
	const ACTION_SEGMENT_CLIENT_OWN_CATEGORY  = 'segment_client_own_category';
	const ACTION_SEGMENT_LEAD_EDIT            = 'segment_lead_edit';
	const ACTION_SEGMENT_CLIENT_PERSONAL_EDIT = 'segment_client_personal_edit';

	const ACTION_BLACKLIST_EDIT  = 'blacklist_edit';
	const ACTION_BLACKLIST_VIEW  = 'blacklist_view';

	const ACTION_TEMPLATE_EDIT  = 'template_edit';
	const ACTION_TEMPLATE_VIEW  = 'template_view';

	const ACTION_START_VIEW  = 'start_view';

	const ACTION_SETTINGS_EDIT  = 'settings_edit';
//	const ACTION_SETTINGS_VIEW  = 'settings_view';
	const ACTION_ADS_MARKETING_FB_EDIT = 'ads_marketing_fb_edit';
	const ACTION_ADS_MARKETING_INSTAGRAM_EDIT = 'ads_marketing_instagram_edit';

	public const PREFIX="ACTION_";

	protected static function getClassName()
	{
		return __CLASS__;
	}

	/**
	 * permission on action
	 * @return array
	 */
	public static function getActionPermissionMap()
	{
		return [
			self::ACTION_MAILING_VIEW                 => PermissionDictionary::MAILING_VIEW,
			self::ACTION_MAILING_EMAIL_EDIT           => PermissionDictionary::MAILING_EMAIL_EDIT,
			self::ACTION_MAILING_SMS_EDIT             => PermissionDictionary::MAILING_SMS_EDIT,
			self::ACTION_MAILING_MESSENGER_EDIT       => PermissionDictionary::MAILING_MESSENGER_EDIT,
			self::ACTION_MAILING_INFO_CALL_EDIT       => PermissionDictionary::MAILING_INFO_CALL_EDIT,
			self::ACTION_MAILING_AUDIO_CALL_EDIT      => PermissionDictionary::MAILING_AUDIO_CALL_EDIT,
			self::ACTION_MAILING_CLIENT_VIEW          => PermissionDictionary::MAILING_CLIENT_VIEW,
			self::ACTION_ADS_VIEW                     => PermissionDictionary::ADS_VIEW,
			self::ACTION_ADS_YANDEX_EDIT              => PermissionDictionary::ADS_YANDEX_EDIT,
			self::ACTION_ADS_GOOGLE_EDIT              => PermissionDictionary::ADS_GOOGLE_EDIT,
			self::ACTION_ADS_VK_EDIT                  => PermissionDictionary::ADS_VK_EDIT,
			self::ACTION_ADS_MARKETING_FB_EDIT        => PermissionDictionary::ADS_MARKETING_FB_EDIT,
			self::ACTION_ADS_MARKETING_INSTAGRAM_EDIT => PermissionDictionary::ADS_MARKETING_INSTAGRAM_EDIT,
			self::ACTION_ADS_FB_INSTAGRAM_EDIT        => PermissionDictionary::ADS_FB_INSTAGRAM_EDIT,
			self::ACTION_ADS_LOOK_ALIKE_VK_EDIT       => PermissionDictionary::ADS_LOOK_ALIKE_VK_EDIT,
			self::ACTION_ADS_LOOK_ALIKE_FB_EDIT       => PermissionDictionary::ADS_LOOK_ALIKE_FB_EDIT,
			self::ACTION_ADS_CLIENT_VIEW              => PermissionDictionary::ADS_CLIENT_VIEW,
			self::ACTION_ADS_CONNECT_CABINET          => PermissionDictionary::ADS_CONNECT_CABINET,
			self::ACTION_SEGMENT_EDIT                 => PermissionDictionary::SEGMENT_EDIT,
			self::ACTION_SEGMENT_VIEW                 => PermissionDictionary::SEGMENT_VIEW,
			self::ACTION_SEGMENT_CLIENT_OWN_CATEGORY  => PermissionDictionary::SEGMENT_CLIENT_OWN_CATEGORY,
			self::ACTION_SEGMENT_CLIENT_EDIT          => PermissionDictionary::SEGMENT_CLIENT_EDIT,
			self::ACTION_SEGMENT_CLIENT_VIEW          => PermissionDictionary::SEGMENT_CLIENT_VIEW,
			self::ACTION_SEGMENT_LEAD_EDIT            => PermissionDictionary::SEGMENT_LEAD_EDIT,
			self::ACTION_SEGMENT_CLIENT_PERSONAL_EDIT => PermissionDictionary::SEGMENT_CLIENT_PERSONAL_EDIT,
			self::ACTION_RC_EDIT                      => PermissionDictionary::RC_EDIT,
			self::ACTION_RC_PAUSE_START_STOP          => PermissionDictionary::RC_PAUSE_START_STOP,
			self::ACTION_RC_VIEW                      => PermissionDictionary::RC_VIEW,
			self::ACTION_BLACKLIST_EDIT               => PermissionDictionary::BLACKLIST_EDIT,
			self::ACTION_BLACKLIST_VIEW               => PermissionDictionary::BLACKLIST_VIEW,
			self::ACTION_TEMPLATE_EDIT                => PermissionDictionary::TEMPLATE_EDIT,
			self::ACTION_TEMPLATE_VIEW                => PermissionDictionary::TEMPLATE_VIEW,
			self::ACTION_START_VIEW                   => PermissionDictionary::START_VIEW,
			self::ACTION_SETTINGS_EDIT                => PermissionDictionary::SETTINGS_EDIT,
//			self::ACTION_SETTINGS_VIEW                => PermissionDictionary::SETTINGS_VIEW,
			self::ACTION_MAILING_PAUSE_START_STOP     => PermissionDictionary::MAILING_PAUSE_START_STOP,
			self::ACTION_ADS_PAUSE_START_STOP         => PermissionDictionary::ADS_PAUSE_START_STOP
		];
	}

	/**
	 * legacy security map
	 * @return array
	 */
	public static function getLegacyMap()
	{
		return[
			self::ACTION_MAILING_VIEW                 => Permission::ACTION_VIEW,
			self::ACTION_MAILING_EMAIL_EDIT           => Permission::ACTION_MODIFY,
			self::ACTION_MAILING_PAUSE_START_STOP     => Permission::ACTION_MODIFY,
			self::ACTION_ADS_PAUSE_START_STOP         => Permission::ACTION_MODIFY,
			self::ACTION_MAILING_SMS_EDIT             => Permission::ACTION_MODIFY,
			self::ACTION_MAILING_MESSENGER_EDIT       => Permission::ACTION_MODIFY,
			self::ACTION_MAILING_INFO_CALL_EDIT       => Permission::ACTION_MODIFY,
			self::ACTION_MAILING_AUDIO_CALL_EDIT      => Permission::ACTION_MODIFY,
			self::ACTION_ADS_VIEW                     => Permission::ACTION_VIEW,
			self::ACTION_ADS_YANDEX_EDIT              => Permission::ACTION_MODIFY,
			self::ACTION_ADS_GOOGLE_EDIT              => Permission::ACTION_MODIFY,
			self::ACTION_ADS_VK_EDIT                  => Permission::ACTION_MODIFY,
			self::ACTION_ADS_MARKETING_INSTAGRAM_EDIT => Permission::ACTION_MODIFY,
			self::ACTION_ADS_MARKETING_FB_EDIT        => Permission::ACTION_MODIFY,
			self::ACTION_ADS_FB_INSTAGRAM_EDIT        => Permission::ACTION_MODIFY,
			self::ACTION_ADS_LOOK_ALIKE_VK_EDIT       => Permission::ACTION_MODIFY,
			self::ACTION_ADS_LOOK_ALIKE_FB_EDIT       => Permission::ACTION_MODIFY,
			self::ACTION_SEGMENT_EDIT                 => Permission::ACTION_MODIFY,
			self::ACTION_SEGMENT_VIEW                 => Permission::ACTION_VIEW,
			self::ACTION_SEGMENT_CLIENT_EDIT          => Permission::ACTION_MODIFY,
			self::ACTION_SEGMENT_CLIENT_OWN_CATEGORY  => Permission::ACTION_MODIFY,
			self::ACTION_SEGMENT_CLIENT_VIEW          => Permission::ACTION_VIEW,
			self::ACTION_SEGMENT_LEAD_EDIT            => Permission::ACTION_MODIFY,
			self::ACTION_SEGMENT_CLIENT_PERSONAL_EDIT => Permission::ACTION_MODIFY,
			self::ACTION_RC_EDIT                      => Permission::ACTION_MODIFY,
			self::ACTION_RC_VIEW                      => Permission::ACTION_VIEW,
			self::ACTION_BLACKLIST_EDIT               => Permission::ACTION_MODIFY,
			self::ACTION_BLACKLIST_VIEW               => Permission::ACTION_VIEW,
			self::ACTION_TEMPLATE_EDIT                => Permission::ACTION_MODIFY,
			self::ACTION_TEMPLATE_VIEW                => Permission::ACTION_VIEW,
			self::ACTION_START_VIEW                   => Permission::ACTION_VIEW,
			self::ACTION_SETTINGS_EDIT                => Permission::ACTION_MODIFY,
//			self::ACTION_SETTINGS_VIEW                => Permission::ACTION_MODIFY,
		];
	}

	/**
	 * get action name by string value
	 * @param string $value string value of action
	 *
	 * @return string|null
	 * @throws \ReflectionException
	 */
	public static function getActionName(string $value): ?string
	{
		$constants = self::getActionNames();
		if (!array_key_exists($value, $constants))
		{
			return null;
		}

		return str_replace(self::PREFIX, '', $constants[$value]);
	}

	/**
	 * @return array
	 * @throws \ReflectionException
	 */
	private static function getActionNames(): array
	{
		$class = new \ReflectionClass(__CLASS__);
		$constants = $class->getConstants();
		foreach ($constants as $name => $value)
		{
			if (strpos($name, self::PREFIX) !== 0)
			{
				unset($constants[$name]);
			}
		}

		return array_flip($constants);
	}
}