<?
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
define("IM_MESSAGE_THREAD", "T");
define("IM_MESSAGE_OPEN_LINE", "L");
/**
 * @use const IM_MESSAGE_CHAT
 * @deprecated
 */
define("IM_MESSAGE_GROUP", "C");

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

define("IM_NOTIFY_FEATURE_SITE", "site");
define("IM_NOTIFY_FEATURE_XMPP", "xmpp");
define("IM_NOTIFY_FEATURE_MAIL", "mail");
define("IM_NOTIFY_FEATURE_PUSH", "push");

global $DBType;

CModule::AddAutoloadClasses(
	"im",
	array(
		"CIMSettings" => "classes/general/im_settings.php",
		"CIMMessenger" => "classes/general/im_messenger.php",
		"CIMNotify" => "classes/general/im_notify.php",
		"CIMContactList" => "classes/".$DBType."/im_contact_list.php",
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
		"CIMHint" => "classes/general/im_hint.php",
		"CIMTableSchema" => "classes/general/im_table_schema.php",
		"CIMNotifySchema" => "classes/general/im_notify_schema.php",
		"CIMRestService" => "classes/general/im_rest.php",
		"DesktopApplication" => "classes/general/im_event.php",
		"CIMStatus" => "classes/general/im_status.php",
		"CIMDisk" => "classes/general/im_disk.php",
		"CIMShare" => "classes/general/im_share.php",
		"\\Bitrix\\Im\\ChatTable" => "lib/model/chat.php",
		"\\Bitrix\\Im\\MessageTable" => "lib/model/message.php",
		"\\Bitrix\\Im\\MessageParamTable" => "lib/model/messageparam.php",
		"\\Bitrix\\Im\\RecentTable" => "lib/model/recent.php",
		"\\Bitrix\\Im\\RelationTable" => "lib/model/relation.php",
		"\\Bitrix\\Im\\StatusTable" => "lib/model/status.php",
	)
);

$jsCoreRel = array('im_common', 'im_phone_call_view', 'clipboard', 'sidepanel');
$jsCoreRelMobile = array('im_common', 'uploader');
if (IsModuleInstalled('voximplant'))
{
	$jsCoreRel[] = 'voximplant';
	$jsCoreRelMobile[] = 'mobile_voximplant';
}
if (IsModuleInstalled('disk'))
{
	$jsCoreRel[] = 'file_dialog';
}
if (IsModuleInstalled('pull'))
{
	$jsCoreRel[] = 'webrtc';
}
if (IsModuleInstalled('pull') || IsModuleInstalled('disk'))
{
	$jsCoreRel[] = 'uploader';
}

$jsCoreRelPage = $jsCoreRel;
$jsCoreRelPage[] = 'im_window';

CJSCore::RegisterExt('im_common', array(
	'js' => '/bitrix/js/im/common.js',
	'css' => '/bitrix/js/im/css/common.css',
	'lang' => '/bitrix/modules/im/lang/'.LANGUAGE_ID.'/js_common.php',
	'rel' => array('ls', 'ajax', 'date', 'fx', 'user', 'restclient', 'phone_number', 'loader')
));

CJSCore::RegisterExt('im_phone_call_view', array(
	'js' => '/bitrix/js/im/phone_call_view.js',
	'css' => array('/bitrix/js/im/css/phone_call_view.css', '/bitrix/components/bitrix/crm.card.show/templates/.default/style.css'),
	'lang' => '/bitrix/modules/im/lang/'.LANGUAGE_ID.'/js_phone_call_view.php',
	'rel' => array('applayout', 'crm_form_loader')
));

CJSCore::RegisterExt('im_web', array(
	'js' => '/bitrix/js/im/im.js',
	'css' => '/bitrix/js/im/css/im.css',
	'lang' => '/bitrix/modules/im/lang/'.LANGUAGE_ID.'/js_im.php',
	'rel' => $jsCoreRel
));

CJSCore::RegisterExt('im_page', array(
	'js' => '/bitrix/js/im/im.js',
	'css' => '/bitrix/js/im/css/im.css',
	'lang' => '/bitrix/modules/im/lang/'.LANGUAGE_ID.'/js_im.php',
	'rel' => $jsCoreRelPage
));

CJSCore::RegisterExt('im_mobile', array(
	'js' => '/bitrix/js/im/mobile.js',
	'lang' => '/bitrix/modules/im/lang/'.LANGUAGE_ID.'/js_mobile.php',
	'rel' => $jsCoreRelMobile
));

CJSCore::RegisterExt('im_mobile_dialog', array(
	'js' => '/bitrix/js/im/mobile_dialog.js',
	'lang' => '/bitrix/modules/im/lang/'.LANGUAGE_ID.'/js_mobile.php',
	'rel' => $jsCoreRelMobile
));

CJSCore::RegisterExt('im_window', array(
	'js' => '/bitrix/js/im/window.js',
	'css' => '/bitrix/js/im/css/window.css',
	'lang' => '/bitrix/modules/im/lang/'.LANGUAGE_ID.'/js_window.php',
	'rel' => Array('popup', 'fx', 'json', 'translit'),
));

CJSCore::RegisterExt('meetecho_janus', array(
	'js' => '/bitrix/js/im/webrtc/janus.js'
));

CJSCore::RegisterExt('im_call', array(
	'js' => '/bitrix/js/im/call.js',
	'css' => '/bitrix/js/im/css/call.css',
	'lang' => '/bitrix/modules/im/lang/'.LANGUAGE_ID.'/js_call.php',
	'rel' => Array('webrtc_adapter', 'meetecho_janus')
));

CJSCore::RegisterExt('im_desktop', array(
	'js' => '/bitrix/js/im/desktop.js',
	'lang' => '/bitrix/modules/im/lang/'.LANGUAGE_ID.'/js_desktop.php',
	'rel' => array('im_page', 'im_call'),
));

CJSCore::RegisterExt('im_timecontrol', array(
	'js' => '/bitrix/js/im/timecontrol.js',
	'rel' => array('timecontrol'),
));

$GLOBALS["APPLICATION"]->AddJSKernelInfo('im', array('/bitrix/js/im/common.js', '/bitrix/js/im/window.js', '/bitrix/js/im/im.js'));
$GLOBALS["APPLICATION"]->AddCSSKernelInfo('im', array('/bitrix/js/im/css/common.css', '/bitrix/js/im/css/window.css', '/bitrix/js/im/css/im.css'));
?>