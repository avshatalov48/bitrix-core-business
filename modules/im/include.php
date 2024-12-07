<?php

use Bitrix\Main\EventResult;
use Bitrix\Main\EventManager;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Copyright;

IncludeModuleLangFile(__FILE__);

/**
 * @use \Bitrix\Im\Revision::getWeb()
 * @deprecated
 */
define("IM_REVISION", 117);
/**
 * @use \Bitrix\Im\Revision::getMobile()
 * @deprecated
 */
define("IM_REVISION_MOBILE", 8); // sync with im.recent/im.dialog components

define("IM_MESSAGE_SYSTEM", "S");
define("IM_MESSAGE_PRIVATE", "P");
define("IM_MESSAGE_CHAT", "C");
define("IM_MESSAGE_OPEN", "O");
define("IM_MESSAGE_COMMENT", "T");
define("IM_MESSAGE_OPEN_LINE", "L");

define("IM_CHAT_TYPE_PERSONAL", "PERSONAL");
/**
 * @use const IM_MESSAGE_CHAT
 * @deprecated
 */
define("IM_MESSAGE_GROUP", "C");

define("IM_NOTIFY_MESSAGE", 0);
define("IM_NOTIFY_CONFIRM", 1);
define("IM_NOTIFY_FROM", 2);
define("IM_NOTIFY_SYSTEM", 4);

define("IM_STATUS_UNREAD", 0);
define("IM_STATUS_NOTIFY", 1);
define("IM_STATUS_READ", 2);

define("IM_MESSAGE_STATUS_RECEIVED", 'received');
define("IM_MESSAGE_STATUS_ERROR", 'error');
define("IM_MESSAGE_STATUS_DELIVERED", 'delivered');

define("IM_CALL_NONE", 0);
define("IM_CALL_VIDEO", 1);
define("IM_CALL_AUDIO", 2);

define("IM_MAIL_SKIP", '#SKIP#');

define("IM_CALL_STATUS_NONE", 0);
define("IM_CALL_STATUS_WAIT", 1);
define("IM_CALL_STATUS_ANSWER", 2);
define("IM_CALL_STATUS_DECLINE", 3);

define("IM_CALL_END_BUSY", 'busy');
define("IM_CALL_END_DECLINE", 'decline');
define("IM_CALL_END_TIMEOUT", 'waitTimeout');
define("IM_CALL_END_ACCESS", 'errorAccess');
define("IM_CALL_END_OFFLINE", 'errorOffline');

define("IM_SPEED_NOTIFY", 1);
define("IM_SPEED_MESSAGE", 2);
define("IM_SPEED_GROUP", 3);

define("IM_CHECK_UPDATE", 'update');
define("IM_CHECK_DELETE", 'delete');

define("IM_DESKTOP_WINDOWS", 'windows');
define("IM_DESKTOP_MAC", 'mac');
define("IM_DESKTOP_LINUX", 'linux');

define("IM_NOTIFY_FEATURE_SITE", "site");
define("IM_NOTIFY_FEATURE_XMPP", "xmpp");
define("IM_NOTIFY_FEATURE_MAIL", "mail");
define("IM_NOTIFY_FEATURE_PUSH", "push");

CModule::AddAutoloadClasses(
	"im",
	array(
		"im" => "install/index.php",
		"CIMSettings" => "classes/general/im_settings.php",
		"CIMMessenger" => "classes/general/im_messenger.php",
		"CIMNotify" => "classes/general/im_notify.php",
		"CIMContactList" => "classes/mysql/im_contact_list.php",
		"CIMChat" => "classes/general/im_chat.php",
		"CIMMessage" => "classes/general/im_message.php",
		"CIMMessageLink" => "classes/general/im_message_param.php",
		"CIMMessageParam" => "classes/general/im_message_param.php",
		"CIMMessageParamAttach" => "classes/general/im_message_param.php",
		"CIMHistory" => "classes/general/im_history.php",
		"CIMEvent" => "classes/general/im_event.php",
		"CIMCall" => "classes/general/im_call.php",
		"CIMMail" => "classes/general/im_mail.php",
		"CIMConvert" => "classes/general/im_convert.php",
		"CIMNotifySchema" => "classes/general/im_notify_schema.php",
		"CIMRestService" => "classes/general/im_rest.php",
		"DesktopApplication" => "classes/general/im_event.php",
		"CIMStatus" => "classes/general/im_status.php",
		"CIMDisk" => "classes/general/im_disk.php",
		"CIMShare" => "classes/general/im_share.php",
	)
);

$isLegacyChatActivated = \Bitrix\Im\Settings::isLegacyChatActivated();

$jsCoreRel = [
	'ui.design-tokens',
	'ui.fonts.opensans',
	'im_desktop_utils',
	'resize_observer',
	'im_common',
	'im.lib.localstorage',
	'clipboard',
	'sidepanel',
	'loader',
	'ui.notification',
	'ui.alerts',
	'ui.vue',
	'ui.buttons',
	'ui.switcher',
	'ui.hint',
	'im.application.launch',
	'im.old-chat-embedding.application.left-panel',
	'im.old-chat-embedding.application.sidebar',
];

$jsCoreRelMobile = array('im_common', 'uploader', 'mobile.pull.client');
if (IsModuleInstalled('voximplant'))
{
	$jsCoreRel[] = 'voximplant';
	$jsCoreRel[] = 'voximplant.phone-calls';
	$jsCoreRelMobile[] = 'mobile_voximplant';
}
if (IsModuleInstalled('disk'))
{
	$jsCoreRel[] = 'file_dialog';
	$jsCoreRel[] = 'im.integration.viewer';
}
if (IsModuleInstalled('calendar'))
{
	$jsCoreRel[] = 'calendar.sliderloader';
}
if (IsModuleInstalled('pull'))
{
	$jsCoreRel[] = 'webrtc';
	$jsCoreRel[] = 'webrtc_adapter';
}
if (IsModuleInstalled('pull') || IsModuleInstalled('disk'))
{
	$jsCoreRel[] = 'uploader';
}

$jsCoreRelPage = $jsCoreRel;
$jsCoreRelPage[] = 'im_window';

$jsIm = [
	'/bitrix/js/im/im.js'
];

CJSCore::RegisterExt('im_call_compatible', array(
	'css' => '/bitrix/js/im/css/common.css',
	'lang' => ['/bitrix/modules/im/js_common.php', '/bitrix/modules/im/lang/'.LANGUAGE_ID.'/js_im.php'],
));

CJSCore::RegisterExt('im_common', array(
	'js' => '/bitrix/js/im/common.js',
	'css' => ['/bitrix/js/im/css/common.css', '/bitrix/js/im/css/dark_im.css'],
	'lang' => '/bitrix/modules/im/js_common.php',
	'rel' => array('ui.design-tokens', 'ls', 'ajax', 'date', 'fx', 'user', 'rest.client', 'phone_number', 'loader', 'ui.viewer', 'main.md5', 'im.debug', 'ui.notification')
));

CJSCore::RegisterExt('im_web', array(
	'js' => $jsIm,
	'css' => array(
		'/bitrix/js/im/css/im.css',
	),
	'lang' => '/bitrix/modules/im/lang/'.LANGUAGE_ID.'/js_im.php',
	'rel' => $jsCoreRel
));

CJSCore::RegisterExt('im_page', array(
	'js' => $jsIm,
	'css' => array(
		'/bitrix/js/im/css/im.css',
		'/bitrix/js/im/css/call/keypad.css',
		'/bitrix/js/im/css/call/view.css',
		'/bitrix/js/im/css/call/sidebar.css',
		'/bitrix/js/im/css/call/promo-popup.css',
	),
	'lang' => '/bitrix/modules/im/js_im.php',
	'rel' => $jsCoreRelPage
));

CJSCore::RegisterExt('im_mobile', array(
	'js' => '/bitrix/js/im/mobile.js',
	'lang' => '/bitrix/modules/im/js_mobile.php',
	'rel' => $jsCoreRelMobile
));

CJSCore::RegisterExt('im_mobile_dialog', array(
	'js' => '/bitrix/js/im/mobile_dialog.js',
	'lang' => '/bitrix/modules/im/js_mobile.php',
	'rel' => $jsCoreRelMobile
));

CJSCore::RegisterExt('im_window', array(
	'js' => '/bitrix/js/im/window.js',
	'css' => '/bitrix/js/im/css/window.css',
	'lang' => '/bitrix/modules/im/js_window.php',
	'rel' => Array('ui.design-tokens', 'popup', 'fx', 'json', 'translit', 'im.component.conference.conference-create', 'ui.alerts'),
));

CJSCore::RegisterExt('im_desktop', array(
	'js' => '/bitrix/js/im/desktop.js',
	'lang' => '/bitrix/modules/im/js_desktop.php',
	'rel' => array('im_page', 'socnetlogdest', 'im.lib.logger'),
));

CJSCore::RegisterExt('im_desktop_utils', array(
	'js' => '/bitrix/js/im/desktop_utils.js',
));

CJSCore::RegisterExt('im_timecontrol', array(
	'js' => '/bitrix/js/im/timecontrol.es6.js',
	'rel' => array('timecontrol'),
));

if ($isLegacyChatActivated)
{
	$asset = Asset::getInstance();
	$asset->addJsKernelInfo('im', array_merge(['/bitrix/js/im/common.js', '/bitrix/js/im/window.js'], $jsIm));
	$asset->addCssKernelInfo('im', array('/bitrix/js/im/css/common.css', '/bitrix/js/im/css/dark_im.css', '/bitrix/js/im/css/window.css', '/bitrix/js/im/css/im.css', '/bitrix/js/im/css/call/view.css', '/bitrix/js/im/css/call/sidebar.css', '/bitrix/js/im/css/call/promo-popup.css'));
}

/* Copyrights */

EventManager::getInstance()->addEventHandler('main', 'onGetThirdPartySoftware', function() {
	return new EventResult(EventResult::SUCCESS, [
		(new Copyright("Emoji-test-regex-pattern v15.1"))
			->setCopyright(" (c) Copyright Mathias Bynens <https://mathiasbynens.be/>")
			->setProductUrl('https://github.com/mathiasbynens/emoji-test-regex-pattern/')
			->setLicence(Copyright::LICENCE_MIT)
	]);
});