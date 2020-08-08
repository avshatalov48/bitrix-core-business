<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Sender\Access\Permission;

class PermissionDictionary extends \Bitrix\Main\Access\Permission\PermissionDictionary
{
	const MAILING_VIEW             = 1;
	const MAILING_EMAIL_EDIT       = 2;
	const MAILING_SMS_EDIT         = 3;
	const MAILING_MESSENGER_EDIT   = 4;
	const MAILING_INFO_CALL_EDIT   = 5;
	const MAILING_AUDIO_CALL_EDIT  = 6;
	const MAILING_PAUSE_START_STOP = 7;
	const MAILING_CLIENT_VIEW      = 8;

	const ADS_VIEW               = 9;
	const ADS_YANDEX_EDIT        = 10;
	const ADS_GOOGLE_EDIT        = 11;
	const ADS_VK_EDIT            = 12;
	const ADS_FB_INSTAGRAM_EDIT  = 13;
	const ADS_LOOK_ALIKE_VK_EDIT = 14;
	const ADS_LOOK_ALIKE_FB_EDIT = 15;
	const ADS_PAUSE_START_STOP   = 16;
	const ADS_CLIENT_VIEW        = 17;
	const ADS_CONNECT_CABINET    = 18;

	const RC_EDIT             = 19;
	const RC_VIEW             = 20;
	const RC_PAUSE_START_STOP = 21;

	const SEGMENT_EDIT                 = 22;
	const SEGMENT_VIEW                 = 23;
	const SEGMENT_CLIENT_EDIT          = 24;
	const SEGMENT_CLIENT_VIEW          = 25;
	const SEGMENT_CLIENT_OWN_CATEGORY  = 26;
	const SEGMENT_LEAD_EDIT            = 27;
	const SEGMENT_CLIENT_PERSONAL_EDIT = 28;

	const BLACKLIST_EDIT = 29;
	const BLACKLIST_VIEW = 30;

	const TEMPLATE_EDIT = 31;
	const TEMPLATE_VIEW = 32;

	const START_VIEW = 33;

	const SETTINGS_EDIT = 34;
//	const SETTINGS_VIEW = 35;
}