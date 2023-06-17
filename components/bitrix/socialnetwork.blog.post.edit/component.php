<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CCacheManager $CACHE_MANAGER */
/** @global CUserTypeManager $USER_FIELD_MANAGER */

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Component\BlogPostEdit;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Socialnetwork\Helper\Mention;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

global $CACHE_MANAGER, $USER_FIELD_MANAGER;

if (!Loader::includeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));

	return false;
}

if (!Loader::includeModule("socialnetwork"))
{
	return false;
}

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$frameMode = ($request->getQuery('IFRAME') === 'Y');

$feature = "blog";
$arParams['SOCNET_GROUP_ID'] = isset($arParams['SOCNET_GROUP_ID']) ? (int) $arParams['SOCNET_GROUP_ID'] : 0;
$arResult["bExtranetUser"] = (Loader::includeModule("extranet") && !CExtranet::IsIntranetUser());
$arResult["bExtranetSite"] = (Loader::includeModule("extranet") && CExtranet::IsExtranetSite());
$arResult["ERROR_MESSAGE"] = "";

$arParams["ID"] = (int)$arParams["ID"];
$arParams["LAZY_LOAD"] = 'Y';
$arResult['startVideoRecorder'] = (
	!empty($_REQUEST["startVideoRecorder"])
	&& $_REQUEST["startVideoRecorder"] === 'Y'
);

$blogModulePermissions = CMain::getGroupRight('blog');

$arResult['SELECTOR_VERSION'] = (int)($arParams['SELECTOR_VERSION'] ?? 1);

$arResult["SHOW_FULL_FORM"] = (
	(
		!empty($_POST)
		&& (
			!isset($_POST["TYPE"])
			|| $_POST['TYPE'] !== 'AUTH'
		)
	)
	|| $arParams["ID"] > 0
	|| !empty($_REQUEST["WFILES"])
	|| !empty($_REQUEST["bp_setting"])
	|| $arResult['startVideoRecorder']
	|| (
		!empty($arParams["PAGE_ID"])
		&& in_array($arParams["PAGE_ID"], array('user_blog_post_edit_profile', 'user_blog_post_edit_grat', 'user_blog_post_edit_post'))
	)
	|| $arResult['SELECTOR_VERSION'] >= 3
);

$arResult["ALLOW_EMAIL_INVITATION"] = (
	ModuleManager::isModuleInstalled('mail')
	&& ModuleManager::isModuleInstalled('intranet')
	&& (
		!Loader::includeModule('bitrix24')
		|| CBitrix24::isEmailConfirmed()
	)
);

$bCalendar = true;
if (!ModuleManager::isModuleInstalled('intranet')) // Disable calendar feature for non cp
{
	$bCalendar = false;
}

if ($bCalendar && $arResult["bExtranetUser"]) // Disable calendar feature for extranet users
{
	$bCalendar = false;
}

if ($arParams["SOCNET_GROUP_ID"] > 0)
{
	$bCalendar = false;
}
elseif (
	!CSocNetFeaturesPerms::CurrentUserCanPerformOperation(
		SONET_ENTITY_USER,
		$USER->getId(),
		"calendar",
		"view"
	)
)
{
	$bCalendar = false;
}

$arParams["B_CALENDAR"] = $bCalendar;
$arResult["bGroupMode"] = false;

if (
	$arParams['SOCNET_GROUP_ID'] > 0
	|| (int)$arParams['USER_ID'] > 0
)
{
	$arResult['bGroupMode'] = ($arParams['SOCNET_GROUP_ID'] > 0);

	if ($arResult['bGroupMode'])
	{
		if ($arGroupSoNet = CSocNetGroup::GetByID($arParams['SOCNET_GROUP_ID']))
		{
			if (!CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams['SOCNET_GROUP_ID'], $feature))
			{
				ShowError(Loc::getMessage('BLOG_SONET_GROUP_MODULE_NOT_AVAIBLE'));
				return false;
			}
		}
		else
		{
			return false;
		}
	}
}

if (!is_array($arParams["GROUP_ID"]))
{
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
}

foreach ($arParams["GROUP_ID"] as $k=>$v)
{
	if ((int)$v <= 0)
	{
		unset($arParams["GROUP_ID"][$k]);
	}
}

if (empty($arParams["GROUP_ID"]))
{
	$tmpVal = COption::GetOptionString("socialnetwork", "sonet_blog_group", false, SITE_ID);
	if ($tmpVal)
	{
		$arTmpVal = unserialize($tmpVal, [ 'allowed_classes' => false ]);
		if (is_array($arTmpVal))
		{
			$arParams["GROUP_ID"] = $arTmpVal;
		}
		elseif ((int)$tmpVal > 0)
		{
			$arParams["GROUP_ID"] = array($arTmpVal);
		}
	}
}
else
{
	$tmpVal = COption::GetOptionString("socialnetwork", "sonet_blog_group", false, SITE_ID);
	if (!$tmpVal)
	{
		COption::SetOptionString("socialnetwork", "sonet_blog_group", serialize($arParams["GROUP_ID"]), false, SITE_ID);
	}
}

$arParams["BLOG_VAR"] = $arParams["BLOG_VAR"] ?? '';
$arParams["PAGE_VAR"] = $arParams["PAGE_VAR"] ?? '';
$arParams["USER_VAR"] = $arParams["USER_VAR"] ?? '';
$arParams["POST_VAR"] = $arParams["POST_VAR"] ?? '';

if ($arParams["BLOG_VAR"] === '')
{
	$arParams["BLOG_VAR"] = "blog";
}
if ($arParams["PAGE_VAR"] === '')
{
	$arParams["PAGE_VAR"] = "page";
}
if ($arParams["USER_VAR"] === '')
{
	$arParams["USER_VAR"] = "id";
}
if ($arParams["POST_VAR"] === '')
{
	$arParams["POST_VAR"] = "id";
}

$applicationCurPage = $APPLICATION->GetCurPage();

$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if ($arParams["PATH_TO_BLOG"] == '')
{
	$arParams["PATH_TO_BLOG"] = htmlspecialcharsbx($applicationCurPage."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");
}

$arParams["PATH_TO_BLOG"] = CHTTP::urlDeleteParams($arParams["PATH_TO_BLOG"], array("WFILES"));

$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if ($arParams["PATH_TO_POST"] == '')
{
	$arParams["PATH_TO_POST"] = htmlspecialcharsbx($applicationCurPage."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");
}

$arParams["PATH_TO_POST_EDIT"] = trim($arParams["PATH_TO_POST_EDIT"]);
if ($arParams["PATH_TO_POST_EDIT"] == '')
{
	$arParams["PATH_TO_POST_EDIT"] = htmlspecialcharsbx($applicationCurPage."?".$arParams["PAGE_VAR"]."=post_edit&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");
}

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"] ?? '');
if ($arParams['PATH_TO_USER'] === '')
{
	$arParams['PATH_TO_USER'] = htmlspecialcharsbx($applicationCurPage . '?' . $arParams['PAGE_VAR'] . '=user&' . $arParams['USER_VAR'] . '=#user_id#');
}

$arParams["PATH_TO_DRAFT"] = trim($arParams["PATH_TO_DRAFT"] ?? '');
if ($arParams['PATH_TO_DRAFT'] === '')
{
	$arParams['PATH_TO_DRAFT'] = htmlspecialcharsbx($applicationCurPage . '?' . $arParams['PAGE_VAR'] . '=draft&' . $arParams['BLOG_VAR'] . '=#blog#');
}

$arParams['PATH_TO_GROUP_BLOG'] = trim($arParams['PATH_TO_GROUP_BLOG'] ?? '');
if ($arParams['PATH_TO_GROUP_BLOG'] === '')
{
	$arParams['PATH_TO_GROUP_BLOG'] = '/workgroups/group/#group_id#/blog/';
}

if ((string)$arParams['PATH_TO_GROUP_POST'] === '')
{
	$arParams['PATH_TO_GROUP_POST'] = '/workgroups/group/#group_id#/blog/#post_id#/';
}

$arParams['PATH_TO_GROUP_POST_EDIT'] = $arParams['PATH_TO_GROUP_POST_EDIT'] ?? '';
if ($arParams['PATH_TO_GROUP_POST_EDIT'] === '')
{
	$arParams['PATH_TO_GROUP_POST_EDIT'] = '/workgroups/group/#group_id#/blog/edit/#post_id#/';
}

$arParams['PATH_TO_GROUP_DRAFT'] = $arParams['PATH_TO_GROUP_DRAFT'] ?? '';
if ($arParams['PATH_TO_GROUP_DRAFT'] === '')
{
	$arParams['PATH_TO_GROUP_DRAFT'] = '/workgroups/group/#group_id#/blog/draft/';
}

$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]) == '' ? false : trim($arParams["PATH_TO_SMILE"]);

$arParams['DATE_TIME_FORMAT'] = (trim(
	empty($arParams['DATE_TIME_FORMAT'])
		? CDatabase::dateFormatToPHP(CSite::getDateFormat('FULL'))
		: $arParams['DATE_TIME_FORMAT']
));

$arParams['USE_CUT'] = ($arParams['USE_CUT'] === 'Y') ? 'Y' : 'N';

$arParams['EDITOR_RESIZABLE'] = $arParams['EDITOR_RESIZABLE'] ?? '';
$arParams['EDITOR_RESIZABLE'] = ($arParams['EDITOR_RESIZABLE'] !== 'N');
$arParams['EDITOR_CODE_DEFAULT'] = $arParams['EDITOR_CODE_DEFAULT'] ?? '';
$arParams['EDITOR_CODE_DEFAULT'] = ($arParams['EDITOR_CODE_DEFAULT'] === 'Y');
$arParams['EDITOR_DEFAULT_HEIGHT'] = (int) ($arParams['EDITOR_DEFAULT_HEIGHT'] ?? 0);
if ((int)$arParams['EDITOR_DEFAULT_HEIGHT'] <= 0)
{
	$arParams['EDITOR_DEFAULT_HEIGHT'] = '120px';
}

$user_id = $USER->GetID();
$arResult["UserID"] = $user_id;
$arResult["allowVideo"] = COption::GetOptionString("blog","allow_video", "Y");

$arParams['ALLOW_POST_CODE'] = $arParams['ALLOW_POST_CODE'] ?? '';
$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";
$arParams['USE_GOOGLE_CODE'] = $arParams['USE_GOOGLE_CODE'] ?? '';
$arParams["USE_GOOGLE_CODE"] = $arParams["USE_GOOGLE_CODE"] === "Y";
$arParams["IMAGE_MAX_WIDTH"] = 400;
$arParams["IMAGE_MAX_HEIGHT"] = 400;

$arParams["POST_PROPERTY"] = $arParams["POST_PROPERTY"] ?? [];
$arParams["POST_PROPERTY"] = (
	is_array($arParams["POST_PROPERTY"])
	? $arParams["POST_PROPERTY"]
	: [$arParams["POST_PROPERTY"]]
);
$arParams["POST_PROPERTY_SOURCE"] = $arParams["POST_PROPERTY"];
$arParams["POST_PROPERTY"][] = "UF_BLOG_POST_DOC";
$arParams["POST_PROPERTY"][] = "UF_BLOG_POST_IMPRTNT";
$arParams["POST_PROPERTY"][] = "UF_IMPRTANT_DATE_END";

if (
	Loader::includeModule("webdav")
	|| Loader::includeModule("disk")
)
{
	$arParams["POST_PROPERTY"][] = "UF_BLOG_POST_FILE";
	$arParams["POST_PROPERTY"][] = "UF_BLOG_POST_F_EDIT";
}
if (ModuleManager::isModuleInstalled("vote"))
{
	$arParams["POST_PROPERTY"][] = "UF_BLOG_POST_VOTE";
}
$arParams["POST_PROPERTY"][] = "UF_BLOG_POST_URL_PRV";

if (Loader::includeModule('mail'))
{
	$arParams['POST_PROPERTY'][] = 'UF_MAIL_MESSAGE';
}

$arResult['BLOG_POST_LISTS'] = (
	Loader::includeModule("lists")
	&& !$arResult["bExtranetSite"]
	&& !$arParams["SOCNET_GROUP_ID"]
	&& ModuleManager::isModuleInstalled('intranet')
	&& (CListPermissions::CheckAccess($USER, COption::GetOptionString("lists", "livefeed_iblock_type_id"), false) > CListPermissions::ACCESS_DENIED)
);

$arResult['BLOG_POST_TASKS'] = (
	ComponentHelper::checkLivefeedTasksAllowed()
	&& Loader::includeModule("tasks")
);

if (
	$arResult['BLOG_POST_TASKS']
	&& (
		(
			$arResult["bGroupMode"]
			&& !CSocNetFeaturesPerms::CurrentUserCanPerformOperation(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "tasks", "create_tasks")
		) || (
			!$arResult["bGroupMode"]
			&& !\Bitrix\Tasks\Access\TaskAccessController::can($USER->getId(), \Bitrix\Tasks\Access\ActionDictionary::ACTION_TASK_CREATE)
		)
	)
)
{
	$arResult['BLOG_POST_TASKS'] = false;
}

if (
	$arResult['BLOG_POST_TASKS']
	&& Loader::includeModule('bitrix24')
	&& !CBitrix24BusinessTools::isToolAvailable($USER->getId(), 'tasks')
)
{
	$arResult['BLOG_POST_TASKS'] = false;
}

if (
	$arResult['BLOG_POST_TASKS']
	&& $arResult["bGroupMode"]
	&& ($arUserActiveFeatures = CSocNetFeatures::GetActiveFeatures(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"]))
	&& is_array($arUserActiveFeatures)
	&& !in_array('tasks', $arUserActiveFeatures)
)
{
	$arResult['BLOG_POST_TASKS'] = false;
}

$a = new CAccess;
$a->UpdateCodes();

$arResult["perms"] = BLOG_PERMS_DENY;
if ($arResult["bGroupMode"])
{
	if (
		CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "full_post", CSocNetUser::IsCurrentUserModuleAdmin())
		|| $blogModulePermissions >= 'W'
	)
	{
		$arResult["perms"] = BLOG_PERMS_FULL;
	}
	elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "moderate_post"))
	{
		$arResult["perms"] = BLOG_PERMS_MODERATE;
	}
	elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "write_post"))
	{
		$arResult["perms"] = BLOG_PERMS_WRITE;
	}
	elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "premoderate_post"))
	{
		$arResult["perms"] = BLOG_PERMS_PREMODERATE;
	}
}
elseif (
	$arParams["USER_ID"] == $user_id
	|| $blogModulePermissions >= 'W'
	|| CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "blog", "full_post", CSocNetUser::IsCurrentUserModuleAdmin())
)
{
	$arResult["perms"] = BLOG_PERMS_FULL;
}

$arBlog = \Bitrix\Blog\Item\Blog::getByUser(array(
	"GROUP_ID" => $arParams["GROUP_ID"],
	"SITE_ID" => SITE_ID,
	"USER_ID" => $arParams["USER_ID"],
	"USE_SOCNET" => "Y"
));

if (empty($arBlog))
{
	if (empty($arParams["GROUP_ID"]))
	{
		$blogGroupId = Option::get('socialnetwork', 'userbloggroup_id', false, SITE_ID);
		if (empty($blogGroupId))
		{
			$blogGroupIdList = ComponentHelper::getSonetBlogGroupIdList([
				'SITE_ID' => SITE_ID
			]);
			if (!empty($blogGroupIdList))
			{
				$blogGroupId = array_shift($blogGroupIdList);
			}
		}
	}
	else
	{
		$blogGroupId = (
		(is_array($arParams['GROUP_ID']))
			? (int) $arParams['GROUP_ID'][0]
			: (int) $arParams['GROUP_ID']
		);
	}

	$arBlog = ComponentHelper::createUserBlog(array(
		'BLOG_GROUP_ID' => $blogGroupId,
		"USER_ID" => $arParams["USER_ID"],
		"SITE_ID" => SITE_ID,
		"PATH_TO_BLOG" => $arParams["PATH_TO_BLOG"]
	));

	if (!$arBlog)
	{
		$arResult["ERROR_MESSAGE"] .= GetMessage("B_B_MES_NO_BLOG");
	}
}

$arResult["Blog"] = $arBlog;

$arResult["urlToBlog"] = CComponentEngine::MakePathFromTemplate(
	($arResult["bGroupMode"] ? $arParams["PATH_TO_GROUP_BLOG"] : $arParams["PATH_TO_BLOG"]),
	[
		"blog" => $arBlog["URL"],
		"user_id" => $arBlog["OWNER_ID"],
		"group_id" => $arParams["SOCNET_GROUP_ID"]
	]
);

$arPostFields = $USER_FIELD_MANAGER->GetUserFields("BLOG_POST", $arParams["ID"], LANGUAGE_ID);
$arResult["POST_PROPERTIES"] = array("SHOW" => "N", "DATA" => array());

$arParams["CACHE_TIME"] = defined("BX_COMP_MANAGED_CACHE") ? 3600*24*365 : 3600*24;
$arResult["PostToShow"]["GRATS"] = array();
$arResult["PostToShow"]["GRATS_DEF"] = false;

$cache = new CPHPCache;
$cache_id = "blog_post_grats_".SITE_ID;
$cache_path = "/blog/form/post/new";

if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$Vars = $cache->GetVars();
	$arResult["PostToShow"]["GRATS"] = $Vars["GRATS"];
	$arResult["PostToShow"]["GRATS_DEF"] = $Vars["GRATS_DEF"];
	$honour_iblock_id = $Vars["GRATS_IBLOCK_ID"];
}
else
{
	$honour_iblock_id = 0;
	$cache->StartDataCache();
	if (
		(
			!empty($arParams["POST_PROPERTY"])
			|| ModuleManager::isModuleInstalled("intranet")
		)
		&& !$arResult["bExtranetSite"]
		&& Loader::includeModule("iblock")
	)
	{
		$rsIBlock = CIBlock::GetList(array(), array("CODE" => "honour", "TYPE" => "structure"));
		if ($arIBlock = $rsIBlock->Fetch())
		{
			$honour_iblock_id = $arIBlock["ID"];

			if (defined('BX_COMP_MANAGED_CACHE'))
			{
				$CACHE_MANAGER->StartTagCache($cache_path);
			}

			$rsIBlockPropertyEnum = CIBlockPropertyEnum::GetList(
				array(
					"SORT" => "ASC",
					"XML_ID" => "ASC"
				),
				array(
					"CODE" => "GRATITUDE",
					"IBLOCK_ID" => $arIBlock["ID"]
				)
			);
			while ($arIBlockPropertyEnum = $rsIBlockPropertyEnum->Fetch())
			{
				$arResult["PostToShow"]["GRATS"][] = $arIBlockPropertyEnum;
				if ($arIBlockPropertyEnum['DEF'] === 'Y')
				{
					$arResult['PostToShow']['GRATS_DEF'] = $arIBlockPropertyEnum;
				}
			}

			if (defined('BX_COMP_MANAGED_CACHE'))
			{
				$CACHE_MANAGER->EndTagCache();
			}
		}
	}
	$cache->EndDataCache(
		array(
			"GRATS" => $arResult["PostToShow"]["GRATS"],
			"GRATS_DEF" => $arResult["PostToShow"]["GRATS_DEF"],
			"GRATS_IBLOCK_ID" => $honour_iblock_id
		)
	);
}

$arPost = [];

if (
	$arParams["ID"] > 0
	&& $arPost = CBlogPost::GetByID($arParams["ID"])
)
{
	$arPost = CBlogTools::htmlspecialcharsExArray($arPost);

	$arPost['DETAIL_TEXT'] = preg_replace("/\[tag\](.+?)\[\/tag\]/is".BX_UTF_PCRE_MODIFIER, "\\1", $arPost['DETAIL_TEXT']);
	$arPost['~DETAIL_TEXT'] = preg_replace("/\[tag\](.+?)\[\/tag\]/is".BX_UTF_PCRE_MODIFIER, "\\1", $arPost['~DETAIL_TEXT']);

	$arResult["Post"] = $arPost;
	if ($arParams['SET_TITLE'] === 'Y')
	{
		$APPLICATION->SetTitle(Loc::getMessage('BLOG_POST_EDIT'));
	}

	if (
		$arParams["USER_ID"] == $user_id
		|| (
			($_POST["apply"] ?? null)
			&& CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false)
		)
		|| $blogModulePermissions >= 'W'
	)
	{
		$arResult["perms"] = BLOG_PERMS_FULL;
	}
	else
	{
		$arResult["perms"] = CBlogPost::GetSocNetPostPerms($arPost["ID"], true, false, $arPost["AUTHOR_ID"]);
	}

	// Get UF_GRATITUDE
	if (
		ModuleManager::isModuleInstalled("intranet")
		&& Loader::includeModule("iblock")
		&& isset($arPostFields["UF_GRATITUDE"])
		&& is_array($arPostFields["UF_GRATITUDE"])
		&& (int)$arPostFields["UF_GRATITUDE"]["VALUE"] > 0
	)
	{
		if ($honour_iblock_id > 0)
		{
			$arGrat = array(
				"ID" => false,
				"USERS" => array(),
				"USERS_FOR_JS" => array(),
				"TYPE" => false
			);
			$rsElementProperty = CIBlockElement::GetProperty(
				$honour_iblock_id,
				$arPostFields["UF_GRATITUDE"]["VALUE"]
			);
			while ($arElementProperty = $rsElementProperty->fetch())
			{
				if (!$arGrat['ID'])
				{
					$arGrat['ID'] = (int)$arPostFields['UF_GRATITUDE']['VALUE'];
				}

				if (
					$arElementProperty['CODE'] === 'USERS'
					&& (int)$arElementProperty['VALUE'] > 0
				)
				{
					$arGrat['USERS'][] = (int)$arElementProperty['VALUE'];
				}
				elseif ($arElementProperty['CODE'] === 'GRATITUDE')
				{
					$arGrat['TYPE'] = [
						'VALUE_ENUM' => $arElementProperty['VALUE_ENUM'],
						'XML_ID' => $arElementProperty['VALUE_XML_ID'],
					];
				}
			}

			if (
				$arGrat['ID']
				&& !empty($arGrat['USERS'])
			)
			{
				$dbUsers = CUser::GetList(
					[
						'last_name' => 'asc',
						'IS_ONLINE' => 'desc'
					],
					'',
					[
						'ID' => implode('|', $arGrat['USERS']),
					],
					[
						"FIELDS" => [ "ID", "LAST_NAME", "NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION" ]
					]
				);

				while($arGratUser = $dbUsers->Fetch())
				{
					$sName = trim(CUser::FormatName(empty($arParams["NAME_TEMPLATE"]) ? CSite::GetNameFormat(false) : $arParams["NAME_TEMPLATE"], $arGratUser));
					$arGrat["USERS_FOR_JS"]["U".$arGratUser["ID"]] = array(
						"id" => "U".$arGratUser["ID"],
						"entityId" => $arGratUser["ID"],
						"name" => $sName,
						"avatar" => "",
						"desc" => $arGratUser["WORK_POSITION"] ? $arGratUser["WORK_POSITION"] : ($arGratUser["PERSONAL_PROFESSION"] ? $arGratUser["PERSONAL_PROFESSION"] : "&nbsp;")
					);
				}

				$arResult["PostToShow"]["GRAT_CURRENT"] = $arGrat;
			}
		}
	}
}
else
{
	$arParams["ID"] = 0;
	if ($arParams['SET_TITLE'] === 'Y')
	{
		$APPLICATION->SetTitle(GetMessage("BLOG_NEW_MESSAGE"));
	}
}

if (
	isset($_GET['delete_blog_post_id'])
	&& (int) $_GET['delete_blog_post_id'] > 0
	&& $_GET['ajax_blog_post_delete'] === 'Y')
{
	if (check_bitrix_sessid())
	{
		try
		{
			$result = \Bitrix\Socialnetwork\Item\Helper::deleteBlogPost([
				'POST_ID' => (int)$_GET['delete_blog_post_id'],
			]);
		}
		catch (Exception $e)
		{
			$arResult['ERROR_MESSAGE'] .= $e->getMessage();
		}
	}
	else
	{
		$arResult["ERROR_MESSAGE"] .= GetMessage("BLOG_BLOG_SESSID_WRONG");
	}

	$arResult["delete_blog_post"] = "Y";
	$this->IncludeComponentTemplate();

	return true;
}

$isPostBeingEdited = ($arParams["ID"] > 0);
if ($isPostBeingEdited)
{
	$periodsOfShowingImportantPost = ["ALWAYS", "CUSTOM"];
}
else
{
	$periodsOfShowingImportantPost = ["ALWAYS", "ONE_DAY", "TWO_DAYS", "WEEK", "MONTH", "CUSTOM"];
}
$arResult["REMAIN_IMPORTANT_TILL"] = [];
foreach ($periodsOfShowingImportantPost as $period)
{
	$attributesForPopupList = [
		"VALUE" => $period,
		"TEXT_KEY"  => ("IMPORTANT_FOR_$period"),
	];
	$arResult["REMAIN_IMPORTANT_TILL"][] = $attributesForPopupList;
}
if (
	(
		$arParams["ID"] === 0
		&& $arResult["perms"] >= BLOG_PERMS_PREMODERATE
	)
	|| (
		$arParams["ID"] > 0
		&& $arResult["perms"] >= BLOG_PERMS_FULL
		&& (int)$arPost['BLOG_ID'] === (int)$arBlog['ID']
	)
)
{
	$arP = [];
	if (
		$arParams["ID"] > 0
		&& $arPost['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_READY
		&& (int)$arPost['AUTHOR_ID'] === (int)$user_id
	)
	{
		$arResult['OK_MESSAGE'] = Loc::getMessage('BPE_HIDDEN_POSTED');
	}

	$bAllowToAll = ComponentHelper::getAllowToAllDestination();

	$bDefaultToAll = (
		$bAllowToAll
			? (Option::get('socialnetwork', 'default_livefeed_toall', 'Y') === 'Y')
			: false
	);

	if (
		(
			isset($_POST['apply'])
			|| isset($_POST['save'])
			|| isset($_POST['do_upload'])
			|| isset($_POST['draft'])
		)
		&& (
			!isset($_POST['changePostFormTab'])
			|| (
				isset($_POST['changePostFormTab'])
				&& $_POST['changePostFormTab'] !== 'tasks'
			)
		)
	)
	{
		if (check_bitrix_sessid())
		{
			if (
				isset($_POST['decode'])
				&& $_POST['decode'] === 'Y'
			)
			{
				CUtil::JSPostUnescape();
			}
		}
		else
		{
			$arResult["ERROR_MESSAGE"] .= GetMessage("BPE_SESS");
		}
	}

	if (
		(
			isset($_GET['image_upload_frame'])
			&& $_GET['image_upload_frame'] === 'Y'
		)
		|| isset($_GET["image_upload"])
		|| isset($_POST["do_upload"])
		|| isset($_GET["del_image_id"])
	)
	{
		if (check_bitrix_sessid())
		{
			if ((int)$_GET['del_image_id'] > 0)
			{
				$del_image_id = (int)$_GET['del_image_id'];
				$aImg = CBlogImage::GetByID($del_image_id);
				if (
					$aImg["BLOG_ID"] == $arBlog["ID"]
					&& (int)$aImg['POST_ID'] === $arParams['ID']
				)
				{
					CBlogImage::Delete($del_image_id);
				}
				$APPLICATION->RestartBuffer();
				die();
			}

			$arResult['imageUploadFrame'] = 'Y';
			$arResult['imageUpload'] = 'Y';
			$APPLICATION->RestartBuffer();
			header('Pragma: no-cache');

			$arFields = [];
			if ($_FILES['BLOG_UPLOAD_FILE']['size'] > 0)
			{
				$arFields = [
					'BLOG_ID' => $arBlog['ID'],
					'POST_ID' => $arParams['ID'],
					'USER_ID' => $arResult['UserID'],
					'=TIMESTAMP_X' => $DB->GetNowFunction(),
					'TITLE' => $_POST['IMAGE_TITLE'],
					'IMAGE_SIZE' => $_FILES['BLOG_UPLOAD_FILE']['size'],
				];
				$arImage = array_merge(
					$_FILES['BLOG_UPLOAD_FILE'],
					[
						'MODULE_ID' => 'blog',
						'del' => 'Y',
					]
				);
				$arFields['FILE_ID'] = $arImage;
			}
			elseif ($_POST['do_upload'] && $_FILES['FILE_ID']['size'] > 0)
			{
				$arFields = [
					'BLOG_ID' => $arBlog['ID'],
					'POST_ID' => $arParams['ID'],
					'USER_ID' => $arResult['UserID'],
					'=TIMESTAMP_X' => $DB->GetNowFunction(),
					'TITLE' => $_POST['IMAGE_TITLE'],
					'IMAGE_SIZE' => $_FILES['FILE_ID']['size'],
					'URL' => $arBlog['URL'],
				];
				$arImage = array_merge(
					$_FILES['FILE_ID'],
					array(
						'MODULE_ID' => 'blog',
						'del' => 'Y',
					)
				);
				$arFields['FILE_ID'] = $arImage;
			}
			if (!empty($arFields))
			{
				if ($imgID = CBlogImage::Add($arFields))
				{
					$aImg = CBlogImage::GetByID($imgID);
					$aImg = CBlogTools::htmlspecialcharsExArray($aImg);

					$aImgNew = CFile::ResizeImageGet(
						$aImg["FILE_ID"],
						array("width" => 90, "height" => 90),
						BX_RESIZE_IMAGE_EXACT,
						true
					);
					$aImg["source"] = CFile::ResizeImageGet(
						$aImg["FILE_ID"],
						array("width" => $arParams["IMAGE_MAX_WIDTH"], "height" => $arParams["IMAGE_MAX_HEIGHT"]),
						BX_RESIZE_IMAGE_PROPORTIONAL,
						true
					);
					$aImg["params"] = CFile::_GetImgParams($aImg["FILE_ID"]);
					$aImg["fileName"] = mb_substr($aImgNew["src"], mb_strrpos($aImgNew["src"], "/") + 1);
					$file = "<img src=\"".$aImgNew["src"]."\" width=\"".$aImgNew["width"]."\" height=\"".$aImgNew["height"]."\" id=\"".$aImg["ID"]."\" border=\"0\" style=\"cursor:pointer\" onclick=\"InsertBlogImage_LHEPostFormId_blogPostForm('".$aImg["ID"]."', '".$aImg["source"]['src']."', '".$aImgNew["source"]['width']."');\" title=\"".GetMessage("BLOG_P_INSERT")."\">";

					$file = str_replace(
						[ "\r", "\n", "'" ],
						[ "\'", ' ', ' ' ],
						$file
					);
					$arResult["ImageModified"] = $file;
					$arResult["Image"] = $aImg;
				}
				elseif ($ex = $APPLICATION->GetException())
				{
					$arResult["ERROR_MESSAGE"] .= $ex->GetString();
				}
			}
		}
	}
	else
	{
		$this->convertRequestData();

		// Save calendar event from Socialnetwork live feed form
		if (
			(
				isset($_POST["save"])
				&& $_POST["save"] === "Y"
			)
			&& (
				isset($_POST["changePostFormTab"])
				&& $_POST["changePostFormTab"] === "calendar"
			)
			&& check_bitrix_sessid()
		)
		{
			if (isset($_POST['EVENT_PERM']))
			{
				$arAccessCodes = array();
				foreach($_POST["EVENT_PERM"] as $v => $k)
				{
					if ($v <> '' && is_array($k) && !empty($k))
					{
						foreach($k as $vv)
						{
							if ($vv <> '')
							{
								$arAccessCodes[] = $vv;
							}
						}
					}
				}
			}

			$rrule = $_POST['EVENT_RRULE'];
			if (isset($_POST['rrule_endson']) && $_POST['rrule_endson'] === 'never')
			{
				unset($rrule['COUNT']);
				unset($rrule['UNTIL']);
			}
			elseif (isset($_POST['rrule_endson']) && $_POST['rrule_endson'] === 'count')
			{
				unset($rrule['UNTIL']);
			}
			elseif (isset($_POST['rrule_endson']) && $_POST['rrule_endson'] === 'until')
			{
				unset($rrule['COUNT']);
			}

			$arFields = [
				'ID' => (int)$_POST['EVENT_ID'],
				"DT_FROM_TS" => $_POST['EVENT_FROM_TS'], // For calendar < 16.x.x
				"DT_TO_TS" => $_POST['EVENT_TO_TS'], // For calendar < 16.x.x
				"DATE_FROM" => $_POST['DATE_FROM'],
				"DATE_TO" => $_POST['DATE_TO'],
				"TIME_FROM" => $_POST['TIME_FROM'],
				"TIME_TO" => $_POST['TIME_TO'],
				"TZ_FROM" => $_POST['TZ_FROM'],
				"TZ_TO" => $_POST['TZ_TO'],
				"DEFAULT_TZ" => $_POST['DEFAULT_TZ'],
				'SKIP_TIME' => ($_POST['EVENT_FULL_DAY'] === 'Y'),
				'NAME' => trim($_POST['EVENT_NAME']),
				'DESCRIPTION' => trim($_POST['EVENT_DESCRIPTION']),
				'SECTION' => (int)$_POST['EVENT_SECTION'],
				'ACCESSIBILITY' => $_POST['EVENT_ACCESSIBILITY'],
				'IMPORTANCE' => $_POST['EVENT_IMPORTANCE'],
				'RRULE' => $rrule,
				'LOCATION' => $_POST['EVENT_LOCATION'],
				"REMIND" => isset($_POST['EVENT_REMIND']) ? array(0 => array('count' => $_POST['EVENT_REMIND_COUNT'], 'type' => $_POST['EVENT_REMIND_TYPE'])) : null
			];

			// Userfields for event
			$arUFFields = array();
			foreach ($_POST as $field => $value)
			{
				if (mb_substr($field, 0, 3) === 'UF_')
				{
					$arUFFields[$field] = $value;
				}
			}

			CCalendarLiveFeed::EditCalendarEventEntry($arFields, $arUFFields, $arAccessCodes, array(
				'type' => 'user',
				'userId' => $arBlog["OWNER_ID"]
			));

			$redirectUrl = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("user_id" => $arBlog["OWNER_ID"]));

			$uri = new Bitrix\Main\Web\Uri($redirectUrl);
			$uri->deleteParams(array("b24statAction", "b24statTab", "b24statAddEmailUserCrmContact"));
			$redirectUrl = $uri->getUri();

			LocalRedirect($redirectUrl);
		}

		if (
			(
				isset($_POST["save"])
				&& $_POST["save"] === "Y"
			)
			&& (
				isset($_POST["changePostFormTab"])
				&& $_POST['changePostFormTab'] === 'lists'
			)
			&& check_bitrix_sessid()
		)
		{
			$redirectUrl = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("user_id" => $arBlog["OWNER_ID"]));

			$uri = new Bitrix\Main\Web\Uri($redirectUrl);
			$uri->deleteParams(array("b24statAction", "b24statTab", "b24statAddEmailUserCrmContact"));
			$redirectUrl = $uri->getUri();

			LocalRedirect($redirectUrl);
		}

		if (
			(
				isset($_POST["apply"])
				|| isset($_POST["save"])
				|| isset($_POST["draft"])
			)
			&& empty($_POST["reset"])
			&& (
				!isset($_POST['changePostFormTab'])
				|| (
					isset($_POST['changePostFormTab'])
					&& $_POST['changePostFormTab'] !== 'tasks'
				)
			)
		) // Save on button click
		{
			if (check_bitrix_sessid())
			{
				if ((string)$arResult['ERROR_MESSAGE'] === '')
				{
					$DB->StartTransaction();

					$categoryIdListFromPostData = BlogPostEdit\Tag::getTagsFromPostData([
						'blogId' => $arBlog['ID'],
					]);

					$DATE_PUBLISH = "";
					if (($_POST["DATE_PUBLISH_DEF"] ?? '') <> '')
					{
						$DATE_PUBLISH = $_POST["DATE_PUBLISH_DEF"];
					}
					elseif (($_POST["DATE_PUBLISH"] ?? '') == '')
					{
						$DATE_PUBLISH = ConvertTimeStamp(time()+CTimeZone::GetOffset(), "FULL");
					}
					else
					{
						$DATE_PUBLISH = $_POST["DATE_PUBLISH"];
					}

					$PUBLISH_STATUS = (
						($_POST["draft"] ?? '') <> ''
							? BLOG_PUBLISH_STATUS_DRAFT
							: BLOG_PUBLISH_STATUS_PUBLISH
					);

					$arFields = array(
						"TITLE" => trim($_POST["POST_TITLE"]),
						'DETAIL_TEXT' => (
							isset($_POST['MOBILE'])
							&& $_POST['MOBILE'] === 'Y'
								? htmlspecialcharsEx($_POST['POST_MESSAGE'])
								: $_POST['POST_MESSAGE']
						),
						"DETAIL_TEXT_TYPE" => "text",
						"DATE_PUBLISH" => $DATE_PUBLISH,
						"PUBLISH_STATUS" => $PUBLISH_STATUS,
						"PATH" => CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("post_id" => "#post_id#", "user_id" => $arBlog["OWNER_ID"])),
						"URL" => $arBlog["URL"],
						"BACKGROUND_CODE" => false
					);

					if (\Bitrix\Main\Config\Configuration::getValue("utf_mode") === true)
					{
						$conn = \Bitrix\Main\Application::getConnection();
						$table = \Bitrix\Blog\PostTable::getTableName();

						if (
							$arFields['TITLE'] <> ''
							&& !$conn->isUtf8mb4($table, 'TITLE')
						)
						{
							$arFields['TITLE'] = \Bitrix\Main\Text\Emoji::encode($arFields['TITLE']);
						}

						if (
							$arFields['DETAIL_TEXT'] <> ''
							&& !$conn->isUtf8mb4($table, 'DETAIL_TEXT')
						)
						{
							$arFields['DETAIL_TEXT'] = \Bitrix\Main\Text\Emoji::encode($arFields['DETAIL_TEXT']);
						}
					}

					if (
						$arParams['ALLOW_POST_CODE']
						&& trim($_POST['CODE'] ?? '') <> ''
					)
					{
						$arFields["CODE"] = trim($_POST["CODE"]);
						$arPCFilter = array("BLOG_ID" => $arBlog["ID"], "CODE" => $arFields["CODE"]);
						if ($arParams['ID'] > 0)
						{
							$arPCFilter["!ID"] = $arParams['ID'];
						}

						$db = CBlogPost::GetList(Array(), $arPCFilter, false, Array("nTopCount" => 1), Array("ID", "CODE", "BLOG_ID"));
						if ($db->Fetch())
						{
							$uind = 0;
							do
							{
								$uind++;
								$arFields['CODE'] .= $uind;
								$arPCFilter["CODE"]  = $arFields["CODE"];
								$db = CBlogPost::GetList(Array(), $arPCFilter, false, Array("nTopCount" => 1), Array("ID", "CODE", "BLOG_ID"));
							}
							while ($db->Fetch());
						}
					}

					$arFields["PERMS_POST"] = array();
					$arFields["PERMS_COMMENT"] = array();

					$arFields["MICRO"] = "N";
					$checkTitle = false;

					if (
						isset($_POST['MOBILE'], $arPost['TITLE'])
						&& $_POST['ACTION'] === 'EDIT_POST'
						&& $_POST['MOBILE'] === 'Y'
					)
					{
						$arFields["TITLE"] = $arPost["~TITLE"];
						$arFields["MICRO"] = $arPost["MICRO"];
					}
					elseif (
						(string)$arFields["TITLE"] === ''
						|| $_POST['show_title'] === 'N'
					)
					{
						$arFields["MICRO"] = "Y";
						$arFields["TITLE"] = preg_replace(array("/\n+/is".BX_UTF_PCRE_MODIFIER, "/\s+/is".BX_UTF_PCRE_MODIFIER), " ", blogTextParser::killAllTags($arFields["DETAIL_TEXT"]));

						$parser = new CTextParser();
						$parser->allow = array('CLEAR_SMILES' => 'Y');

						$arFields["TITLE"] = preg_replace("/&nbsp;/is".BX_UTF_PCRE_MODIFIER, "", $parser->convertText($arFields["TITLE"]));
						$arFields["TITLE"] = trim($arFields["TITLE"], " \t\n\r\0\x0B\xA0");

						$checkTitle = true;
					}

					$newCategoryIdList = BlogPostEdit\Tag::parseTagsFromFields([
						'blogCategoryIdList' => $categoryIdListFromPostData,
						'postFields' => $arFields,
						'blogId' => $arBlog['ID'],
					]);

					$categoryIdList = array_merge($categoryIdListFromPostData, $newCategoryIdList);
					$CATEGORY_ID = implode(",", $categoryIdList);

					$arFields["CATEGORY_ID"] = $CATEGORY_ID;
					$arFields["SOCNET_RIGHTS"] = array();

					$bError = false;

					if (!empty($_POST["SPERM"]))
					{
						ComponentHelper::processBlogPostNewMailUser($_POST, $arResult);

						$resultFields = array(
							'ERROR_MESSAGE' => false,
							'PUBLISH_STATUS' => $arFields['PUBLISH_STATUS']
						);

						$destParams = array(
							'POST_ID' => $arParams["ID"],
							'PERM' => $_POST["SPERM"],
							'IS_REST' => false,
							'IS_EXTRANET_USER' => $arResult["bExtranetUser"]
						);
						if ($arParams["ID"] <= 0)
						{
							$destParams['AUTHOR_ID'] = $user_id;
						}

						$arFields["SOCNET_RIGHTS"] = ComponentHelper::convertBlogPostPermToDestinationList($destParams, $resultFields);

						$arFields["PUBLISH_STATUS"] = $resultFields['PUBLISH_STATUS'];
						if (!empty($resultFields['ERROR_MESSAGE']))
						{
							$arResult["ERROR_MESSAGE"] = $resultFields['ERROR_MESSAGE'];
							$bError = true;
						}
					}

					if (
						!$bError
						&& empty($arFields["SOCNET_RIGHTS"])
					)
					{
						$bError = true;
						$arResult["ERROR_MESSAGE"] .= GetMessage("BLOG_BPE_DESTINATION_EMPTY");
					}

					$mentionList = [];
					$mentionListOld = [];

					if (!$bError)
					{
						$fieldName = 'UF_BLOG_POST_DOC';
						if (
							isset($GLOBALS[$fieldName])
							&& is_array($GLOBALS[$fieldName])
						)
						{
							$arOldFiles = array();
							if ($arParams["ID"] > 0 && $_POST["blog_upload_cid"] == '')
							{
								$dbP = CBlogPost::GetList(array(), array("ID" => $arParams["ID"]), false, false, array("ID", $fieldName));
								if ($arP = $dbP->Fetch())
								{
									$arOldFiles = $arP[$fieldName];
								}
							}
							$arAttachedFiles = array();
							foreach ($GLOBALS[$fieldName] as $fileID)
							{
								$fileID = (int)$fileID;

								if ($fileID <= 0)
								{
									continue;
								}

								if (
									(
										!is_array($_SESSION["MFI_UPLOADED_FILES_".$_POST["blog_upload_cid"]])
										|| !in_array($fileID, $_SESSION["MFI_UPLOADED_FILES_".$_POST["blog_upload_cid"]])
									)
									&& ( // mobile
										!is_array($_SESSION["MFU_UPLOADED_FILES_".$USER->GetId()])
										|| !in_array($fileID, $_SESSION["MFU_UPLOADED_FILES_".$USER->GetId()])
									)
								)
								{
									if (
										empty($arOldFiles)
										|| !in_array($fileID, $arOldFiles)
									)
									{
										continue;
									}
								}

								$arFile = CFile::GetFileArray($fileID);
								if (CFile::CheckImageFile(CFile::MakeFileArray($fileID)) === null)
								{
									$arImgFields = array(
										"BLOG_ID" => $arBlog["ID"],
										"POST_ID" => 0,
										"USER_ID" => $arResult["UserID"],
										"=TIMESTAMP_X" => $DB->GetNowFunction(),
										"TITLE" => $arFile["FILE_NAME"],
										"IMAGE_SIZE" => $arFile["FILE_SIZE"],
										"FILE_ID" => $fileID,
										"URL" => $arBlog["URL"],
										"IMAGE_SIZE_CHECK" => "N",
									);
									$imgID = CBlogImage::Add($arImgFields);
									if ((int)$imgID <= 0)
									{
										$APPLICATION->ThrowException("Error Adding file by CBlogImage::Add");
									}
									else
									{
										$arFields["DETAIL_TEXT"] = str_replace("[IMG ID=".$fileID."file", "[IMG ID=".$imgID."", $arFields["DETAIL_TEXT"]);
									}
								}
								else
								{
									$arAttachedFiles[] = $fileID;
								}
							}
							if (
								is_array($arPostFields)
								&& is_array($arPostFields[$fieldName])
								&& is_array($arPostFields[$fieldName]["VALUE"])
							)
							{
								$arAttachedFiles = array_unique(array_merge($arAttachedFiles, array_intersect($GLOBALS[$fieldName], $arPostFields[$fieldName]["VALUE"])));
							}
							$GLOBALS[$fieldName] = $arAttachedFiles;
						}

						CSocNetLogComponent::checkEmptyUFValue('UF_BLOG_POST_FILE');

						if (!empty($arParams["POST_PROPERTY"]))
						{
							$USER_FIELD_MANAGER->EditFormAddFields("BLOG_POST", $arFields);
						}

						$mentionList = Mention::getUserIds($_POST['POST_MESSAGE']);

						$APPLICATION->ResetException();
						$bAdd = false;

						$bNeedAddGrat = false;
						if (
							array_key_exists("GRAT", $_POST)
							&& isset($_POST["GRAT"]["U"])
							&& is_array($_POST["GRAT"]["U"])
							&& array_key_exists("GRAT_TYPE", $_POST)
							&& array_key_exists("changePostFormTab", $_POST)
							&& (
								$_POST['changePostFormTab'] === 'grat'
								|| (
									isset($arParams["PAGE_ID"])
									&& in_array($arParams["PAGE_ID"], [ "user_blog_post_edit_grat", "user_grat" ])
								)
							)
						)
						{
							$bNeedAddGrat = true;
						}

						if (
							!empty($_POST["attachedFilesRaw"])
							&& is_array($_POST["attachedFilesRaw"])
						)
						{
							CSocNetLogComponent::saveRawFilesToUF(
								$_POST["attachedFilesRaw"],
								(
									ModuleManager::isModuleInstalled("webdav")
									|| ModuleManager::isModuleInstalled("disk")
										? "UF_BLOG_POST_FILE"
										: "UF_BLOG_POST_DOC"
								),
								$arFields
							);
						}

						$voteCode = (string)$request->getPost('UF_BLOG_POST_VOTE');

						if (
							$checkTitle
							&& (string)$arFields['TITLE'] === ''
						)
						{
							if (
								$voteCode !== ''
								&& !empty($request->getPost('UF_BLOG_POST_VOTE_' . $voteCode . '_DATA'))
							)
							{
								$arFields['TITLE'] = Loc::getMessage('BLOG_EMPTY_TITLE_VOTE_PLACEHOLDER');
							}
							elseif (
								!empty($arFields["UF_BLOG_POST_FILE"])
								&& is_array($arFields["UF_BLOG_POST_FILE"])
							)
							{
								foreach ($arFields['UF_BLOG_POST_FILE'] as $val)
								{
									if (empty($val))
									{
										continue;
									}

									$arFields['TITLE'] = Loc::getMessage('BLOG_EMPTY_TITLE_PLACEHOLDER2');
									break;
								}
							}
						}

						if (
							$checkTitle
							&& $arFields["TITLE"] == ''
							&& isset($_POST["MOBILE"])
							&& $_POST['MOBILE'] === 'Y'
						)
						{
							$arFields["TITLE"] = GetMessage("BLOG_EMPTY_TITLE_PLACEHOLDER3");
						}

						$arFields['SEARCH_GROUP_ID'] = Option::get('socialnetwork', 'userbloggroup_id', false, SITE_ID);
						if (isset($_POST["postShowingDuration"]) && in_array($_POST["postShowingDuration"], $periodsOfShowingImportantPost))
						{
							if ($_POST["postShowingDuration"] !== "CUSTOM")
							{
								$userDateTimeNow = \Bitrix\Main\Type\DateTime::createFromTimestamp(time() + CTimeZone::GetOffset());
								if (isset($_POST['postShowingDuration']) && $_POST['postShowingDuration'] === 'ALWAYS')
								{
									$arFields["UF_IMPRTANT_DATE_END"] = null;
								}
								else
								{
									switch ($_POST["postShowingDuration"])
									{
										case "ONE_DAY":
											$showEndTime = $userDateTimeNow->setTime(23, 59, 59);
											break;
										case "TWO_DAYS":
											$showEndTime = $userDateTimeNow->setTime(23, 59, 59)->add("1D");
											break;
										case "WEEK":
											$showEndTime = $userDateTimeNow->setTime(23, 59, 59)->add("7D");
											break;
										case "MONTH":
											$showEndTime = $userDateTimeNow->setTime(23, 59, 59)->add("1M");
											break;
										default:
											break;
									}
									$arFields["UF_IMPRTANT_DATE_END"] = \Bitrix\Main\Type\DateTime::createFromTimestamp($showEndTime->getTimestamp() - CTimeZone::GetOffset());
								}
							}
							else
							{
								$postEndingServerTime = \Bitrix\Main\Type\DateTime::createFromUserTime($arFields["UF_IMPRTANT_DATE_END"]);
								$postEndingServerTime->add("-T1S");
								$arFields["UF_IMPRTANT_DATE_END"] = $postEndingServerTime;
							}
						}

						$newGratData = [];
						$arUsersFromPOST = [];
						if (
							!empty($_POST["GRAT_TYPE"])
							&& !empty($_POST["GRAT"])
							&& !empty($_POST["GRAT"]["U"])
							&& is_array($_POST["GRAT"]["U"])
						)
						{
							foreach ($_POST["GRAT"]["U"] as $code)
							{
								if (preg_match('/^U(\d+)$/', $code, $matches))
								{
									$arUsersFromPOST[] = $matches[1];
								}
							}

							$newGratData = [
								'TYPE' => $_POST["GRAT_TYPE"],
								'USERS' => array_diff($arUsersFromPOST, (
									!empty($arResult["PostToShow"]["GRAT_CURRENT"])
									&& !empty($arResult["PostToShow"]["GRAT_CURRENT"]["USERS"])
									&& is_array($arResult["PostToShow"]["GRAT_CURRENT"]["USERS"])
										? $arResult["PostToShow"]["GRAT_CURRENT"]["USERS"]
										: []
								))
							];
						}

						$allowEmptyDetailText = false;

						if (trim($arFields['DETAIL_TEXT']) === '')
						{
							$voteData = $request->getPost('UF_BLOG_POST_VOTE_' . $voteCode . '_DATA');
							if (
								is_array($voteData)
								&& isset($voteData['QUESTIONS'])
							)
							{
								$question = array_shift($voteData['QUESTIONS']);
								if ((string)$question['QUESTION'] !== '')
								{
									$allowEmptyDetailText = true;
								}
							}

							if (
								!$allowEmptyDetailText
								&& !empty($arFields['UF_BLOG_POST_FILE'])
								&& is_array($arFields['UF_BLOG_POST_FILE'])
							)
							{
								foreach ($arFields['UF_BLOG_POST_FILE'] as $val)
								{
									if (!empty($val))
									{
										$allowEmptyDetailText = true;
										break;
									}
								}
							}

							if ($allowEmptyDetailText)
							{
								$arFields['DETAIL_TEXT'] = '[B][/B]';
							}
						}

						$newID = 0;

						$arOldPost = [];
						if ($arParams["ID"] > 0)
						{
							if (
								is_array($arUsersFromPOST)
								&& array_key_exists("GRAT_TYPE", $_POST)
							)
							{
								$bGratFromForm = true;

								if (
									is_array($arResult["PostToShow"]["GRAT_CURRENT"] ?? null)
									&& empty(array_diff($arResult["PostToShow"]["GRAT_CURRENT"]["USERS"], $arUsersFromPOST))
									&& empty(array_diff($arUsersFromPOST, $arResult["PostToShow"]["GRAT_CURRENT"]["USERS"]))
									&& isset($arResult["PostToShow"]["GRAT_CURRENT"]["TYPE"]["XML_ID"])
									&& mb_strtolower($arResult["PostToShow"]["GRAT_CURRENT"]["TYPE"]["XML_ID"]) === mb_strtolower($_POST["GRAT_TYPE"])
								)
								{
									$bNeedAddGrat = false;
									$bGratSimilar = true;
								}
							}

							if (
								(
									!isset($arParams["MOBILE"])
									|| $arParams['MOBILE'] !== 'Y'
								)
								&& (
									$_POST['changePostFormTab'] !== 'grat'
									|| (
										$bGratFromForm
										&& !$bGratSimilar
									)
								)
								&& (
									is_array($arResult["PostToShow"]["GRAT_CURRENT"] ?? null)
									&& (int)$arResult['PostToShow']['GRAT_CURRENT']['ID'] > 0
									&& Loader::includeModule("iblock")
								)
							)
							{
								CIBlockElement::Delete($arResult["PostToShow"]["GRAT_CURRENT"]["ID"]);

								if ($_POST['changePostFormTab'] !== 'grat')
								{
									CBlogPost::Update($arParams["ID"], array(
										"DETAIL_TEXT_TYPE" => "text",
										"UF_GRATITUDE" => false
									));
								}
							}

							$arOldPost = CBlogPost::GetByID($arParams["ID"]);

							if (
								($arParams['MOBILE'] ?? null) === 'Y'
								&& in_array("UF_BLOG_POST_URL_PRV", $arParams["POST_PROPERTY"])
								&& empty($arFields["UF_BLOG_POST_URL_PRV"])
								&& (
									empty($arPostFields['UF_BLOG_POST_URL_PRV'])
									|| empty($arPostFields['UF_BLOG_POST_URL_PRV']['VALUE'])
								)
								&& !empty($arFields["DETAIL_TEXT"])
								&& ($urlPreviewValue = ComponentHelper::getUrlPreviewValue($arFields["DETAIL_TEXT"]))
							)
							{
								$arFields["UF_BLOG_POST_URL_PRV"] = $urlPreviewValue;
							}

							$mentionListOld = Mention::getUserIds($arOldPost['DETAIL_TEXT']);
							$socnetRightsOld = CBlogPost::GetSocnetPerms($arParams["ID"]);

							unset($arFields["DATE_PUBLISH"]);

							if ($newID = CBlogPost::Update($arParams["ID"], $arFields))
							{
								BXClearCache(true, ComponentHelper::getBlogPostCacheDir(array(
									'TYPE' => 'post',
									'POST_ID' => $arParams["ID"]
								)));
								BXClearCache(true, ComponentHelper::getBlogPostCacheDir(array(
									'TYPE' => 'post_general',
									'POST_ID' => $arParams["ID"]
								)));
								BXClearCache(True, ComponentHelper::getBlogPostCacheDir(array(
									'TYPE' => 'posts_popular',
									'SITE_ID' => SITE_ID
								)));

								$arFields["AUTHOR_ID"] = $arOldPost["AUTHOR_ID"];
								if ($arOldPost['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_PUBLISH)
								{
									if ($arFields['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_DRAFT)
									{
										CBlogPost::DeleteLog($newID);
									}
									elseif ($arFields['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_PUBLISH)
									{
										$arParamsUpdateLog = [
											'allowVideo' => $arResult['allowVideo'],
											'PATH_TO_SMILE' => $arParams['PATH_TO_SMILE'],
											'SITE_ID' => SITE_ID,
										];
										CBlogPost::UpdateLog($newID, $arFields, $arBlog, $arParamsUpdateLog);
									}
								}
								elseif (
									$arFields['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_PUBLISH
									&& $arOldPost['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_READY
								)
								{
									CBlogPost::notifyImPublish([
										'TYPE' => 'POST',
										'TITLE' => ($arFields['TITLE'] ?? $arOldPost['TITLE']),
										'TO_USER_ID' => $arFields['AUTHOR_ID'],
										'POST_URL' => CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams['PATH_TO_POST']), [
											'post_id' => $newID,
											'user_id' => $arBlog['OWNER_ID'],
										]),
										'POST_ID' => $newID,
									]);
								}
							}
						}
						else
						{
							$arFields["=DATE_CREATE"] = $DB->GetNowFunction();
							$arFields["AUTHOR_ID"] = $arResult["UserID"];
							$arFields["BLOG_ID"] = $arBlog["ID"];

							if (!$allowEmptyDetailText)
							{
								$dbDuplPost = CBlogPost::GetList(
									array("ID" => "DESC"),
									array("BLOG_ID" => $arBlog["ID"]),
									false,
									array("nTopCount" => 1),
									array("ID", "BLOG_ID", "AUTHOR_ID", "DETAIL_TEXT", "TITLE")
								);
								if ($arDuplPost = $dbDuplPost->Fetch())
								{
									$liveFeedEntity = \Bitrix\Socialnetwork\Livefeed\Provider::init(array(
										'ENTITY_TYPE' => 'BLOG_POST',
										'ENTITY_ID' => $arDuplPost['ID'],
									));
									$logRights = $liveFeedEntity->getLogRights();
									foreach ($logRights as $key => $groupCode)
									{
										if (
											$groupCode === 'SA'
											|| $groupCode === 'U' . $arFields["AUTHOR_ID"]
											|| preg_match('/^US(\d+)$/i', $groupCode, $matches)
											|| preg_match('/^OSG(\d+)/i', $groupCode, $matches)
											|| preg_match('/^SG(\d+)_/i', $groupCode, $matches)
										)
										{
											unset($logRights[$key]);
										}
										elseif ($groupCode === 'G2')
										{
											$logRights[$key] = 'UA';
										}
									}

									$filesList = (
										is_array($arFields["UF_BLOG_POST_FILE"])
											? array_values($arFields["UF_BLOG_POST_FILE"])
											: []
									);
									$filesList = array_values(array_filter($filesList, static function($val) {
										return !empty($val);
									}));

									$diff1 = array_diff($logRights, $arFields["SOCNET_RIGHTS"]);
									$diff2 = array_diff($arFields["SOCNET_RIGHTS"], $logRights);

									if (
										empty($filesList) // no files
										&& !$bNeedAddGrat // no gratitudes
										&& empty($_POST['UF_MAIL_MESSAGE'])
										&& (int)$arDuplPost['BLOG_ID'] === (int)$arFields['BLOG_ID']
										&& (int)$arDuplPost['AUTHOR_ID'] === (int)$arFields['AUTHOR_ID']
										&& md5($arDuplPost['DETAIL_TEXT']) === md5($arFields['DETAIL_TEXT'])
										&& md5($arDuplPost['TITLE']) === md5($arFields['TITLE'])
										&& empty($diff1)
										&& empty($diff2)
									)
									{
										$bError = true;
										$arResult["ERROR_MESSAGE"] = Loc::getMessage('B_B_PC_DUPLICATE_POST');
									}
								}
							}

							if (
								!$bError
								&& ($arParams['MOBILE'] ?? '') === 'Y'
								&& empty($arFields["UF_BLOG_POST_URL_PRV"])
								&& in_array("UF_BLOG_POST_URL_PRV", $arParams["POST_PROPERTY"], true)
								&& ($urlPreviewValue = ComponentHelper::getUrlPreviewValue($arFields["DETAIL_TEXT"]))
							)
							{
								$arFields["UF_BLOG_POST_URL_PRV"] = $urlPreviewValue;
							}

							if (!$bError)
							{
								$newID = CBlogPost::Add($arFields);
								$socnetRightsOld = Array("U" => Array());

								$bAdd = true;
								$bNeedMail = false;
							}
						}

						if ((int)$newID > 0)
						{
							if (
								$bNeedAddGrat
								&& !empty($arUsersFromPOST)
								&& is_array($arUsersFromPOST)
								&& Loader::includeModule("iblock")
							)
							{
								$arGratFromPOST = false;

								foreach ($arResult["PostToShow"]["GRATS"] as $arGrat)
								{
									if (mb_strtolower($arGrat["XML_ID"]) === mb_strtolower($_POST["GRAT_TYPE"]))
									{
										$arGratFromPOST = $arGrat;
										break;
									}
								}

								if ($arGratFromPOST)
								{
									$new_grat_element_id = \Bitrix\Socialnetwork\Helper\Gratitude::create([
										'medal' => $arGratFromPOST['XML_ID'],
										'employees' => $arUsersFromPOST
									]);

									if ($new_grat_element_id > 0)
									{
										CBlogPost::Update($newID, array(
											"DETAIL_TEXT_TYPE" => "text",
											"UF_GRATITUDE" => $new_grat_element_id
										));
									}
								}
							}

							CBlogPostCategory::DeleteByPostID($newID);
							foreach ($categoryIdList as $categoryId)
							{
								CBlogPostCategory::add([
									'BLOG_ID' => $arBlog['ID'],
									'POST_ID' => $newID,
									'CATEGORY_ID' => $categoryId,
								]);
							}

							$DB->Query("UPDATE b_blog_image SET POST_ID=" . $newID . " WHERE BLOG_ID=" . $arBlog["ID"] . " AND POST_ID=0", true);

							$bHasImg = false;
							$bHasTag = false;
							$bHasProps = false;
							$bHasOnlyAll = false;

							if (!empty($categoryIdList))
							{
								$bHasTag = true;
							}

							if (CBlogImage::GetList(
								[],
								[
									'BLOG_ID' => $arBlog['ID'],
									'POST_ID' => $newID,
									'IS_COMMENT' => 'N',
								],
								false,
								false,
								[ 'ID' ]
							)->fetch())
							{
								$bHasImg = true;
							}

							$arPostFieldsOLD = $arPostFields;

							$arPostFields = $USER_FIELD_MANAGER->GetUserFields("BLOG_POST", $newID, LANGUAGE_ID);
							if (
								($arPostFields["UF_BLOG_POST_IMPRTNT"]["VALUE"] != $arPostFieldsOLD["UF_BLOG_POST_IMPRTNT"]["VALUE"])
								|| (
									$arParams["ID"] > 0
									&& (
										$arResult["Post"]["~DETAIL_TEXT"] != $arFields["DETAIL_TEXT"]
										|| $arResult["Post"]["~TITLE"] != $arFields["TITLE"]
									)
								)
							)
							{
								if ($arPostFields["UF_BLOG_POST_IMPRTNT"]["VALUE"] != $arPostFieldsOLD["UF_BLOG_POST_IMPRTNT"]["VALUE"])
								{
									if ($arPostFields["UF_BLOG_POST_IMPRTNT"]["VALUE"])
										CBlogUserOptions::SetOption($newID, "BLOG_POST_IMPRTNT", "Y", $USER->GetID());
									else
										CBlogUserOptions::DeleteOption($newID, "BLOG_POST_IMPRTNT", $USER->GetID());
								}

								if (defined("BX_COMP_MANAGED_CACHE"))
								{
									$CACHE_MANAGER->ClearByTag('blogpost_important_all');
								}
							}
							foreach ($arPostFields as $FIELD_NAME => $arPostField)
							{
								if (!empty($arPostField["VALUE"]))
								{
									$bHasProps = true;
									break;
								}
							}

							if (
								!empty($arFields["SOCNET_RIGHTS"])
								&& count($arFields["SOCNET_RIGHTS"]) == 1
								&& in_array("UA", $arFields["SOCNET_RIGHTS"], true)
							)
							{
								$bHasOnlyAll = true;
							}

							$arFieldsHave = array(
								"HAS_IMAGES" => ($bHasImg ? "Y" : "N"),
								"HAS_TAGS" => ($bHasTag ? "Y" : "N"),
								"HAS_PROPS" => ($bHasProps ? "Y" : "N"),
								"HAS_SOCNET_ALL" => ($bHasOnlyAll ? "Y" : "N"),
							);
							CBlogPost::Update($newID, $arFieldsHave, false);
						}

						$logEntryActivated = false;
						if (
							is_array($arOldPost)
							&& ($arOldPost["PUBLISH_STATUS"] ?? '') != BLOG_PUBLISH_STATUS_READY
							&& ($arFields["PUBLISH_STATUS"] ?? '') == BLOG_PUBLISH_STATUS_PUBLISH
						)
						{
							if ($postItem = \Bitrix\Blog\Item\Post::getById($newID))
							{
								if ($logEntryActivated = $postItem->activateLogEntry())
								{
									$logId = $postItem->getLogId();
								}
							}
						}

						if (
							(
								$bAdd
								&& $newID
								&& $arFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH
							)
							|| (
								($arOldPost["PUBLISH_STATUS"] ?? null) != BLOG_PUBLISH_STATUS_PUBLISH
								&& $arFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH
							)
						)
						{
							$arFields["ID"] = $newID;
							if (!$logEntryActivated)
							{
								$arParamsNotify = Array(
									"bSoNet" => true,
									"UserID" => $arResult["UserID"],
									"allowVideo" => $arResult["allowVideo"],
									"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
									"PATH_TO_POST" => $arParams["PATH_TO_POST"],
									"SOCNET_GROUP_ID" => $arParams["SOCNET_GROUP_ID"],
									"user_id" => $arParams["USER_ID"],
									"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
									"SHOW_LOGIN" => $arParams["SHOW_LOGIN"] ?? '',
								);
								$logId = CBlogPost::Notify($arFields, $arBlog, $arParamsNotify);
							}

							\Bitrix\Blog\Util::sendBlogPing([
								'siteId' => SITE_ID,
								'pathToBlog' => $arParams['PATH_TO_BLOG'],
								'blogFields' => $arBlog,
							]);
						}
					}

					if (
						isset($newID)
						&& $newID > 0
						&& (string)$arResult['ERROR_MESSAGE'] === ''
					) // Record saved successfully
					{
						if ((int) ($logId ?? null) <= 0)
						{
							$blogPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;

							$res = \Bitrix\Socialnetwork\LogTable::getList(array(
								'filter' => array(
									'EVENT_ID' => $blogPostLivefeedProvider->getEventId(),
									'SOURCE_ID' => $newID
								),
								'select' => array('ID')
							));
							if ($logFields = $res->fetch())
							{
								$logId = $logFields['ID'];
							}
						}

						if (
							isset($logId)
							&& (int)$logId > 0
						)
						{
							$logFields = array(
								"EVENT_ID" => \Bitrix\Socialnetwork\Item\Helper::getBlogPostEventId([
									'postId' => $newID
								])
							);
							if ($post = \Bitrix\Blog\Item\Post::getById($newID))
							{
								$logFields["TAG"] = $post->getTags();
							}
							CSocNetLog::Update((int)$logId, $logFields);
						}

						$DB->Commit();
						$postUrl = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("post_id" => $newID, "user_id" => $arBlog["OWNER_ID"]));

						if ($arFields["PUBLISH_STATUS"] === BLOG_PUBLISH_STATUS_PUBLISH)
						{
							BXClearCache(true, ComponentHelper::getBlogPostCacheDir(array(
								'TYPE' => 'posts_last',
								'SITE_ID' => SITE_ID
							)));
							BXClearCache(true, ComponentHelper::getBlogPostCacheDir(array(
								'TYPE' => 'posts_last_blog',
								'SITE_ID' => SITE_ID
							)));

							ComponentHelper::notifyBlogPostCreated([
								'post' => [
									'ID' => $newID,
									'TITLE' => $arFields["TITLE"],
									'AUTHOR_ID' => $arParams["USER_ID"]
								],
								'siteId' => SITE_ID,
								'postUrl' => $postUrl,
								'socnetRights' => (isset($logId) && (int)$logId > 0 ? \Bitrix\Socialnetwork\Item\LogRight::get($logId) : $arFields["SOCNET_RIGHTS"]),
								'socnetRightsOld' => (!empty($socnetRightsOld) ? $socnetRightsOld : []),
								'mentionListOld' => $mentionListOld,
								'mentionList' => $mentionList,
								'gratData' => (!empty($newGratData) ? $newGratData : [])
							]);

							if (!empty($mentionList))
							{
								$arMentionedDestCode = array();
								foreach ($mentionList as $val)
								{
									if (!in_array($val, $mentionListOld))
									{
										$arMentionedDestCode[] = "U".$val;
									}
								}

								if (!empty($arMentionedDestCode))
								{
									\Bitrix\Main\FinderDestTable::merge(array(
										"CONTEXT" => "mention",
										"CODE" => array_unique($arMentionedDestCode)
									));
								}
							}
						}
						elseif (
							$arFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_READY
							&& (
								!isset($arOldPost)
								|| !isset($arOldPost["PUBLISH_STATUS"])
								|| $arOldPost["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_READY
							)
							&& !empty($arFields["SOCNET_RIGHTS"])
						)
						{
							CBlogPost::NotifyImReady(array(
								"TYPE" => "POST",
								"POST_ID" => $newID,
								"TITLE" => $arFields["TITLE"],
								"POST_URL" => $postUrl,
								"FROM_USER_ID" => $arParams["USER_ID"],
								"TO_SOCNET_RIGHTS" => $arFields["SOCNET_RIGHTS"]
							));
						}

						$arParams["ID"] = $newID;
						if (!empty($_POST["SPERM"]["SG"]))
						{
							foreach ($_POST["SPERM"]["SG"] as $v)
							{
								$group_id_tmp = mb_substr($v, 2);
								if ((int)$group_id_tmp > 0)
								{
									CSocNetGroup::SetLastActivity((int)$group_id_tmp);
								}
							}
						}

						if (
							in_array(
								$arParams['PAGE_ID'] ?? '',
								[
									'user_blog_post_edit_profile',
									'user_blog_post_edit_grat',
								]
							)
						)
						{
							$redirectUrl = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST_EDIT_PROFILE"], array("post_id"=>$newID, "user_id" => $arBlog["OWNER_ID"])).'?IFRAME=Y';
						}
						elseif (($arParams['PAGE_ID'] ?? '') === 'user_blog_post_edit_post')
						{
							$redirectUrl = CComponentEngine::makePathFromTemplate(
								$arParams['PATH_TO_POST_EDIT_POST'],
								array(
									'post_id' => $newID,
									'user_id' => $arBlog['OWNER_ID']
								)
							) . '?IFRAME=Y&successPostId=' . $newID;
						}
						elseif (($_POST["apply"] ?? '') == '')
						{
							if (
								$arFields["PUBLISH_STATUS"] === BLOG_PUBLISH_STATUS_DRAFT
								|| ($_POST["draft"] ?? '') <> ''
							)
							{
								$redirectUrl = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_DRAFT"], array("user_id" => $arBlog["OWNER_ID"]));
							}
							elseif ($arFields["PUBLISH_STATUS"] === BLOG_PUBLISH_STATUS_READY)
							{
								$redirectUrl = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST_EDIT"], array("post_id"=>$newID, "user_id" => $arBlog["OWNER_ID"]))."?moder=y";
							}
							else
							{
								$redirectUrl = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("user_id" => $arBlog["OWNER_ID"]));
							}

							if (
								!$bAdd
								&& (
									!isset($arParams["MOBILE"])
									|| $arParams['MOBILE'] !== 'Y'
								)
							)
							{
								$redirectUrl .= '#post'.$newID;
							}
						}
						else
						{
							$redirectUrl = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST_EDIT"], array("post_id"=>$newID, "user_id" => $arBlog["OWNER_ID"]));
						}
						$as = new CAutoSave(); // It is necessary to clear autosave buffer
						$as->Reset();

						if (Loader::includeModule('pull'))
						{
							\Bitrix\Pull\Event::send();
						}

						$uri = new Bitrix\Main\Web\Uri($redirectUrl);
						$uri->deleteParams([ 'b24statAction', 'b24statTab', 'b24statAddEmailUserCrmContact' ]);
						$redirectUrl = $uri->getUri();

						LocalRedirect($redirectUrl);
					}
					else
					{
						$DB->Rollback();

						if ((string)$arResult['ERROR_MESSAGE'] === '')
						{
							if ($ex = $APPLICATION->GetException())
							{
								if (
									$ex instanceof CAdminException
									&& ($errors = $ex->GetMessages())
									&& isset($_POST["MOBILE"])
									&& $_POST['MOBILE'] === 'Y'
									&& is_array($errors)
									&& !empty($errors)
								)
								{
									$errorTextList = [];
									foreach ($errors as $error)
									{
										$errorTextList[] = $error['text'];
									}
									$arResult["ERROR_MESSAGE"] = implode("\n", $errorTextList);
								}
								else
								{
									$arResult["ERROR_MESSAGE"] = $ex->GetString();
								}
							}
							else
							{
								$arResult["ERROR_MESSAGE"] = "Error saving data to database.<br />";
							}
						}
					}
				}
			}
			else
			{
				$arResult["ERROR_MESSAGE"] = GetMessage("BPE_SESS");
			}
		}
		elseif (isset($_POST["reset"]))
		{
			if ($arFields["PUBLISH_STATUS"] === BLOG_PUBLISH_STATUS_DRAFT)
			{
				LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_DRAFT"], [
					"user_id" => $arBlog["OWNER_ID"],
				]));
			}
			elseif ($arResult["bGroupMode"])
			{
				LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_BLOG"], [
					"group_id" => $arParams["SOCNET_GROUP_ID"],
				]));
			}
			else
			{
				LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], [
					"user_id" => $arBlog["OWNER_ID"],
				]));
			}
		}

		if (
			$arParams["ID"] > 0
			&& $arResult["ERROR_MESSAGE"] == ''
		) // Edit post
		{
			$arResult["PostToShow"]["TITLE"] = ($arPost['MICRO'] !== 'Y' ? $arPost['TITLE'] : '');
			$arResult["PostToShow"]["DETAIL_TEXT"] = $arPost["DETAIL_TEXT"];
			$arResult["PostToShow"]["~DETAIL_TEXT"] = $arPost["~DETAIL_TEXT"];
			$arResult["PostToShow"]["DETAIL_TEXT_TYPE"] = $arPost["DETAIL_TEXT_TYPE"];
			$arResult["PostToShow"]["PUBLISH_STATUS"] = $arPost["PUBLISH_STATUS"];
			$arResult["PostToShow"]["ENABLE_TRACKBACK"] = ($arPost['ENABLE_TRACKBACK'] === 'Y');
			$arResult["PostToShow"]["ENABLE_COMMENTS"] = $arPost["ENABLE_COMMENTS"];
			$arResult["PostToShow"]["ATTACH_IMG"] = $arPost["ATTACH_IMG"];
			$arResult["PostToShow"]["DATE_PUBLISH"] = $arPost["DATE_PUBLISH"];
			$arResult["PostToShow"]["CATEGORY_ID"] = $arPost["CATEGORY_ID"];
			$arResult["PostToShow"]["FAVORITE_SORT"] = $arPost["FAVORITE_SORT"];
			$arResult["PostToShow"]["MICRO"] = $arPost["MICRO"];
			if ($arParams["ALLOW_POST_CODE"])
			{
				$arResult["PostToShow"]["CODE"] = $arPost["CODE"];
			}

			$arResult["PostToShow"]["SPERM"] = CBlogPost::GetSocnetPerms($arPost["ID"]);
			if (
				is_array($arResult["PostToShow"]["SPERM"]["U"][$arPost["AUTHOR_ID"]] ?? null)
				&& in_array("US".$arPost["AUTHOR_ID"], $arResult["PostToShow"]["SPERM"]["U"][$arPost["AUTHOR_ID"]])
			)
			{
				$arResult["PostToShow"]["SPERM"]["U"]["A"] = Array();
			}

			if (
				!is_array($arResult["PostToShow"]["SPERM"]["U"][$arPost["AUTHOR_ID"]] ?? null)
				|| !in_array("U".$arPost["AUTHOR_ID"], $arResult["PostToShow"]["SPERM"]["U"][$arPost["AUTHOR_ID"]])
			)
			{
				unset($arResult["PostToShow"]["SPERM"]["U"][$arPost["AUTHOR_ID"]]);
			}
		}
		else
		{
			$arResult["PostToShow"]["TITLE"] = htmlspecialcharsEx($_POST["POST_TITLE"] ?? '');
			$arResult["PostToShow"]["CATEGORY_ID"] = $_POST["CATEGORY_ID"] ?? '';
			$arResult["PostToShow"]["CategoryText"] = htmlspecialcharsEx($_POST["TAGS"] ?? '');
			$arResult["PostToShow"]["DETAIL_TEXT"] = $_POST["POST_MESSAGE"] ?? '';
			$arResult["PostToShow"]["~DETAIL_TEXT"] = $_POST["POST_MESSAGE"] ?? '';
			$arResult["PostToShow"]["PUBLISH_STATUS"] = htmlspecialcharsEx($_POST["PUBLISH_STATUS"] ?? '');
			$arResult["PostToShow"]["ENABLE_COMMENTS"] = htmlspecialcharsEx($_POST["ENABLE_COMMENTS"] ?? '');
			$arResult["PostToShow"]["DATE_PUBLISH"] = isset($_POST["DATE_PUBLISH"])
				? htmlspecialcharsEx($_POST["DATE_PUBLISH"])
				: ConvertTimeStamp(time() + CTimeZone::GetOffset(), "FULL")
			;

			if ($arParams['ALLOW_POST_CODE'])
			{
				$arResult['PostToShow']['CODE'] = htmlspecialcharsEx($_POST['CODE'] ?? '');
			}

			$arResult["PostToShow"]["SPERM"] = CBlogTools::htmlspecialcharsExArray($_POST["SPERM"] ?? []);
			if (empty($arResult['PostToShow']['SPERM']))
			{
				$arResult['PostToShow']['SPERM'] = [];
			}

			if (empty($_POST['SPERM']))
			{

				if ($arParams['SOCNET_GROUP_ID'] > 0)
				{
					$arResult['PostToShow']['SPERM']['SG'][$arParams['SOCNET_GROUP_ID']] = '';
				}

				if ((int) ($arParams['SOCNET_USER_ID'] ?? 0) > 0)
				{
					$arResult['PostToShow']['SPERM']['U'][(int)$arParams['SOCNET_USER_ID']] = '';
				}
			}
			else
			{
				foreach ($_POST['SPERM'] as $k => $v)
				{
					foreach ($v as $vv1)
					{
						if ($vv1 <> '')
						{
							if ($vv1 === 'UA')
							{
								$arResult["PostToShow"]["SPERM"]["U"][] = "A";
							}
							else
							{
								$arResult["PostToShow"]["SPERM"][$k][str_replace($k, "", $vv1)] = "";
							}
						}
					}
				}
			}

			if (
				(
					array_key_exists("GRAT", $_POST)
					&& isset($_POST["GRAT"]["U"])
				)
				|| isset($_POST["GRAT_TYPE"])
				|| isset($_GET["gratCode"])
			)
			{
				if (
					array_key_exists("GRAT", $_POST)
					&& isset($_POST["GRAT"]["U"])
					&& is_array($_POST["GRAT"]["U"])
					&& count($_POST["GRAT"]["U"]) > 0
				)
				{
					$arUsersFromPOST = array();

					foreach ($_POST["GRAT"]["U"] as $code)
					{
						if (
							preg_match('/^U(\d+)$/', $code, $matches)
							&& (int)$matches[1] > 0
						)
						{
							$arUsersFromPOST[] = (int)$matches[1];
						}
					}

					if (!empty($arUsersFromPOST))
					{
						$dbUsers = CUser::GetList(
							[
								'last_name' => 'asc',
								'IS_ONLINE' => 'desc',
							],
							'',
							[
								'ID' => implode('|', $arUsersFromPOST),
							],
							[
								'FIELDS' => [ "ID", "LAST_NAME", "NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION" ]
							]

						);
						while($arGratUser = $dbUsers->Fetch())
						{
							$arResult["PostToShow"]["GRAT_CURRENT"]["USERS"][] = $arGratUser["ID"];

							$sName = trim(CUser::FormatName(empty($arParams["NAME_TEMPLATE"]) ? CSite::GetNameFormat(false) : $arParams["NAME_TEMPLATE"], $arGratUser));
							$arResult["PostToShow"]["GRAT_CURRENT"]["USERS_FOR_JS"]["U".$arGratUser["ID"]] = array(
								"id" => "U".$arGratUser["ID"],
								"entityId" => $arGratUser["ID"],
								"name" => $sName,
								"avatar" => "",
								"desc" => $arGratUser["WORK_POSITION"] ? $arGratUser["WORK_POSITION"] : ($arGratUser["PERSONAL_PROFESSION"] ? $arGratUser["PERSONAL_PROFESSION"] : "&nbsp;")
							);
						}
					}
				}

				$gratType = false;
				if (
					isset($_POST["GRAT_TYPE"])
					&& $_POST["GRAT_TYPE"] <> ''
				)
				{
					$gratType = $_POST["GRAT_TYPE"];
				}
				elseif (
					isset($_GET["gratCode"])
					&& $_GET["gratCode"] <> ''
				)
				{
					$gratType = $_GET["gratCode"];
				}

				if (
					$gratType
					&& is_array($arResult["PostToShow"]["GRATS"])
				)
					foreach ($arResult["PostToShow"]["GRATS"] as $arGrat)
					{
						if ($arGrat["XML_ID"] == $gratType)
						{
							$arResult["PostToShow"]["GRAT_CURRENT"]["TYPE"] = $arGrat;
							break;
						}
					}
			}

			if (
				isset($_REQUEST['moder'])
				&& $_REQUEST['moder'] === 'y'
			)
			{
				$arResult['OK_MESSAGE'] = Loc::getMessage('BPE_HIDDEN_POSTED');
			}
		}

		if ($arResult["SHOW_FULL_FORM"])
		{
			/* @deprecated */
			$arResult["Smiles"] = CBlogSmile::GetSmilesList();
		}

		$arResult["Images"] = Array();
		if (
			!empty($arBlog)
			&& (
				(
					isset($arPost["ID"])
					&& $arPost["ID"] > 0
				)
				|| $arResult["ERROR_MESSAGE"] <> ''
			)
		)
		{
			$arFilter = array(
					"POST_ID" => $arParams["ID"],
					"BLOG_ID" => $arBlog["ID"],
					"IS_COMMENT" => "N",
				);
			if ($arParams["ID"]==0)
				$arFilter["USER_ID"] = $arResult["UserID"];

			$res = CBlogImage::GetList(array("ID"=>"ASC"), $arFilter);
			while($aImg = $res->Fetch())
			{
				$aImgNew = CFile::ResizeImageGet(
					$aImg["FILE_ID"],
					array("width" => 90, "height" => 90),
					BX_RESIZE_IMAGE_EXACT,
					true
				);
				$aImgNew["source"] = CFile::ResizeImageGet(
					$aImg["FILE_ID"],
					array("width" => $arParams["IMAGE_MAX_WIDTH"], "height" => $arParams["IMAGE_MAX_HEIGHT"]),
					BX_RESIZE_IMAGE_PROPORTIONAL,
					true
				);
				$aImgNew["ID"] = $aImg["ID"];
				$aImgNew["params"] = CFile::_GetImgParams($aImg["FILE_ID"]);
				$aImgNew["fileName"] = mb_substr($aImgNew["src"], mb_strrpos($aImgNew["src"], "/") + 1);
				$aImgNew["fileShow"] = "<img src=\"".$aImgNew["src"]."\" width=\"".$aImgNew["width"]."\" height=\"".$aImgNew["height"]."\" border=\"0\" style=\"cursor:pointer\" onclick=\"InsertBlogImage_LHEPostFormId_blogPostForm('".$aImg["ID"]."', '".$aImgNew["source"]['src']."', '".$aImgNew["source"]['width']."');\" title=\"".GetMessage("BLOG_P_INSERT")."\">";
				$aImgNew["SRC"] = $aImgNew["source"]["src"];

				$aImgNew["FILE_NAME"] = $aImgNew["fileName"];
				$aImgNew["FILE_SIZE"] = $aImgNew["source"]["size"];
				$aImgNew["URL"] = $aImgNew["src"];
				$aImgNew["CONTENT_TYPE"] = "image/xyz";
				$aImgNew["THUMBNAIL"] = $aImgNew["src"];
				$aImgNew["DEL_URL"] = $APPLICATION->GetCurPageParam(
					"del_image_id=".$aImg["ID"]."&".bitrix_sessid_get(),
					Array("sessid", "image_upload_frame", "image_upload", "do_upload","del_image_id"));
				$arResult["Images"][] = $aImgNew;
			}
		}

		if (mb_strpos($arResult["PostToShow"]["CATEGORY_ID"] ?? '', ",") !== false)
		{
			$arResult["PostToShow"]["CATEGORY_ID"] = explode(",", trim($arResult["PostToShow"]["CATEGORY_ID"]));
		}

		$arResult["Category"] = [];

		if (
			($arResult["PostToShow"]["CategoryText"] ?? null) === ''
			&& !empty($arResult["PostToShow"]["CATEGORY_ID"])
		)
		{

			$selectedCategoriesList = [];
			$res = CBlogCategory::GetList(array("NAME" => "ASC"), array("BLOG_ID" => $arBlog["ID"]));
			while ($arCategory = $res->GetNext())
			{
				if (is_array($arResult["PostToShow"]["CATEGORY_ID"]))
				{
					if (in_array($arCategory["ID"], $arResult["PostToShow"]["CATEGORY_ID"]))
					{
						$arCategory["Selected"] = "Y";
					}
				}
				elseif ((int)$arCategory['ID'] === (int)$arResult['PostToShow']['CATEGORY_ID'])
				{
					$arCategory["Selected"] = "Y";
				}

				if ($arCategory['Selected'] === 'Y')
				{
					$selectedCategoriesList[(int)$arCategory['ID']] = $arCategory["~NAME"];
				}

				$arResult["Category"][$arCategory["ID"]] = $arCategory;
			}

			$categoryIdList = $arResult["PostToShow"]["CATEGORY_ID"];
			if (!is_array($categoryIdList))
			{
				$categoryIdList = [ $categoryIdList ];
			}

			$selectedCategoriesNameList = [];
			foreach ($categoryIdList as $categoryId)
			{
				if (!isset($selectedCategoriesList[(int)$categoryId]))
				{
					continue;
				}
				$selectedCategoriesNameList[] = $selectedCategoriesList[(int)$categoryId];
			}

			$arResult['PostToShow']['CategoryText'] = implode(',', $selectedCategoriesNameList);
		}

		foreach ($arParams["POST_PROPERTY"] as $FIELD_NAME)
		{
			$arPostField = $arPostFields[$FIELD_NAME] ?? null;
			if (!!$arPostField)
			{
				if (
					!empty($arResult["ERROR_MESSAGE"])
					&& !empty($_POST[$FIELD_NAME])
				)
				{
					$arPostField["VALUE"] = $_POST[$FIELD_NAME];
				}

				$arPostField["~EDIT_FORM_LABEL"] = ($arPostField["EDIT_FORM_LABEL"] !== "" ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"]);
				$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arPostField["~EDIT_FORM_LABEL"]);
				$arResult["POST_PROPERTIES"]["DATA"][$FIELD_NAME] = $arPostField;
				$arResult["POST_PROPERTIES"]["SHOW"] = "Y";
			}
		}

		if (
			isset($_REQUEST["WFILES"])
			&& !empty($_REQUEST["WFILES"])
			&& is_array($_REQUEST["WFILES"])
			&& !$_POST["save"]
		)
		{
			$isDiskProperty = (
				isset($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_FILE"]['USER_TYPE_ID'])
				&& $arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_FILE"]['USER_TYPE_ID'] === 'disk_file'
			);

			foreach ($_REQUEST["WFILES"] as $val)
			{
				$val = (int)$val;
				if ($val <= 0)
				{
					continue;
				}
				if ($isDiskProperty)
				{
					//@see Bitrix\Disk\Uf\FileUserType::NEW_FILE_PREFIX
					$val = 'n' . $val;
				}
				$arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_FILE"]["VALUE"][] = $val;
			}
			if (!empty($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_FILE"]["VALUE"]))
			{
				$arResult["needShow"] = true;
			}
		}

		$arResult["urlToDelImage"] = $APPLICATION->GetCurPageParam("del_image_id=#del_image_id#&".bitrix_sessid_get(), Array("sessid", "image_upload_frame", "image_upload", "do_upload","del_image_id"));

		$serverName = "";
		$dbSite = CSite::GetByID(SITE_ID);
		$arSite = $dbSite->Fetch();
		$serverName = htmlspecialcharsEx($arSite["SERVER_NAME"]);
		if ($serverName == '')
		{
			$serverName = (
				defined("SITE_SERVER_NAME")
				&& SITE_SERVER_NAME <> ''
					? SITE_SERVER_NAME
					: COption::GetOptionString("main", "server_name", "www.bitrixsoft.com")
			);

			if ($serverName == '')
			{
				$serverName = $_SERVER["HTTP_HOST"];
			}
		}
		$serverName = "http://".$serverName;

		$arResult["PATH_TO_POST"] = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id" => "#post_id#", "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));
		$arResult["PATH_TO_POST1"] = $serverName.mb_substr($arResult["PATH_TO_POST"], 0, mb_strpos($arResult["PATH_TO_POST"], "#post_id#"));
		$arResult["PATH_TO_POST2"] = mb_substr($arResult["PATH_TO_POST"], mb_strpos($arResult["PATH_TO_POST"], "#post_id#") + mb_strlen("#post_id#"));
	}

	CJSCore::Init(array('socnetlogdest'));
	// socialnetwork

	if ($arResult["SHOW_FULL_FORM"])
	{
		$arResult['PostToShow']['FEED_DESTINATION'] = [
			'LAST' => [],
		];

		if ($arResult['SELECTOR_VERSION'] < 2)
		{
			$dataAdditional = [];
			$arResult['DEST_SORT'] = CSocNetLogDestination::getDestinationSort([
				'DEST_CONTEXT' => 'BLOG_POST',
				'ALLOW_EMAIL_INVITATION' => $arResult['ALLOW_EMAIL_INVITATION']
			], $dataAdditional);

			CSocNetLogDestination::fillLastDestination(
				$arResult['DEST_SORT'],
				$arResult['PostToShow']['FEED_DESTINATION']['LAST'],
				[
					'EMAILS' => ($arResult['ALLOW_EMAIL_INVITATION'] ? 'Y' : 'N'),
					'DATA_ADDITIONAL' => $dataAdditional
				]
			);
		}

		if (
			$arResult['SELECTOR_VERSION'] < 2
			|| (
				empty($arResult['PostToShow']['SPERM'])
				&& $arResult['bExtranetUser']
			)
		)
		{
			$limitReached = false;
			$arResult['PostToShow']['FEED_DESTINATION']['SONETGROUPS'] = ComponentHelper::getSonetGroupAvailable([
				'limit' => 100,
			], $limitReached);

			if (
				$arResult['bExtranetUser']
				&& !empty($arResult['PostToShow']['FEED_DESTINATION']['LAST']['SONETGROUPS'])
				&& !$limitReached
			)
			{
				$sonetGroupAvailable = (
				!empty($arResult['PostToShow']['FEED_DESTINATION']['SONETGROUPS'])
					? $arResult['PostToShow']['FEED_DESTINATION']['SONETGROUPS']
					: []
				);

				foreach ($arResult['PostToShow']['FEED_DESTINATION']['LAST']['SONETGROUPS'] as $key => $value)
				{
					if (!in_array($value, $sonetGroupAvailable))
					{
						unset($arResult['PostToShow']['FEED_DESTINATION']['LAST']['SONETGROUPS'][$key]);
					}
				}
			}

			$arResult['PostToShow']['FEED_DESTINATION']['SONETGROUPS_LIMITED'] = ($limitReached ? 'Y' : 'N');

			if (
				!empty($arResult['PostToShow']['FEED_DESTINATION']['LAST']['SONETGROUPS'])
				&& !empty($arResult['PostToShow']['FEED_DESTINATION']['SONETGROUPS'])
			)
			{
				$arDestSonetGroup = [];
				foreach ($arResult['PostToShow']['FEED_DESTINATION']['LAST']['SONETGROUPS'] as $value)
				{
					if (!array_key_exists($value, $arResult['PostToShow']['FEED_DESTINATION']['SONETGROUPS']))
					{
						$arDestSonetGroup[] = (int)mb_substr($value, 2);
					}
				}
				if (!empty($arDestSonetGroup))
				{
					$sonetGroupsAdditionalList = CSocNetLogDestination::getSocnetGroup([
						'features' => $arResult['PostToShow']['FEED_DESTINATION']['SONETGROUPS_FEATURES'],
						'id' => $arDestSonetGroup,
					]);
					if (!empty($sonetGroupsAdditionalList))
					{
						$arResult['PostToShow']['FEED_DESTINATION']['SONETGROUPS'] = array_merge($arResult['PostToShow']['FEED_DESTINATION']['SONETGROUPS'], $sonetGroupsAdditionalList);
					}
				}
			}
		}

		$arDestUser = [
			'LAST' => [],
			'SELECTED' => []
		];

		$arResult['PostToShow']['FEED_DESTINATION']['SELECTED'] = [];

		if (empty($arResult['PostToShow']['SPERM']))
		{
			$requestDestData = $request->get('destTo');
			if (!empty($requestDestData))
			{
				if (!is_array($requestDestData))
				{
					$requestDestData = [ $requestDestData ];
				}

				foreach ($requestDestData as $dest)
				{
					if (preg_match('/^U(\d+)$/', $dest, $matches))
					{
						$arResult['PostToShow']['FEED_DESTINATION']['SELECTED'][$dest] = 'users';
					}
					elseif (preg_match('/^SG(\d+)$/', $dest, $matches))
					{
						$arResult['PostToShow']['FEED_DESTINATION']['SELECTED'][$dest] = 'sonetgroups';
					}
					elseif (preg_match('/^DR(\d+)$/', $dest, $matches))
					{
						$arResult['PostToShow']['FEED_DESTINATION']['SELECTED'][$dest] = 'department';
					}
					elseif ($dest === 'UA')
					{
						$arResult['PostToShow']['FEED_DESTINATION']['SELECTED'][$dest] = 'groups';
					}
				}
			}
			elseif ($arResult['bExtranetUser'])
			{
				if (!empty($arResult['PostToShow']['FEED_DESTINATION']['LAST']['SONETGROUPS']))
				{
					foreach ($arResult['PostToShow']['FEED_DESTINATION']['LAST']['SONETGROUPS'] as $val)
					{
						$arResult['PostToShow']['FEED_DESTINATION']['SELECTED'][$val] = 'sonetgroups';
					}
				}
				else
				{
					foreach ($arResult['PostToShow']['FEED_DESTINATION']['SONETGROUPS'] as $k => $val)
					{
						$arResult['PostToShow']['FEED_DESTINATION']['SELECTED'][$k] = 'sonetgroups';
					}
				}

				if (empty($arResult['PostToShow']['FEED_DESTINATION']['SELECTED']))
				{
					$arResult['FATAL_MESSAGE'] .= Loc::getMessage('BLOG_SONET_MODULE_NOT_AVAIBLE');
				}
			}
			elseif ($bDefaultToAll)
			{
				if (ModuleManager::isModuleInstalled('intranet'))
				{
					$siteDepartmentID = Option::get('main', 'wizard_departament', false, SITE_ID, true);
					if ((int)$siteDepartmentID > 0)
					{
						$arResult['PostToShow']['FEED_DESTINATION']['SELECTED']['DR' . (int)$siteDepartmentID] = 'department';
					}
					else
					{
						$arResult['PostToShow']['FEED_DESTINATION']['SELECTED']['UA'] = 'groups';
					}
				}
				else
				{
					$arResult['PostToShow']['FEED_DESTINATION']['SELECTED']['UA'] = 'groups';
				}
			}
		}
		else
		{
			foreach ($arResult['PostToShow']['SPERM'] as $type => $ar)
			{
				if (!is_array($ar))
				{
					continue;
				}

				foreach ($ar as $id => $value)
				{
					if ($type === 'U')
					{
						if (
							$id === 'A'
							|| $value === 'A'
						)
						{
							if ($bAllowToAll)
							{
								$arResult['PostToShow']['FEED_DESTINATION']['SELECTED']['UA'] = 'groups';
							}
						}
						else
						{
							$arResult['PostToShow']['FEED_DESTINATION']['SELECTED']['U' . $id] = 'users';
							$arDestUser['SELECTED'][] = $id;
						}
					}
					elseif ($type === 'SG')
					{
						$arResult['PostToShow']['FEED_DESTINATION']['SELECTED']['SG' . $id] = 'sonetgroups';
					}
					elseif ($type === 'DR')
					{
						$arResult['PostToShow']['FEED_DESTINATION']['SELECTED']['DR' . $id] = 'department';
					}
				}
			}
		}

		$arResult['PostToShow']['FEED_DESTINATION']['EXTRANET_USER'] = ($arResult["bExtranetUser"] ? 'Y' : 'N');

		if ($arResult['SELECTOR_VERSION'] < 2)
		{
			$arResult['PostToShow']['FEED_DESTINATION']['HIDDEN_GROUPS'] = [];
			$arHiddenGroups = [];
			$arUserCodesSelected = [];
			$arDepartmentCodesSelected = [];

			if (!empty($arResult['PostToShow']['FEED_DESTINATION']['SELECTED']))
			{
				foreach ($arResult['PostToShow']['FEED_DESTINATION']['SELECTED'] as $groupId => $value)
				{
					if (
						$value === 'sonetgroups'
						&& empty($arResult['PostToShow']['FEED_DESTINATION']['SONETGROUPS'][$groupId])
					)
					{
						$arHiddenGroups[] = mb_substr($groupId, 2);
					}
					elseif ($value === 'users')
					{
						$arUserCodesSelected[] = $groupId;
					}
					elseif ($value === 'department')
					{
						$arDepartmentCodesSelected[] = $groupId;
					}
				}
			}

			if (!empty($arHiddenGroups))
			{
				$res = \Bitrix\Socialnetwork\WorkgroupTable::getList([
					'filter' => [
						'@ID' => $arHiddenGroups,
					],
					'select' => [ 'ID', 'NAME', 'DESCRIPTION', 'OPENED' ],
				]);

				while ($group = $res->fetch())
				{
					if (
						$group['OPENED'] === 'Y'
						|| CSocNetUser::isCurrentUserModuleAdmin()
					)
					{
						$arResult['PostToShow']['FEED_DESTINATION']['SONETGROUPS']['SG' . $group['ID']] = [
							'id' => 'SG' . $group['ID'],
							'entityId' => $group['ID'],
							'name' => $group['NAME'],
							'desc' => $group['DESCRIPTION'],
						];
					}
					else
					{
						$arResult['PostToShow']['FEED_DESTINATION']['HIDDEN_GROUPS'][$group['ID']] = [
							'ID' => $group['ID'],
							'NAME' => $group['NAME'],
							'TYPE' => 'sonetgroups',
							'PREFIX' => 'SG',
						];
					}
				}
			}

			if (!CSocNetUser::isCurrentUserModuleAdmin() && is_object($USER))
			{
				$arGroupID = CSocNetLogTools::getAvailableGroups(
					($arResult['bExtranetUser'] ? 'Y' : 'N'),
					($arResult['bExtranetSite'] ? 'Y' : 'N')
				);

				foreach ($arResult['PostToShow']['FEED_DESTINATION']['HIDDEN_GROUPS'] as $group_code => $arBlogSPerm)
				{
					if (!in_array($group_code, $arGroupID))
					{
						$arResult['PostToShow']['FEED_DESTINATION']['HIDDEN_GROUPS'][$group_code]['NAME'] = Loc::getMessage('B_B_HIDDEN_GROUP');
					}
				}
			}

			$tmp = $arResult['PostToShow']['FEED_DESTINATION']['HIDDEN_GROUPS'];
			$arResult['PostToShow']['FEED_DESTINATION']['HIDDEN_GROUPS'] = [];
			foreach ($tmp as $key => $value)
			{
				$arResult['PostToShow']['FEED_DESTINATION']['HIDDEN_GROUPS']['SG' . $key] = $value;
			}

			$arResult['PostToShow']['FEED_DESTINATION']['HIDDEN_ITEMS'] = $arResult['PostToShow']['FEED_DESTINATION']['HIDDEN_GROUPS'];

			// intranet structure
			$arStructure = CSocNetLogDestination::getStucture([ 'LAZY_LOAD' => true ]);
			$arResult['PostToShow']['FEED_DESTINATION']['DEPARTMENT'] = $arStructure['department'];
			$arResult['PostToShow']['FEED_DESTINATION']['DEPARTMENT_RELATION'] = $arStructure['department_relation'];
			$arResult['PostToShow']['FEED_DESTINATION']['DEPARTMENT_RELATION_HEAD'] = $arStructure['department_relation_head'];

			if ($arResult['bExtranetUser'])
			{
				$arResult['PostToShow']['FEED_DESTINATION']['USERS'] = CSocNetLogDestination::getExtranetUser();
			}
			else
			{
				if (!empty($arResult['PostToShow']['FEED_DESTINATION']['LAST']['USERS']))
				{
					foreach ($arResult['PostToShow']['FEED_DESTINATION']['LAST']['USERS'] as $value)
					{
						$arDestUser['LAST'][] = str_replace('U', '', $value);
					}
				}

				$destinationUsersLast = [];
				$destinationUsersSelected = [];

				if (!empty($arDestUser['LAST']))
				{
					$destinationUsersLast = CSocNetLogDestination::getUsers([
						'id' => $arDestUser['LAST'],
						'CRM_ENTITY' => ModuleManager::isModuleInstalled('crm'),
					]);
				}

				if (!empty($arDestUser['SELECTED']))
				{
					$destinationUsersSelected = CSocNetLogDestination::getUsers(array(
						'id' => $arDestUser['SELECTED'],
						'CRM_ENTITY' => ModuleManager::isModuleInstalled('crm'),
						'IGNORE_ACTIVITY' => 'Y',
					));
				}

				$arResult['PostToShow']['FEED_DESTINATION']['USERS'] = array_merge($destinationUsersLast, $destinationUsersSelected);

				if ($arResult['ALLOW_EMAIL_INVITATION'])
				{
					CSocNetLogDestination::fillEmails($arResult['PostToShow']['FEED_DESTINATION']);
				}
			}

			foreach ($arUserCodesSelected as $selectedUserCode)
			{
				if (array_key_exists($selectedUserCode, $arResult['PostToShow']["FEED_DESTINATION"]['USERS']))
				{
					continue;
				}

				$arResult['PostToShow']['FEED_DESTINATION']['HIDDEN_ITEMS'][$selectedUserCode] = [
					'ID' => mb_substr($selectedUserCode, 1),
					'NAME' => Loc::getMessage('B_B_HIDDEN_USER'),
					'TYPE' => 'users',
					'PREFIX' => 'U',
				];
			}

			foreach ($arDepartmentCodesSelected as $selectedDepartmentCode)
			{
				$departrmentIdToCheckList = [];
				if (!array_key_exists($selectedDepartmentCode, $arResult['PostToShow']['FEED_DESTINATION']['DEPARTMENT']))
				{
					$departrmentIdToCheckList[] = mb_substr($selectedDepartmentCode, 2);
				}

				if (
					!empty($departrmentIdToCheckList)
					&& Loader::includeModule('iblock')
					&& (($structureIBlockId = Option::get('intranet', 'iblock_structure', 0)) > 0)
				)
				{
					$res = CIBlockSection::getList(
						[],
						[
							'=IBLOCK_ID' => $structureIBlockId,
							'ID' => $departrmentIdToCheckList,
							'=ACTIVE' => 'Y',
						],
						false,
						[ 'ID' ]
					);

					while ($section = $res->fetch())
					{
						$arResult['PostToShow']['FEED_DESTINATION']['HIDDEN_ITEMS'][$selectedDepartmentCode] = [
							'ID' => $section['ID'],
							'NAME' => Loc::getMessage('B_B_HIDDEN_DEPARTMENT'),
							'TYPE' => 'department',
							'PREFIX' => 'DR',
						];
					}
				}
			}

			$arResult['PostToShow']['FEED_DESTINATION']['USERS_VACATION'] = \Bitrix\Socialnetwork\Integration\Intranet\Absence\User::getDayVacationList();
		}

		$arResult['PostToShow']['FEED_DESTINATION']['DENY_TOALL'] = !$bAllowToAll;
	}
}
else
{
	$arResult['FATAL_MESSAGE'] = Loc::getMessage('BLOG_ERR_NO_RIGHTS');
}

CSocNetTools::InitGlobalExtranetArrays();
Loader::includeModule('intranet'); // for gov/public language messages

$this->includeComponentTemplate();
