<?
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Integration;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

IncludeModuleLangFile(__FILE__);

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/socialnetwork/tools.php");

define("SONET_RELATIONS_FRIEND", "F");
define("SONET_RELATIONS_REQUEST", "Z");
define("SONET_RELATIONS_BAN", "B");

define("SONET_ROLES_OWNER", "A");
define("SONET_ROLES_MODERATOR", "E");
define("SONET_ROLES_USER", "K");
define("SONET_ROLES_BAN", "T");
define("SONET_ROLES_REQUEST", "Z");
define("SONET_ROLES_ALL", "N");
define("SONET_ROLES_AUTHORIZED", "L");

define("SONET_RELATIONS_TYPE_ALL", "A");
define("SONET_RELATIONS_TYPE_AUTHORIZED", "C");
define("SONET_RELATIONS_TYPE_FRIENDS2", "E");
define("SONET_RELATIONS_TYPE_FRIENDS", "M");
define("SONET_RELATIONS_TYPE_NONE", "Z");

define("SONET_INITIATED_BY_USER", "U");
define("SONET_INITIATED_BY_GROUP", "G");

define("SONET_MESSAGE_SYSTEM", "S");
define("SONET_MESSAGE_PRIVATE", "P");

define("DisableSonetLogVisibleSubscr", true);

$arClasses = array(
	"CSocNetGroup" => "classes/mysql/group.php",
	"CSocNetGroupSubject" => "classes/mysql/group_subject.php",
	"CSocNetUserToGroup" => "classes/mysql/user_group.php",
	"CSocNetFeatures" => "classes/mysql/group_features.php",
	"CSocNetFeaturesPerms" => "classes/mysql/group_features_perms.php",
	"CSocNetUserRelations" => "classes/mysql/user_relations.php",
	"CSocNetSmile" => "classes/mysql/smile.php",
	"CSocNetUser" => "classes/mysql/user.php",
	"CSocNetUserPerms" => "classes/mysql/user_perms.php",
	"CSocNetUserEvents" => "classes/mysql/user_events.php",
	"CSocNetMessages" => "classes/mysql/messages.php",
	"CSocNetEventUserView" => "classes/mysql/event_user_view.php",
	"CSocNetLog" => "classes/mysql/log.php",
	"CSocNetLogTools" => "classes/general/log_tools.php",
	"CSocNetLogToolsPhoto" => "classes/general/log_tools_photo.php",
	"CSocNetForumComments" => "classes/general/log_forum_comments.php",
	"CSocNetLogRights" => "classes/general/log_rights.php",
	"CSocNetLogPages" => "classes/general/log_pages.php",
	"CSocNetLogFollow" => "classes/general/log_follow.php",
	"CSocNetLogSmartFilter" => "classes/mysql/log_smartfilter.php",
	"CSocNetLogRestService" => "classes/general/rest.php",
	"logTextParser" => "classes/general/log_tools.php",
	"CSocNetPhotoCommentEvent" => "classes/general/log_tools_photo.php",
	"CSocNetLogComments" => "classes/mysql/log_comments.php",
	"CSocNetLogEvents" => "classes/mysql/log_events.php",
	"CSocNetLogCounter" => "classes/mysql/log_counter.php",
	"CSocNetLogFavorites" => "classes/mysql/log_favorites.php",
	"CSocNetLogComponent" => "classes/general/log_tools.php",
	"CSocNetSubscription" => "classes/mysql/subscription.php",
	"CSocNetSearch" => "classes/general/search.php",
	"CSocNetSearchReindex" => "classes/general/search_reindex.php",
	"CSocNetTextParser" => "classes/general/functions.php",
	"CSocNetTools" => "classes/general/functions.php",
	"CSocNetAllowed" => "classes/general/functions.php",
	"CSocNetGroupAuthProvider" => "classes/general/authproviders.php",
	"CSocNetUserAuthProvider" => "classes/general/authproviders.php",
	"CSocNetLogDestination" => "classes/general/log_destination.php",
	"CSocNetNotifySchema" => "classes/general/notify_schema.php",
	"CSocNetPullSchema" => "classes/general/notify_schema.php",
	"socialnetwork" => "install/index.php",
);
CModule::AddAutoloadClasses("socialnetwork", $arClasses);

global $arSocNetAllowedRolesForUserInGroup;
$arSocNetAllowedRolesForUserInGroup = array(SONET_ROLES_MODERATOR, SONET_ROLES_USER, SONET_ROLES_BAN, SONET_ROLES_REQUEST, SONET_ROLES_OWNER);

global $arSocNetAllowedRolesForFeaturesPerms;
$arSocNetAllowedRolesForFeaturesPerms = array(SONET_ROLES_MODERATOR, SONET_ROLES_USER, SONET_ROLES_ALL, SONET_ROLES_OWNER, SONET_ROLES_AUTHORIZED);

global $arSocNetAllowedInitiatePerms;
$arSocNetAllowedInitiatePerms = array(SONET_ROLES_MODERATOR, SONET_ROLES_USER, SONET_ROLES_OWNER);

global $arSocNetAllowedSpamPerms;
$arSocNetAllowedSpamPerms = array(SONET_ROLES_MODERATOR, SONET_ROLES_USER, SONET_ROLES_OWNER, SONET_ROLES_ALL);

global $arSocNetAllowedRelations;
$arSocNetAllowedRelations = array(SONET_RELATIONS_FRIEND, SONET_RELATIONS_REQUEST, SONET_RELATIONS_BAN);

global $arSocNetAllowedRelationsType;
$arSocNetAllowedRelationsType = array(SONET_RELATIONS_TYPE_ALL, SONET_RELATIONS_TYPE_FRIENDS2, SONET_RELATIONS_TYPE_FRIENDS, SONET_RELATIONS_TYPE_NONE, SONET_RELATIONS_TYPE_AUTHORIZED);

global $arSocNetAllowedInitiatedByType;
$arSocNetAllowedInitiatedByType = array(SONET_INITIATED_BY_USER, SONET_INITIATED_BY_GROUP);

define("SONET_ENTITY_GROUP", "G");
define("SONET_ENTITY_USER", "U");

define("SONET_SUBSCRIBE_ENTITY_GROUP", "G");
define("SONET_SUBSCRIBE_ENTITY_USER", "U");

global $arSocNetAllowedEntityTypes;
$arSocNetAllowedEntityTypes = array(SONET_ENTITY_GROUP, SONET_ENTITY_USER);

$arEntityTypesDescTmp = array(
	SONET_SUBSCRIBE_ENTITY_GROUP => array(
		"TITLE_LIST" => GetMessage("SOCNET_LOG_LIST_G_ALL"),
		"TITLE_LIST_MY" => GetMessage("SOCNET_LOG_LIST_G_ALL_MY"),
		"TITLE_ENTITY" => GetMessage("SOCNET_LOG_G"),
		"TITLE_ENTITY_XDI" => GetMessage("SOCNET_LOG_XDI_G"),
		"TITLE_SETTINGS_ALL" => GetMessage("SOCNET_LOG_GROUP_SETTINGS_ALL"),
		"TITLE_SETTINGS_ALL_1" => GetMessage("SOCNET_LOG_GROUP_SETTINGS_ALL_1"),
		"TITLE_SETTINGS_ALL_2" => GetMessage("SOCNET_LOG_GROUP_SETTINGS_ALL_2"),
		"USE_CB_FILTER" => "Y",
		"HAS_MY" => "Y",
		"CLASS_MY"	=> "CSocNetTools",
		"METHOD_MY"	=> "GetMyGroups",
		"CLASS_OF" => "CSocNetTools",
		"METHOD_OF"	=> "GetGroupUsers",
		"CLASS_MY_BY_ID" => "CSocNetTools",
		"METHOD_MY_BY_ID" => "IsMyGroup",
		"CLASS_DESC_GET" => "CSocNetGroup",
		"METHOD_DESC_GET" => "GetByID",
		"CLASS_DESC_SHOW" => "CSocNetLogTools",
		"METHOD_DESC_SHOW" => "ShowGroup",
		"URL_PARAM_KEY" => "PATH_TO_GROUP",
		"URL_PATTERN" => "group_id",
		"HAS_SITE_ID" => "Y",
		"XDIMPORT_ALLOWED" => "Y"
	),
	SONET_SUBSCRIBE_ENTITY_USER	=> array(
		"TITLE_LIST" => GetMessage("SOCNET_LOG_LIST_U_ALL"),
		"TITLE_LIST_MY" => GetMessage("SOCNET_LOG_LIST_U_ALL_MY"),
		"TITLE_ENTITY" => GetMessage("SOCNET_LOG_U"),
		"TITLE_ENTITY_XDI" => GetMessage("SOCNET_LOG_XDI_U"),
		"TITLE_SETTINGS_ALL" => GetMessage("SOCNET_LOG_USER_SETTINGS_ALL"),
		"TITLE_SETTINGS_ALL_1" => GetMessage("SOCNET_LOG_USER_SETTINGS_ALL_1"),
		"TITLE_SETTINGS_ALL_2" => GetMessage("SOCNET_LOG_USER_SETTINGS_ALL_2"),
		"USE_CB_FILTER" => "Y",
		"HAS_CB" => "Y",
		"HAS_MY" => "Y",
		"CLASS_MY" => "CSocNetTools",
		"METHOD_MY"	=> "GetMyUsers",
		"CLASS_OF" => "CSocNetTools",
		"METHOD_OF" => "GetMyUsers",
		"CLASS_MY_BY_ID" => "CSocNetTools",
		"METHOD_MY_BY_ID" => "IsMyUser",
		"CLASS_DESC_GET" => "CSocNetUser",
		"METHOD_DESC_GET" => "GetByID",
		"CLASS_DESC_SHOW" => "CSocNetLogTools",
		"METHOD_DESC_SHOW" => "ShowUser",
		"URL_PARAM_KEY" => "PATH_TO_USER",
		"URL_PATTERN" => "user_id",
		"XDIMPORT_ALLOWED" => "Y"
	)
);

if (
	!CSocNetUser::IsFriendsAllowed()
	|| !CBXFeatures::IsFeatureEnabled("Friends")
)
{
	$arEntityTypesDescTmp[SONET_SUBSCRIBE_ENTITY_USER]["HAS_MY"] = "N";
}

$arEntityTypeTmp = array(
	SONET_SUBSCRIBE_ENTITY_USER,
	SONET_SUBSCRIBE_ENTITY_GROUP
);

CSocNetAllowed::AddAllowedEntityType($arEntityTypeTmp);

foreach ($arEntityTypesDescTmp as $entityTypeDescCode => $arEntityTypeDesc)
{
	CSocNetAllowed::AddAllowedEntityTypeDesc($entityTypeDescCode, $arEntityTypeDesc);
}

if (
	!defined("BX_MOBILE_LOG")
	|| BX_MOBILE_LOG != true
)
{
	Loader::includeModule('intranet');
	IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/socialnetwork/install/js/log_destination.php');

	CJSCore::RegisterExt('socnetlogdest', array(
		'js' => '/bitrix/js/socialnetwork/log-destination.js',
		'css' => [
			'/bitrix/js/intranet/intranet-common.css',
			'/bitrix/js/main/core/css/core_finder.css'
		],
		'lang_additional' => array(
			'LM_POPUP_TITLE' => GetMessage("LM_POPUP_TITLE"),
			'LM_POPUP_TAB_LAST' => GetMessage("LM_POPUP_TAB_LAST"),
			'LM_POPUP_TAB_SG' => GetMessage("LM_POPUP_TAB_SG"),
			'LM_POPUP_TAB_SG_PROJECT' => GetMessage("LM_POPUP_TAB_SG_PROJECT"),
			'LM_POPUP_TAB_STRUCTURE' => GetMessage("LM_POPUP_TAB_STRUCTURE"),
			'LM_POPUP_TAB_EMAIL' => GetMessage("LM_POPUP_TAB_EMAIL"),
			'LM_POPUP_TAB_MAIL_CONTACTS' => GetMessage("LM_POPUP_TAB_MAIL_CONTACTS"),
			'LM_POPUP_TAB_CRMEMAIL' => GetMessage("LM_POPUP_TAB_CRMEMAIL"),
			'LM_POPUP_TAB_STRUCTURE_EXTRANET' => GetMessage("LM_POPUP_TAB_STRUCTURE_EXTRANET"),
			'LM_POPUP_CHECK_STRUCTURE' => GetMessage("LM_POPUP_CHECK_STRUCTURE"),
			'LM_POPUP_TAB_LAST_USERS' => GetMessage("LM_POPUP_TAB_LAST_USERS"),
			'LM_POPUP_TAB_LAST_NETWORK' => GetMessage("LM_POPUP_TAB_LAST_NETWORK"),
			'LM_POPUP_TAB_LAST_CRMEMAILS' => GetMessage("LM_POPUP_TAB_LAST_CRMEMAILS"),
			'LM_POPUP_TAB_LAST_MAIL_CONTACTS' => GetMessage("LM_POPUP_TAB_LAST_MAIL_CONTACTS"),
			'LM_POPUP_TAB_LAST_CONTACTS' => GetMessage("LM_POPUP_TAB_LAST_CONTACTS"),
			'LM_POPUP_TAB_LAST_COMPANIES' => GetMessage("LM_POPUP_TAB_LAST_COMPANIES"),
			'LM_POPUP_TAB_LAST_LEADS' => GetMessage("LM_POPUP_TAB_LAST_LEADS"),
			'LM_POPUP_TAB_LAST_DEALS' => GetMessage("LM_POPUP_TAB_LAST_DEALS"),
			'LM_POPUP_TAB_LAST_SG' => GetMessage("LM_POPUP_TAB_LAST_SG"),
			'LM_POPUP_TAB_LAST_SG_PROJECT' => GetMessage("LM_POPUP_TAB_LAST_SG_PROJECT"),
			'LM_POPUP_TAB_LAST_STRUCTURE' => GetMessage("LM_POPUP_TAB_LAST_STRUCTURE"),
			'LM_POPUP_TAB_SEARCH' => GetMessage("LM_POPUP_TAB_SEARCH"),
			'LM_SEARCH_PLEASE_WAIT' => GetMessage("LM_SEARCH_PLEASE_WAIT"),
			'LM_EMPTY_LIST' => GetMessage("LM_EMPTY_LIST"),
			'LM_PLEASE_WAIT' => GetMessage("LM_PLEASE_WAIT"),
			'LM_CREATE_SONETGROUP_TITLE' => GetMessage("LM_CREATE_SONETGROUP_TITLE"),
			'LM_CREATE_SONETGROUP_BUTTON_CREATE' => GetMessage("LM_CREATE_SONETGROUP_BUTTON_CREATE"),
			'LM_CREATE_SONETGROUP_BUTTON_CANCEL' => GetMessage("LM_CREATE_SONETGROUP_BUTTON_CANCEL"),
			'LM_INVITE_EMAIL_USER_BUTTON_OK' => GetMessage("LM_INVITE_EMAIL_USER_BUTTON_OK"),
			'LM_INVITE_EMAIL_USER_TITLE' => GetMessage("LM_INVITE_EMAIL_USER_TITLE"),
			'LM_INVITE_EMAIL_USER_PLACEHOLDER_NAME' => GetMessage("LM_INVITE_EMAIL_USER_PLACEHOLDER_NAME"),
			'LM_INVITE_EMAIL_USER_PLACEHOLDER_LAST_NAME' => GetMessage("LM_INVITE_EMAIL_USER_PLACEHOLDER_LAST_NAME"),
			'LM_INVITE_EMAIL_CRM_CREATE_CONTACT' => GetMessage("LM_INVITE_EMAIL_CRM_CREATE_CONTACT"),
			'LM_POPUP_WAITER_TEXT' => GetMessage("LM_POPUP_WAITER_TEXT"),
			'LM_POPUP_SEARCH_NETWORK' => GetMessage("LM_POPUP_SEARCH_NETWORK"),
		),
		'rel' => array('core', 'popup', 'json', 'finder')
	));
}

$transformationLimit = 0;
if(
	ModuleManager::isModuleInstalled('disk') &&
	ModuleManager::isModuleInstalled('transformer') &&
	Option::get('disk', 'disk_allow_video_transformation', 'N') == 'Y'
)
{
	$transformationLimit = Option::get('disk', 'disk_max_size_for_video_transformation', 300) * 1024 * 1024;
}
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/socialnetwork/install/js/video_recorder.php');
CJSCore::RegisterExt('videorecorder', array(
	'js' => '/bitrix/js/socialnetwork/video_recorder.js',
	'css' => [
		'/bitrix/js/intranet/intranet-common.css',
		'/bitrix/js/socialnetwork/css/video_recorder.css'
	],
	'lang_additional' => array(
		'BLOG_VIDEO_RECORD_BUTTON' => GetMessage('BLOG_VIDEO_RECORD_BUTTON'),
		'BLOG_VIDEO_RECORD_CANCEL_BUTTON' => GetMessage('BLOG_VIDEO_RECORD_CANCEL_BUTTON'),
		'BLOG_VIDEO_RECORD_LOGO' => GetMessage('BLOG_VIDEO_RECORD_LOGO'),
		'BLOG_VIDEO_RECORD_STOP_BUTTON' => GetMessage('BLOG_VIDEO_RECORD_STOP_BUTTON'),
		'BLOG_VIDEO_RECORD_USE_BUTTON' => GetMessage('BLOG_VIDEO_RECORD_USE_BUTTON'),
		'BLOG_VIDEO_RECORD_IN_PROGRESS_LABEL' => GetMessage('BLOG_VIDEO_RECORD_IN_PROGRESS_LABEL'),
		'BLOG_VIDEO_RECORD_AGREE' => GetMessage('BLOG_VIDEO_RECORD_AGREE'),
		'BLOG_VIDEO_RECORD_CLOSE' => GetMessage('BLOG_VIDEO_RECORD_CLOSE'),
		'BLOG_VIDEO_RECORD_ASK_PERMISSIONS' => GetMessage('BLOG_VIDEO_RECORD_ASK_PERMISSIONS'),
		'BLOG_VIDEO_RECORD_DEFAULT_CAMERA_NAME' => GetMessage('BLOG_VIDEO_RECORD_DEFAULT_CAMERA_NAME'),
		'BLOG_VIDEO_RECORD_REQUIREMENTS' => GetMessage('BLOG_VIDEO_RECORD_REQUIREMENTS'),
		'BLOG_VIDEO_RECORD_REQUIREMENTS_TITLE' => GetMessage('BLOG_VIDEO_RECORD_REQUIREMENTS_TITLE'),
		'BLOG_VIDEO_RECORD_PERMISSIONS_ERROR' => GetMessage('BLOG_VIDEO_RECORD_PERMISSIONS_ERROR'),
		'BLOG_VIDEO_RECORD_PERMISSIONS_TITLE' => GetMessage('BLOG_VIDEO_RECORD_PERMISSIONS_TITLE'),
		'BLOG_VIDEO_RECORD_SPOTLIGHT_MESSAGE' => GetMessage('BLOG_VIDEO_RECORD_SPOTLIGHT_MESSAGE'),
		'DISK_VIDEO_TRANSFORMATION_LIMIT' => $transformationLimit,
		'BLOG_VIDEO_RECORD_TRANFORM_LIMIT_TEXT' => GetMessage('BLOG_VIDEO_RECORD_TRANFORM_LIMIT_TEXT'),
		'BLOG_VIDEO_RECORD_RESTART_BUTTON' => GetMessage('BLOG_VIDEO_RECORD_RESTART_BUTTON'),
		'BLOG_VIDEO_RECORD_PERMISSIONS_ERROR_TITLE' => GetMessage('BLOG_VIDEO_RECORD_PERMISSIONS_ERROR_TITLE'),
		'BLOG_VIDEO_RECORD_ERROR_CHROME_HTTPS' => GetMessage('BLOG_VIDEO_RECORD_ERROR_CHROME_HTTPS'),
	),
	'rel' => array('core', 'popup', 'ui.fonts.opensans'),
));

CJSCore::RegisterExt('comment_aux', [
	'lang_additional' => [],
	'rel' => [ 'socialnetwork.commentaux' ],
]);

CJSCore::RegisterExt('render_parts', [
	'lang_additional' => [],
	'rel' => [ 'socialnetwork.renderparts' ],
]);

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/socialnetwork/install/js/content_view.php');
CJSCore::RegisterExt('content_view', array(
	'js' => '/bitrix/js/socialnetwork/content_view.js',
	'css' => '/bitrix/js/socialnetwork/css/content_view.css',
	'lang_additional' => array(
		'SONET_CONTENTVIEW_JS_HIDDEN_COUNT' => GetMessage("SONET_CONTENTVIEW_JS_HIDDEN_COUNT"),
	),
	'rel' => ['ui.design-tokens', 'ajax', 'popup', 'main.polyfill.intersectionobserver' ]
));

$arLogEvents = array(
	"system" =>  array(
		"ENTITIES"	=> array(
			SONET_SUBSCRIBE_ENTITY_GROUP => array(
				"TITLE" => GetMessage("SOCNET_LOG_SYSTEM_GROUP"),
				"TITLE_SETTINGS" => GetMessage("SOCNET_LOG_SYSTEM_GROUP_SETTINGS"),
				"TITLE_SETTINGS_1" => GetMessage("SOCNET_LOG_SYSTEM_GROUP_SETTINGS_1"),
				"TITLE_SETTINGS_2" => GetMessage("SOCNET_LOG_SYSTEM_GROUP_SETTINGS_2"),
				"OPERATION" => "viewsystemevents",
			),
			SONET_SUBSCRIBE_ENTITY_USER => array(
				"TITLE" => GetMessage("SOCNET_LOG_SYSTEM_USER"),
				"TITLE_SETTINGS" => GetMessage("SOCNET_LOG_SYSTEM_USER_SETTINGS"),
				"TITLE_SETTINGS_1" => GetMessage("SOCNET_LOG_SYSTEM_USER_SETTINGS_1"),
				"TITLE_SETTINGS_2" => GetMessage("SOCNET_LOG_SYSTEM_USER_SETTINGS_2"),
				"OPERATION" => "viewprofile"
			)
		),
		"FULL_SET" => array("system", "system_friends", "system_groups"),
		"CLASS_FORMAT"	=> "CSocNetLogTools",
		"METHOD_FORMAT" => "FormatEvent_System"
	),
	"system_groups" => array(
		"ENTITIES" => array(
			SONET_SUBSCRIBE_ENTITY_USER => array(
				"TITLE" => GetMessage("SOCNET_LOG_SYSTEM_GROUPS_USER"),
				"OPERATION" => "viewgroups"
			)
		),
		"HIDDEN" => true,
		"CLASS_FORMAT" => "CSocNetLogTools",
		"METHOD_FORMAT" => "FormatEvent_SystemGroups"
	),
	"system_friends" =>  array(
		"ENTITIES" => array(
			SONET_SUBSCRIBE_ENTITY_USER => array(
				"TITLE" => GetMessage("SOCNET_LOG_SYSTEM_FRIENDS_USER"),
				"OPERATION" => "viewfriends"
			)
		),
		"HIDDEN" => true,
		"CLASS_FORMAT" => "CSocNetLogTools",
		"METHOD_FORMAT" => "FormatEvent_SystemFriends"
	)
);

foreach ($arLogEvents as $eventCode => $arLogEventTmp)
{
	CSocNetAllowed::AddAllowedLogEvent($eventCode, $arLogEventTmp);
}

global $arSocNetUserOperations;
$arSocNetUserOperations = array(
	"invitegroup" => SONET_RELATIONS_TYPE_AUTHORIZED,
	"message" => SONET_RELATIONS_TYPE_AUTHORIZED,
	"videocall" => SONET_RELATIONS_TYPE_AUTHORIZED,
	"viewfriends" => COption::GetOptionString("socialnetwork", "default_user_viewfriends", SONET_RELATIONS_TYPE_ALL),
	"viewgroups" => COption::GetOptionString("socialnetwork", "default_user_viewgroups", SONET_RELATIONS_TYPE_ALL),
	"viewprofile" => COption::GetOptionString("socialnetwork", "default_user_viewprofile", SONET_RELATIONS_TYPE_ALL),
);

global $arSocNetUserEvents;
$arSocNetUserEvents = array(
	"SONET_NEW_MESSAGE",
	"SONET_VIDEO_CALL",
	"SONET_INVITE_FRIEND",
	"SONET_INVITE_GROUP",
	"SONET_AGREE_FRIEND",
	"SONET_BAN_FRIEND"
);

if(
	!IsModuleInstalled("video")
	|| !CBXFeatures::IsFeatureEnabled("VideoConference")
)
{
	unset($arSocNetUserOperations["videocall"]);
	unset($arSocNetUserEvents[1]);
}

if (!CBXFeatures::IsFeatureEnabled("WebMessenger"))
{
	unset($arSocNetUserOperations["message"]);
	unset($arSocNetUserEvents[0]);
}

if (!CBXFeatures::IsFeatureEnabled("Workgroups"))
{
	unset($arSocNetUserOperations["invitegroup"]);
	unset($arSocNetUserOperations["viewgroups"]);
	unset($arSocNetUserEvents[3]);
}

if (!defined("CACHED_b_sonet_group_subjects"))
{
	define("CACHED_b_sonet_group_subjects", 3600);
}

class CSocNetUpdater
{
	function Run($version)
	{
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/updtr".$version.".php");
	}
}

?>