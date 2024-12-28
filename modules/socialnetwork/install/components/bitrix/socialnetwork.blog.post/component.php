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

global $CACHE_MANAGER, $USER_FIELD_MANAGER;

use Bitrix\Blog\Item\Permissions;
use Bitrix\Main\Page\Asset;
use Bitrix\Socialnetwork\Collab\Provider\CollabProvider;
use Bitrix\Socialnetwork\Item\Helper;
use Bitrix\Socialnetwork\Livefeed;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Main\Engine\ActionFilter\Service\Token;
use Bitrix\Socialnetwork\Integration\Extranet\User;

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}
if (!CModule::IncludeModule("socialnetwork"))
{
	return;
}

if (
	!isset($arParams["CHECK_PERMISSIONS_DEST"])
	|| $arParams["CHECK_PERMISSIONS_DEST"] == ''
)
{
	$arParams["CHECK_PERMISSIONS_DEST"] = "N";
}

$user_id = (int)$USER->getId();
$arResult['USER_ID'] = $user_id;
$arResult["TZ_OFFSET"] = CTimeZone::GetOffset();

$arParams["TYPE"] ??= null;
$arResult["bFromList"] = (($arParams["FROM_LOG"] ?? null) === "Y" || $arParams["TYPE"] === "DRAFT" || $arParams["TYPE"] === "MODERATION");
$arResult["contentViewIsSet"] = false;
$arResult["bIntranetInstalled"] = ModuleManager::isModuleInstalled("intranet");
$arResult["bExtranetInstalled"] = ($arResult["bIntranetInstalled"] && CModule::IncludeModule("extranet"));
$arResult["bExtranetSite"] = ($arResult["bExtranetInstalled"] && CExtranet::IsExtranetSite());
$arResult["bExtranetUser"] = ($arResult["bExtranetInstalled"] && !CExtranet::IsIntranetUser());
$arResult["ReadOnly"] = (
	(isset($arParams["GROUP_READ_ONLY"]) && $arParams["GROUP_READ_ONLY"] === "Y")
	|| (isset($arParams["MODE"]) && $arParams["MODE"] === "LANDING")
);

$folderUsers = COption::GetOptionString("socialnetwork", "user_page", false, SITE_ID);
$arParams["PATH_TO_LOG_TAG"] = $folderUsers."log/?TAG=#tag#";
if (
	defined('SITE_TEMPLATE_ID')
	&& SITE_TEMPLATE_ID === 'bitrix24'
)
{
	$arParams["PATH_TO_LOG_TAG"] .= "&apply_filter=Y";
}

$arParams["GROUP_ID"] = $arParams["GROUP_ID"] ?? [];
if (!is_array($arParams["GROUP_ID"]))
{
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
}

foreach($arParams["GROUP_ID"] as $k=>$v)
{
	if((int)$v <= 0)
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
		elseif((int)$tmpVal > 0)
		{
			$arParams["GROUP_ID"] = array($arTmpVal);
		}
	}
}

$arResult["bPublicPage"] = (isset($arParams["PUB"]) && $arParams["PUB"] === "Y");

if ($arResult["bExtranetUser"] && !$arResult["bPublicPage"])
{
	$arUserIdVisible = CExtranet::GetMyGroupsUsersSimple(SITE_ID);
}

$arParams['FORM_ID'] = ($arParams['FORM_ID'] ?? '');

$arResult["bTasksInstalled"] = CModule::IncludeModule("tasks");
$arResult["bTasksAvailable"] = (
	!$arResult["bPublicPage"]
	&& $arResult["bTasksInstalled"]
	&& (
		!CModule::IncludeModule('bitrix24')
		|| CBitrix24BusinessTools::isToolAvailable($arResult['USER_ID'], "tasks")
	)
	&& \Bitrix\Tasks\Access\TaskAccessController::can($arResult['USER_ID'], \Bitrix\Tasks\Access\ActionDictionary::ACTION_TASK_CREATE)
);

if (!$arResult["bPublicPage"])
{
	$arParams["BLOG_VAR"] = $arParams["BLOG_VAR"] ?? '';
	$arParams["PAGE_VAR"] = $arParams["PAGE_VAR"] ?? '';
	$arParams["USER_VAR"] = $arParams["USER_VAR"] ?? '';
	$arParams["POST_VAR"] = $arParams["POST_VAR"] ?? '';

	if ($arParams["BLOG_VAR"] == '')
	{
		$arParams["BLOG_VAR"] = "blog";
	}
	if ($arParams["PAGE_VAR"] == '')
	{
		$arParams["PAGE_VAR"] = "page";
	}
	if ($arParams["USER_VAR"] == '')
	{
		$arParams["USER_VAR"] = "id";
	}
	if ($arParams["POST_VAR"] == '')
	{
		$arParams["POST_VAR"] = "id";
	}

	$applicationCurPage = $APPLICATION->GetCurPage();

	$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"] ?? '');
	if ($arParams["PATH_TO_BLOG"] == '')
	{
		$arParams["PATH_TO_BLOG"] = htmlspecialcharsbx($applicationCurPage."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");
	}

	$arParams["PATH_TO_POST_IMPORTANT"] = trim($arParams["PATH_TO_POST_IMPORTANT"] ?? '');
	if ($arParams["PATH_TO_POST_IMPORTANT"] == '')
	{
		$arParams["PATH_TO_POST_IMPORTANT"] = "/company/personal/user/#user_id#/blog/important/";
	}

	$arParams["PATH_TO_BLOG_CATEGORY"] = trim($arParams["PATH_TO_BLOG_CATEGORY"] ?? '');
	if ($arParams["PATH_TO_BLOG_CATEGORY"] == '')
	{
		$arParams["PATH_TO_BLOG_CATEGORY"] = htmlspecialcharsbx($applicationCurPage."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#"."&category=#category_id#");
	}

	$arParams["PATH_TO_POST_EDIT"] = trim($arParams["PATH_TO_POST_EDIT"] ?? '');
	if ($arParams["PATH_TO_POST_EDIT"] == '')
	{
		$arParams["PATH_TO_POST_EDIT"] = "/company/personal/user/#user_id#/blog/edit/#post_id#/";
	}

	$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"] ?? '');
	if ($arParams["PATH_TO_USER"] == '')
	{
		$arParams["PATH_TO_USER"] = htmlspecialcharsbx($applicationCurPage."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
	}

	if (($arParams["PATH_TO_SEARCH_TAG"] ?? null) == '')
	{
		$arParams["PATH_TO_SEARCH_TAG"] = SITE_DIR."search/?tags=#tag#";
	}

	if (!isset($arParams["PATH_TO_CONPANY_DEPARTMENT"]) || $arParams["PATH_TO_CONPANY_DEPARTMENT"] == '')
	{
		$arParams["PATH_TO_CONPANY_DEPARTMENT"] = "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#";
	}
}

$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"] ?? '');
if ($arParams["PATH_TO_POST"] == '')
{
	$arParams["PATH_TO_POST"] = "/company/personal/user/#user_id#/blog/#post_id#/";
}

$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"] ?? '') == '' ? false : trim($arParams["PATH_TO_SMILE"]);

if (!isset($arParams["PATH_TO_MESSAGES_CHAT"]) || $arParams["PATH_TO_MESSAGES_CHAT"] == '')
	$arParams["PATH_TO_MESSAGES_CHAT"] = "/company/personal/messages/chat/#user_id#/";
if (!isset($arParams["PATH_TO_VIDEO_CALL"]) || $arParams["PATH_TO_VIDEO_CALL"] == '')
	$arParams["PATH_TO_VIDEO_CALL"] = "/company/personal/video/#user_id#/";

$arParams["CACHE_TIME"] = 3600*24*365;

if (trim($arParams["NAME_TEMPLATE"] ?? '') == '')
{
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
}

$arParams['SHOW_LOGIN'] = $arParams['SHOW_LOGIN'] !== "N" ? "Y" : "N";
$arParams['DATE_TIME_FORMAT_S'] = $arParams['DATE_TIME_FORMAT'];

CSocNetLogComponent::processDateTimeFormatParams($arParams);

// activation rating
CRatingsComponentsMain::GetShowRating($arParams);
$arParams["USE_CUT"] = ($arParams["USE_CUT"] ?? null) === "Y" ? "Y" : "N";
$arParams["SEF"] = (isset($arParams["SEF"]) && $arParams["SEF"] === "N" ? "N" : "Y");

$arParams["IMAGE_MAX_WIDTH"] = (int)$arParams["IMAGE_MAX_WIDTH"];
$arParams["IMAGE_MAX_HEIGHT"] = (int)$arParams["IMAGE_MAX_HEIGHT"];
if ($arParams["IMAGE_MAX_WIDTH"] <= 0)
{
	$arParams["IMAGE_MAX_WIDTH"] = \Bitrix\Blog\Util::getImageMaxWidth();
}
if ($arParams["IMAGE_MAX_HEIGHT"] <= 0)
{
	$arParams["IMAGE_MAX_HEIGHT"] = \Bitrix\Blog\Util::getImageMaxHeight();
}

$arParams["ATTACHED_IMAGE_MAX_WIDTH_SMALL"] = $arParams["ATTACHED_IMAGE_MAX_WIDTH_SMALL"] ?? 0;
$arParams["ATTACHED_IMAGE_MAX_HEIGHT_SMALL"] = $arParams["ATTACHED_IMAGE_MAX_HEIGHT_SMALL"] ?? 0;
$arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"] = $arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"] ?? 0;
$arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"] = $arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"] ?? 0;

$arParams["ATTACHED_IMAGE_MAX_WIDTH_SMALL"] = ((int)$arParams["ATTACHED_IMAGE_MAX_WIDTH_SMALL"] > 0 ? (int)$arParams["ATTACHED_IMAGE_MAX_WIDTH_SMALL"] : 70);
$arParams["ATTACHED_IMAGE_MAX_HEIGHT_SMALL"] = ((int)$arParams["ATTACHED_IMAGE_MAX_HEIGHT_SMALL"] > 0 ? (int)$arParams["ATTACHED_IMAGE_MAX_HEIGHT_SMALL"] : 70);
$arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"] = ((int)$arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"] > 0 ? (int)$arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"] : 1000);
$arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"] = ((int)$arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"] > 0 ? (int)$arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"] : 1000);

$arParams["AVATAR_SIZE_COMMON"] = (isset($arParams["AVATAR_SIZE_COMMON"]) && (int)$arParams["AVATAR_SIZE_COMMON"] > 0) ? (int)$arParams["AVATAR_SIZE_COMMON"] : 100;
$arParams["AVATAR_SIZE"] = (isset($arParams["AVATAR_SIZE"]) && (int)$arParams["AVATAR_SIZE"] > 0) ? (int)$arParams["AVATAR_SIZE"] : 100;
$arParams["AVATAR_SIZE_COMMENT"] = (isset($arParams["AVATAR_SIZE_COMMENT"]) && (int)$arParams["AVATAR_SIZE_COMMENT"] > 0) ? (int)$arParams["AVATAR_SIZE_COMMENT"] : 100;

$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";
$arParams["CHECK_COMMENTS_PERMS"] = (isset($arParams["CHECK_COMMENTS_PERMS"]) && $arParams["CHECK_COMMENTS_PERMS"] === "Y" ? "Y" : "N");

if(empty($arParams["POST_PROPERTY"]))
{
	$arParams["POST_PROPERTY"] = array();
}

$arParams["POST_PROPERTY_SOURCE"] = $arParams["POST_PROPERTY"];

$arParams["POST_PROPERTY"][] = "UF_BLOG_POST_DOC";
$arParams["POST_PROPERTY"][] = "UF_BLOG_POST_IMPRTNT";
if (
	CModule::IncludeModule("webdav")
	|| CModule::IncludeModule("disk")
)
{
	$arParams["POST_PROPERTY"][] = "UF_BLOG_POST_FILE";
	$arParams["POST_PROPERTY"][] = "UF_BLOG_POST_D_FILE";
}
if(IsModuleInstalled("vote"))
{
	$arParams["POST_PROPERTY"][] = "UF_BLOG_POST_VOTE";
}
if($arResult["bIntranetInstalled"])
{
	$arParams["POST_PROPERTY"][] = "UF_GRATITUDE";
}
$arParams["POST_PROPERTY"][] = "UF_BLOG_POST_URL_PRV";

if (
	!array_key_exists("GET_FOLLOW", $arParams)
	|| $arParams["GET_FOLLOW"] == ''
	|| (defined("DisableSonetLogFollow") && DisableSonetLogFollow === true)
)
{
	$arParams["GET_FOLLOW"] = "N";
}

if (\Bitrix\Main\Loader::includeModule('mail'))
{
	$arParams['POST_PROPERTY'][] = 'UF_MAIL_MESSAGE';
}

$arResult["ALLOW_EMAIL_INVITATION"] = (
	IsModuleInstalled('mail')
	&& $arResult["bIntranetInstalled"]
	&& (
		!\Bitrix\Main\Loader::includeModule('bitrix24')
		|| CBitrix24::isEmailConfirmed()
	)
);

$arBlog = [];

if (
	!$arResult["bFromList"]
	&& !$arResult["bPublicPage"]
)
{
	$arParams["USE_CUT"] = "N";

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
			$blogGroupId = \Bitrix\Main\Config\Option::get(
				'socialnetwork',
				'userbloggroup_id',
				false,
				SITE_ID
			);
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
	}

	$arResult["Blog"] = $arBlog;

	if($USER->IsAuthorized())
	{
		CSocNetTools::InitGlobalExtranetArrays();
		if (isset($GLOBALS["arExtranetGroupID"]))
		{
			$arResult["arExtranetGroupID"] = $GLOBALS["arExtranetGroupID"];
		}
	}
}

$arParams["ID"] = trim($arParams["ID"]);
if(preg_match("/^[1-9][0-9]*\$/", $arParams["ID"]))
{
	$arParams["ID"] = (int)$arParams["ID"];
}
else
{
	$arParams["ID"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["~ID"]));
	$arParams["ID"] = CBlogPost::GetID($arParams["ID"], $arBlog["ID"]);
}

if (
	$arParams["ID"] == ""
	&& !$arResult["bFromList"]
)
{
	ShowError(GetMessage("B_B_MES_NO_POST"));
	@define("ERROR_404", "Y");
	CHTTP::SetStatus("404 Not Found");
	return;
}

$arPost = array();
$cacheTtl = 2592000;
$cacheId = 'blog_post_socnet_general_'.$arParams["ID"].'_'.LANGUAGE_ID.($arResult["TZ_OFFSET"] <> 0 ? "_".$arResult["TZ_OFFSET"] : "")."_".Bitrix\Main\Context::getCurrent()->getCulture()->getDateTimeFormat();
$cacheDir = ComponentHelper::getBlogPostCacheDir(array(
	'TYPE' => 'post_general',
	'POST_ID' => $arParams["ID"]
));

$obCache = new CPHPCache;
if($obCache->InitCache($cacheTtl, $cacheId, $cacheDir))
{
	$arPost = $obCache->GetVars();
	$postItem = new \Bitrix\Blog\Item\Post;
	$postItem->setFields($arPost);
}

if (empty($arPost["ID"]))
{
	$obCache->StartDataCache();
	$postItem = \Bitrix\Blog\Item\Post::getById($arParams["ID"]);
	$arPost = $postItem->getFields();
	$obCache->EndDataCache($arPost);
}

if (
	!empty($arPost)
	&& $arPost["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH
	&& !in_array($arParams["TYPE"], array("DRAFT", "MODERATION"))
)
{
	unset($arPost);
}

$a = new CAccess;
$a->UpdateCodes();

if(
	(
		!empty($arBlog)
		&& $arBlog["ACTIVE"] === "Y"
	)
	|| $arResult["bFromList"]
	|| $arResult["bPublicPage"]
)
{
	if(!empty($arPost))
	{
		$bNoLogEntry = false;

		$fetchLogEntryNeeded = false;
		if (
			$arParams['GET_FOLLOW'] === 'Y'
			&& (
				!array_key_exists('FOLLOW', $arParams)
				|| $arParams['FOLLOW'] == ''
			)
		)
		{
			$fetchLogEntryNeeded = true;
		}

		if (
			$USER->isAuthorized()
			&& (
				!array_key_exists('PINNED', $arParams)
				|| $arParams['PINNED'] == ''
			)
		)
		{
			$fetchLogEntryNeeded = true;
		}

		if (
			!$fetchLogEntryNeeded
			&& (int)$arParams['LOG_ID'] <= 0
		)
		{
			$fetchLogEntryNeeded = true;
		}

		if (
			!$fetchLogEntryNeeded
			&& $arResult['bPublicPage']
		)
		{
			$fetchLogEntryNeeded = true;
		}

		if (
			$fetchLogEntryNeeded
			&& CModule::IncludeModule("socialnetwork")
		)
		{
			$blogPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;

			$arFilter = array(
				"EVENT_ID" => $blogPostLivefeedProvider->getEventId(),
				"SOURCE_ID" => $arParams["ID"],
			);

			if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite())
			{
				$arFilter["SITE_ID"] = SITE_ID;
			}
			elseif (!$arResult["bPublicPage"])
			{
				$arFilter["SITE_ID"] = array(SITE_ID, false);
			}

			$listParams = [];
			$listSelect = [ 'ID', 'FAVORITES_USER_ID' ];
			if ($arParams['GET_FOLLOW'] === 'Y')
			{
				$listParams['USE_FOLLOW'] = 'Y';
				$listSelect[] = 'FOLLOW';
			}
			if ($USER->isAuthorized())
			{
				$listParams['USE_PINNED'] = 'Y';
				$listSelect[] = 'PINNED_USER_ID';
			}

			$rsLogSrc = CSocNetLog::GetList(
				array(),
				$arFilter,
				false,
				false,
				$listSelect,
				$listParams
			);

			if ($arLogSrc = $rsLogSrc->Fetch())
			{
				$arParams["LOG_ID"] = $arLogSrc["ID"];
				$arParams["FAVORITES_USER_ID"] = $arLogSrc["FAVORITES_USER_ID"];
				if ($arParams["GET_FOLLOW"] === "Y")
				{
					$arParams["FOLLOW"] = $arLogSrc["FOLLOW"];
				}
				if ($USER->isAuthorized())
				{
					$arParams['PINNED'] = ($arLogSrc['PINNED_USER_ID'] ? 'Y' : 'N');
				}
			}
			elseif (!$arResult["bFromList"])
			{
				$bNoLogEntry = true;
			}
		}

		$arPost = CBlogTools::htmlspecialcharsExArray($arPost);

		if($arPost["AUTHOR_ID"] == $user_id)
		{
			$arResult["PostPerm"] = Permissions::FULL;
		}
		elseif ($arResult["bFromList"])
		{
			$arResult["PostPerm"] = Permissions::READ;
			if (
				CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, (!isset($arParams["MOBILE"]) || $arParams["MOBILE"] !== "Y"))
				|| CMain::GetGroupRight("blog") >= "W"
			)
			{
				$arResult["PostPerm"] = Permissions::FULL;
			}
		}
		else
		{
			if ($bNoLogEntry)
			{
				$arResult["PostPerm"] = Permissions::DENY;
			}
			elseif (
				CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, (!isset($arParams["MOBILE"]) || $arParams["MOBILE"] !== "Y"))
				|| CMain::GetGroupRight("blog") >= "W"
			)
			{
				$arResult["PostPerm"] = Permissions::FULL;
			}
			else
			{
				$permsResult = $postItem->getSonetPerms([
					"PUBLIC" => $arResult["bPublicPage"],
					"CHECK_FULL_PERMS" => true,
					"LOG_ID" => (isset($arParams["LOG_ID"]) ? $arParams["LOG_ID"] : false),
				]);
				$arResult["PostPerm"] = $permsResult['PERM'];
			}
		}

		$arPost["perms"] = $arResult["PostPerm"];

		$arResult["Post"] = $arPost;
		$arResult["Post"]["SPERM"] = [];
		$arResult["PostSrc"] = $arPost;
		$arResult["Blog"] = $arBlog;
		$arResult["PostSrc"]["PATH_TO_CONPANY_DEPARTMENT"] = $arParams["PATH_TO_CONPANY_DEPARTMENT"];
		$arResult["PostSrc"]["PATH_TO_GROUP"] = $arParams["PATH_TO_GROUP"];
		$arResult["PostSrc"]["bExtranetSite"] = $arResult["bExtranetSite"];

		$arResult["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("post_id"=>CBlogPost::GetPostID($arResult["Post"]["ID"], $arResult["Post"]["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arPost["AUTHOR_ID"]));
		$arResult["urlToPostPub"] = (isset($arParams["POST_DATA"]["urlToPub"]) && is_array($arParams["POST_DATA"]) ? $arParams["POST_DATA"]["urlToPub"] : '');

		if (
			!$arResult["bPublicPage"]
			&& is_set($arParams["PATH_TO_GROUP"])
		)
		{
			$strSiteWorkgroupsPage = COption::GetOptionString("socialnetwork", "workgroups_page", SITE_DIR."workgroups/", SITE_ID);
			if (
				($strSiteWorkgroupsPage <> '')
				&& mb_strpos($arParams["PATH_TO_GROUP"], $strSiteWorkgroupsPage) === 0
			)
			{
				$arParams["PATH_TO_GROUP"] = "#GROUPS_PATH#".mb_substr($arParams["PATH_TO_GROUP"], mb_strlen($strSiteWorkgroupsPage), mb_strlen($arParams["PATH_TO_GROUP"]) - mb_strlen($strSiteWorkgroupsPage));
			}
		}

		if (
			isset($_GET["delete"])
			&& $_GET["delete"] === "Y"
			&& !$arResult["bFromList"]
			&& !$arResult["bPublicPage"]
		)
		{
			if (check_bitrix_sessid())
			{
				try
				{
					Helper::deleteBlogPost([
						'POST_ID' => $arParams['ID'],
					]);
					$url = CComponentEngine::makePathFromTemplate($arParams['PATH_TO_BLOG'], [
						'blog' => $arBlog['URL'],
						'user_id' => $arBlog['OWNER_ID'],
						'group_id' => $arBlog['SOCNET_GROUP_ID']
					]);
					$url .= (mb_strpos($url, '?') === false ? '?' : '&') . 'del_id=' . $arParams['ID'] . '&success=Y';
					LocalRedirect($url);
				}
				catch (Exception $e)
				{
					$arResult["ERROR_MESSAGE"] .= $e->getMessage().'<br />';
				}
			}
			else
			{
				$arResult["ERROR_MESSAGE"] .= GetMessage("BLOG_BLOG_SESSID_WRONG").'<br />';
			}
		}

		if (
			isset($_GET["hide"])
			&& $_GET["hide"] === "Y"
			&& !$arResult["bFromList"]
			&& !$arResult["bPublicPage"]
		)
		{
			if (check_bitrix_sessid())
			{
				if ($arResult["PostPerm"] >= Permissions::MODERATE)
				{
					if(CBlogPost::Update($arParams["ID"], Array("PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_READY)))
					{
						BXClearCache(True, ComponentHelper::getBlogPostCacheDir(array(
							'TYPE' => 'posts_popular',
							'SITE_ID' => SITE_ID
						)));
						BXClearCache(true, ComponentHelper::getBlogPostCacheDir(array(
							'TYPE' => 'post',
							'POST_ID' => $arParams["ID"]
						)));
						BXClearCache(true, ComponentHelper::getBlogPostCacheDir(array(
							'TYPE' => 'post_general',
							'POST_ID' => $arParams["ID"]
						)));
						if ($postItem = \Bitrix\Blog\Item\Post::getById($arParams["ID"]))
						{
							$postItem->deactivateLogEntry();
						}

						$url = (
							isset($_REQUEST["SONET_GROUP_ID"])
							&& (int)$_REQUEST["SONET_GROUP_ID"] > 0
							&& !empty($arParams["PATH_TO_GROUP_BLOG"])
								? CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_BLOG"], array("group_id" => (int)$_REQUEST["SONET_GROUP_ID"]))
								: CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("user_id" => $arBlog["OWNER_ID"]))
						);

						if(mb_strpos($url, "?") === false)
						{
							$url .= "?";
						}
						else
						{
							$url .= "&";
						}
						$url .= "hide_id=".$arParams["ID"]."&success=Y";

						LocalRedirect($url);
					}
					else
					{
						$arResult["ERROR_MESSAGE"] .= GetMessage("BLOG_BLOG_BLOG_MES_HIDE_ERROR").'<br />';
					}
				}
				else
				{
					$arResult["ERROR_MESSAGE"] .= GetMessage("BLOG_BLOG_BLOG_MES_HIDE_NO_RIGHTS").'<br />';
				}
			}
			else
			{
				$arResult["ERROR_MESSAGE"] .= GetMessage("BLOG_BLOG_SESSID_WRONG").'<br />';
			}
		}

		if ($arResult["PostPerm"] > Permissions::DENY)
		{
			if (
				!$arResult["bFromList"]
				&& $_SERVER["REQUEST_METHOD"] !== "POST"
			)
			{
				if ($liveFeedEntity = Livefeed\BlogPost::init(array(
					'ENTITY_TYPE' => Livefeed\Provider::DATA_ENTITY_TYPE_BLOG_POST,
					'ENTITY_ID' => $arPost["ID"],
					'LOG_ID' => (isset($arParams["LOG_ID"]) ? (int)$arParams["LOG_ID"] : false)
				)))
				{
					$arResult['contentViewIsSet'] = true;
					$liveFeedEntity->setContentView();
				}
			}

			/* share */
			if(
				$_SERVER["REQUEST_METHOD"] === "POST"
				&& ($_POST["act"] ?? '') === "share"
				&& check_bitrix_sessid()
				&& $USER->IsAuthorized()
			)
			{
				$APPLICATION->RestartBuffer();

				$perms2update = array();
				$spermOld = CBlogPost::getSocNetPerms($arParams["ID"]);
				foreach($spermOld as $type => $val)
				{
					foreach($val as $id => $values)
					{
						if($type !== "U")
						{
							$perms2update[] = $type.$id;
						}
						else
						{
							$perms2update[] = (
								in_array("US".$id, $values)
									? "UA"
									: $type.$id
							);
						}
					}
				}

				$arNewRights = array();

				$this->convertRequestData();

				$spermNew = $_POST["SPERM"];
				$tmp = $_POST;
				$tmp['SPERM'] = $spermNew;
				ComponentHelper::processBlogPostNewMailUser($tmp, $arResult);
				$spermNew = $tmp['SPERM'];

				$bCurrentUserAdmin = CSocNetUser::IsCurrentUserModuleAdmin();
				$canPublish = true;

				foreach($spermNew as $type => $val)
				{
					foreach($val as $id => $code)
					{
						if(in_array($type, array("U", "SG", "DR", "CRMCONTACT")))
						{
							if(!in_array($code, $perms2update))
							{
								if ($type === 'SG')
								{
									$oGrId = (int)str_replace("SG", "", $code);

									$canPublish = (
										$bCurrentUserAdmin
										|| CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $oGrId, "blog", "write_post")
										|| CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $oGrId, "blog", "moderate_post")
										|| CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $oGrId, "blog", "full_post")
									);

									if (!$canPublish)
									{
										break;
									}
								}

								$perms2update[] = $code;
								$arNewRights[] = $code;
							}
						}
						elseif($type === "UA")
						{
							if(!in_array("UA", $perms2update))
							{
								$perms2update[] = "UA";
								$arNewRights[] = "UA";
							}
						}
					}

					if (!$canPublish)
					{
						break;
					}
				}

				if (
					!empty($arNewRights)
					&& $canPublish
				)
				{
					ComponentHelper::processBlogPostShare(
						array(
							"POST_ID" => $arParams["ID"],
							"BLOG_ID" => $arPost["BLOG_ID"],
							"SITE_ID" => SITE_ID,
							"SONET_RIGHTS" => $perms2update,
							"NEW_RIGHTS" => $arNewRights,
							"USER_ID" => $user_id
						),
						array_merge($arParams, array(
							"CAN_USER_COMMENT" => (!$arResult["ReadOnly"] ? 'Y' : 'N')
						))
					);
				}
				elseif (!$canPublish)
				{
					$response = array(
						'errorMessage' => GetMessage('SBP_SHARE_PREMODERATION'),
						'status' => "error",
					);
					$APPLICATION->restartBuffer();
					while (ob_end_clean()) {}
					header('Content-Type:application/json; charset=UTF-8');
					?><?= \Bitrix\Main\Web\Json::encode($response) ?><?php
					CMain::finalActions();
					die;
				}

				die();
			}
			/* end share */
			if (!$arResult["bFromList"])
			{
				if ($arPost["MICRO"] !== "Y")
				{
					$strTitle = $arPost["TITLE"];
				}
				else
				{
					$strTitle = $arPost["DETAIL_TEXT"];
					$strTitle = preg_replace("/\\[(\\/?)(url)(.*?)\\]/isu", "", $strTitle);
					$parser = new CTextParser();
					$parser->allow = array('CLEAR_SMILES' => 'Y', 'IMG' => 'Y');
					$strTitle = preg_replace("/&nbsp;/isu", "", $parser->convertText($strTitle));
					$strTitle = str_replace("<br />", " ", $strTitle);
					$strTitle = blogTextParser::killAllTags($strTitle);
					$strTitle = htmlspecialcharsback(TruncateText($strTitle, 100));
				}
				$APPLICATION->SetTitle($strTitle);
			}

			if ($arParams["SET_NAV_CHAIN"] === "Y")
			{
				$APPLICATION->AddChainItem($arBlog["NAME"], CComponentEngine::MakePathFromTemplate(htmlspecialcharsback($arParams["PATH_TO_BLOG"]), array("blog" => $arBlog["URL"], "user_id" => $arPost["AUTHOR_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"])));
			}

			$cache = new CPHPCache;

			$arCacheID = array();
			$arKeys = array(
				"MOBILE",
				"USE_CUT",
				"PATH_TO_SMILE",
				"ATTACHED_IMAGE_MAX_WIDTH_SMALL",
				"ATTACHED_IMAGE_MAX_HEIGHT_SMALL",
				"ATTACHED_IMAGE_MAX_WIDTH_FULL",
				"ATTACHED_IMAGE_MAX_HEIGHT_FULL",
				"POST_PROPERTY",
				"PATH_TO_USER",
				"PATH_TO_POST",
				"PATH_TO_GROUP",
				"PATH_TO_SEARCH_TAG",
				"PATH_TO_LOG_TAG",
				"IMAGE_MAX_WIDTH",
				"IMAGE_MAX_HEIGHT",
				"DATE_TIME_FORMAT",
				"DATE_TIME_FORMAT_S",
				"ALLOW_POST_CODE",
				"AVATAR_SIZE_COMMENT",
				"NAME_TEMPLATE",
				"SHOW_LOGIN",
				"LAZYLOAD",
				"PUB"
			);
			foreach($arKeys as $param_key)
			{
				$arCacheID[$param_key] = (array_key_exists($param_key, $arParams) ? $arParams[$param_key] : false);
			}

			$cache_id = "blog_socnet_post_".md5(serialize($arCacheID))."_".LANGUAGE_ID."_".$arParams["DATE_TIME_FORMAT"];
			if ($arResult["TZ_OFFSET"] <> 0)
			{
				$cache_id .= "_".$arResult["TZ_OFFSET"];
			}

			if (
				!empty($arParams["MOBILE"])
				&& $arParams["MOBILE"] === "Y"
				&& CModule::IncludeModule('mobile')
			)
			{
				$imageResizeWidth = CMobileHelper::getDeviceResizeWidth();
				if ($imageResizeWidth)
				{
					$cache_id .= "_".$imageResizeWidth;
				}
			}

			$cache_path = ComponentHelper::getBlogPostCacheDir(array(
				'TYPE' => 'post',
				'POST_ID' => $arPost["ID"]
			));

			$isInitCache = (
				$arParams["CACHE_TIME"] > 0
				&& $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path)
			);
			$Vars = [];
			if ($isInitCache)
			{
				$Vars = $cache->GetVars();
			}

			if ($Vars && !empty($Vars["Post"]["ID"]))
			{
				$arResult["POST_PROPERTY"] = $Vars["POST_PROPERTY"] ?? [];
				$arResult["Post"] = $Vars["Post"];
				$arResult["images"] = $Vars["images"];
				$arResult["Category"] = $Vars["Category"];
				$arResult["GRATITUDE"] = $Vars["GRATITUDE"];
				$arResult["URL_PREVIEW"] = ($Vars["URL_PREVIEW"] ?? '');
				$arResult["POST_PROPERTIES"] = $Vars["POST_PROPERTIES"];
				$arResult["arUser"] = $Vars["arUser"];
				$arResult["Assets"] = ($Vars["Assets"] ?? []);

				if (!empty($arResult["Assets"]))
				{
					if (!empty($arResult["Assets"]["CSS"]))
					{
						foreach($arResult["Assets"]["CSS"] as $cssFile)
						{
							Asset::getInstance()->addCss($cssFile);
						}
					}

					if (!empty($arResult["Assets"]["JS"]))
					{
						foreach($arResult["Assets"]["JS"] as $jsFile)
						{
							Asset::getInstance()->addJs($jsFile);
						}
					}
				}
				$cache->Output();
			}
			else
			{
				$arResult["Post"]["hasVideoInline"] = false;
				$arResult["Assets"] = array(
					"CSS" => array(),
					"JS" => array()
				);

				if ($arParams["CACHE_TIME"] > 0)
				{
					$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
					if (defined("BX_COMP_MANAGED_CACHE"))
					{
						$CACHE_MANAGER->StartTagCache($cache_path);
						$CACHE_MANAGER->RegisterTag("USER_NAME_".(int)$arPost["AUTHOR_ID"]);
					}
				}

				$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"], array("bPublic" => $arResult["bPublicPage"]));
				$p->bMobile = (isset($arParams["MOBILE"]) && $arParams["MOBILE"] === "Y");

				$arResult["POST_PROPERTIES"] = array("SHOW" => "N");

				$bHasImg = false;
				$bHasTag = false;
				$bHasProps = false;
				$bHasOnlyAll = false;

				if (!empty($arParams["POST_PROPERTY"]))
				{
					if($arPost["HAS_PROPS"] !== "N")
					{
						$arPostFields = $USER_FIELD_MANAGER->GetUserFields("BLOG_POST", $arPost["ID"], LANGUAGE_ID);

						if (count($arParams["POST_PROPERTY"]) > 0)
						{
							foreach ($arPostFields as $FIELD_NAME => $arPostField)
							{
								if (!in_array($FIELD_NAME, $arParams["POST_PROPERTY"]))
								{
									continue;
								}

								if(
									$FIELD_NAME === "UF_GRATITUDE"
									&& array_key_exists("VALUE", $arPostField)
									&& (int)$arPostField["VALUE"] > 0
								)
								{
									$bHasProps = true;
									$gratValue = $arPostField["VALUE"];

									if (CModule::IncludeModule("iblock"))
									{
										if (
											!is_array($GLOBALS["CACHE_HONOUR"])
											|| !array_key_exists("honour_iblock_id", $GLOBALS["CACHE_HONOUR"])
											|| (int)$GLOBALS["CACHE_HONOUR"]["honour_iblock_id"] <= 0
										)
										{
											$rsIBlock = CIBlock::GetList(array(), array("=CODE" => "honour", "=TYPE" => "structure"));
											if ($arIBlock = $rsIBlock->Fetch())
											{
												$GLOBALS["CACHE_HONOUR"]["honour_iblock_id"] = $arIBlock["ID"];
											}
										}

										if ((int)$GLOBALS["CACHE_HONOUR"]["honour_iblock_id"] > 0)
										{
											$arGrat = array(
												"USERS" => array(),
												"USERS_FULL" => array(),
												"TYPE" => false
											);
											$rsElementProperty = CIBlockElement::GetProperty(
												$GLOBALS["CACHE_HONOUR"]["honour_iblock_id"],
												$gratValue
											);
											while ($arElementProperty = $rsElementProperty->GetNext())
											{
												if (
													$arElementProperty["CODE"] === "USERS"
													&& (int)$arElementProperty["VALUE"] > 0
												)
												{
													$arGrat["USERS"][] = $arElementProperty["VALUE"];
												}
												elseif ($arElementProperty["CODE"] === "GRATITUDE")
												{
													$arGrat["TYPE"] = array(
														"VALUE_ENUM" => $arElementProperty["VALUE_ENUM"],
														"XML_ID" => $arElementProperty["VALUE_XML_ID"]
													);
												}
											}

											if (count($arGrat["USERS"]) > 0)
											{
												if (
													$arParams["CACHE_TIME"] > 0
													&& defined("BX_COMP_MANAGED_CACHE")
												)
												{
													foreach($arGrat["USERS"] as $i => $grat_user_id)
													{
														$CACHE_MANAGER->RegisterTag("USER_NAME_".(int)$grat_user_id);
													}
												}

												$arGratUsers = array();

												$rsUser = CUser::GetList(
													'',
													'',
													array(
														"ID" => implode("|", $arGrat["USERS"])
													),
													array(
														"FIELDS" => array(
															"ID",
															"PERSONAL_GENDER", "PERSONAL_PHOTO",
															"LOGIN", "NAME", "LAST_NAME", "SECOND_NAME", "EMAIL",
															"WORK_POSITION"
														)
													)
												);

												while ($arGratUser = $rsUser->Fetch())
												{
													$arGratUser["AVATAR_SRC"] = CSocNetLogTools::FormatEvent_CreateAvatar($arGratUser, array("AVATAR_SIZE" => (isset($arParams["AVATAR_SIZE_COMMON"]) ? $arParams["AVATAR_SIZE_COMMON"] : 58)), "");
													$arGratUser["AVATAR_SIZE"] = (($arParams["MOBILE"] ?? '') === "Y" ? 58 : (count($arGrat["USERS"]) <= 4 ? 50 : 26));
													$arGratUser["URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arGratUser["ID"]));
													$arGratUsers[] = $arGratUser;
												}

												$arGrat["USERS_FULL"] = $arGratUsers;
											}
											if (count($arGrat["USERS_FULL"]) > 0)
											{
												$arResult["GRATITUDE"] = $arGrat;
											}
										}
									}
								}
								else
								{
									$arPostField["EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"] <> '' ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
									$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arPostField["EDIT_FORM_LABEL"]);
									$arPostField["~EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"];
									$arResult["POST_PROPERTIES"]["DATA"][$FIELD_NAME] = $arPostField;

									if (!empty($arPostField["VALUE"]))
									{
										$bHasProps = true;
									}
								}
							}
						}
						if (!empty($arResult["POST_PROPERTIES"]["DATA"]))
						{
							$arResult["POST_PROPERTIES"]["SHOW"] = "Y";
						}
					}
				}

				$arImages = [];
				if($arPost["HAS_IMAGES"] !== "N")
				{
					$res = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>$arPost['ID'], "IS_COMMENT" => "N"));
					while ($arImage = $res->Fetch())
					{
						$bHasImg = true;
						$arImages[$arImage['ID']] = $arImage['FILE_ID'];

						$resizedImageData = CFile::ResizeImageGet(
							$arImage['FILE_ID'],
							[
								'width' => $arParams['ATTACHED_IMAGE_MAX_WIDTH_SMALL'],
								'height' => $arParams['ATTACHED_IMAGE_MAX_HEIGHT_SMALL'],
							],
							BX_RESIZE_IMAGE_EXACT,
							true
						);

						$resizedWidth = (int)$resizedImageData['width'];
						$resizedHeight = (int)$resizedImageData['height'];

						if (
							(int)$resizedImageData['width'] > $arParams['ATTACHED_IMAGE_MAX_WIDTH_SMALL']
							|| (int)$resizedImageData['height'] > $arParams['ATTACHED_IMAGE_MAX_HEIGHT_SMALL']
						)
						{
							if ((int)$resizedImageData['width'] > (int)$resizedImageData['height'])
							{
								$coeff = $resizedImageData['width'] / $arParams['ATTACHED_IMAGE_MAX_WIDTH_SMALL'];
								$resizedWidth = $arParams['ATTACHED_IMAGE_MAX_WIDTH_SMALL'];
								$resizedHeight = ($resizedImageData['height'] / $coeff);
							}
							else
							{
								$coeff = $resizedImageData['height'] / $arParams['ATTACHED_IMAGE_MAX_HEIGHT_SMALL'];
								$resizedHeight = $arParams['ATTACHED_IMAGE_MAX_HEIGHT_SMALL'];
								$resizedWidth = (int)($resizedImageData['width'] / $coeff);
							}
						}

						$arResult['images'][$arImage['ID']] = [
							'small' => '/bitrix/components/bitrix/blog/show_file.php?fid=' . $arImage['ID'] . '&width=' . $arParams['ATTACHED_IMAGE_MAX_WIDTH_SMALL'] . '&height=' . $arParams['ATTACHED_IMAGE_MAX_HEIGHT_SMALL'] . '&type=square',
							'full' => '/bitrix/components/bitrix/blog/show_file.php?fid=' . $arImage['ID'] . '&width=' . $arParams['ATTACHED_IMAGE_MAX_WIDTH_FULL'] . '&height=' . $arParams['ATTACHED_IMAGE_MAX_HEIGHT_FULL'],
							'resizedWidth' => $resizedWidth,
							'resizedHeight' => $resizedHeight,
						];
					}
				}

				$arParserParams = Array(
					"imageWidth" => $arParams["IMAGE_MAX_WIDTH"],
					"imageHeight" => $arParams["IMAGE_MAX_HEIGHT"],
					"pathToUser" => $arParams["PATH_TO_USER"],
					"ATTRIBUTES" => $arParams["ATTRIBUTES"] ?? null,
				);

				if ($p->bMobile)
				{
					$arParserParams["imageWidth"] = 275;
					$arParserParams["imageHeight"] = 416;
				}

				if (!empty($arParams["LOG_ID"]))
				{
					$arParserParams["pathToUserEntityType"] = 'LOG_ENTRY';
					$arParserParams["pathToUserEntityId"] = (int)$arParams["LOG_ID"];
				}

				$arAllow = array(
					"HTML" => "N",
					"ANCHOR" => "Y",
					"BIU" => "Y",
					"IMG" => "Y",
					"QUOTE" => "Y",
					"CODE" => "Y",
					"FONT" => "Y",
					"LIST" => "Y",
					"SMILES" => "Y",
					"NL2BR" => "N",
					"VIDEO" => "Y",
					"USER" => "Y",
					"TAG" => "Y",
					"SHORT_ANCHOR" => "Y"
				);
				if(COption::GetOptionString("blog","allow_video", "Y") !== "Y")
				{
					$arAllow["VIDEO"] = "N";
				}

				$arResult["Post"]["hasInlineDiskFile"] = false;

				if (
					isset($arResult["POST_PROPERTIES"]["DATA"])
					&& is_array($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_FILE"])
				)
				{
					$p->arUserfields = array("UF_BLOG_POST_FILE" => array_merge($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_FILE"], array("TAG" => "DOCUMENT ID")));

					if (!empty($arResult['POST_PROPERTIES']['DATA']['UF_BLOG_POST_FILE']['VALUE']))
					{
						$arResult["Post"]["hasInlineDiskFile"] = ComponentHelper::hasTextInlineImage((string)$arPost["~DETAIL_TEXT"], $arResult['POST_PROPERTIES']['DATA']['UF_BLOG_POST_FILE']);
					}
				}

				$p->LAZYLOAD = (isset($arParams["LAZYLOAD"]) && $arParams["LAZYLOAD"] === "Y" ? "Y" : "N");

				$detailText = (!empty($arPost['BACKGROUND_CODE']) ? $this->clearTextForColoredPost($arPost['~DETAIL_TEXT']) : $arPost['~DETAIL_TEXT']);
				$arResult['Post']['textFormated'] = $p->convert(
					$detailText,
					($arParams['USE_CUT'] === 'Y'),
					$arImages,
					$arAllow,
					$arParserParams
				);

				if ($arAllow["VIDEO"] === "Y")
				{
					$arResult["Post"]["hasVideoInline"] = preg_match(
						"/<video([^>]*)>(.+?)<\\/video[\\s]*>/isu",
						$arResult["Post"]["textFormated"]
					);
				}

				if (
					isset($arResult["POST_PROPERTIES"]["DATA"])
					&& is_array($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_FILE"])
					&& is_array($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_FILE"]["VALUE"])
				)
				{
					$arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_FILE"]["SOURCE_VALUE"] = $arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_FILE"]["VALUE"];
				}

				if (
					$arParams["USE_CUT"] === "Y"
					&& preg_match("/(\[CUT\])/i", $arPost['~DETAIL_TEXT'])
				)
				{
					$arResult["Post"]["CUT"] = "Y";
				}

				if(!empty($p->showedImages) && !empty($arResult["images"]))
				{
					foreach($p->showedImages as $val)
					{
						if(!empty($arResult["images"][$val]))
						{
							unset($arResult["images"][$val]);
						}
					}
				}
				$arResult["Post"]["DATE_PUBLISH_FORMATED"] = FormatDateFromDB($arResult["Post"]["DATE_PUBLISH"], $arParams["DATE_TIME_FORMAT"], true);
				$arResult["Post"]["DATE_PUBLISH_DATE"] = FormatDateFromDB($arResult["Post"]["DATE_PUBLISH"], FORMAT_DATE);
				if (strcasecmp(LANGUAGE_ID, 'EN') !== 0 && strcasecmp(LANGUAGE_ID, 'DE') !== 0)
				{
					$arResult["Post"]["DATE_PUBLISH_FORMATED"] = mb_strtolower($arResult["Post"]["DATE_PUBLISH_FORMATED"]);
					$arResult["Post"]["DATE_PUBLISH_DATE"] = mb_strtolower($arResult["Post"]["DATE_PUBLISH_DATE"]);
				}
				// strip current year
				if (!empty($arParams['DATE_TIME_FORMAT_S']) && ($arParams['DATE_TIME_FORMAT_S'] === 'j F Y G:i' || $arParams['DATE_TIME_FORMAT_S'] === 'j F Y g:i a'))
				{
					$arResult["Post"]["DATE_PUBLISH_FORMATED"] = ltrim($arResult["Post"]["DATE_PUBLISH_FORMATED"], '0');
					$arResult["Post"]["DATE_PUBLISH_DATE"] = ltrim($arResult["Post"]["DATE_PUBLISH_DATE"], '0');
					$curYear = date('Y');
					$arResult["Post"]["DATE_PUBLISH_FORMATED"] = str_replace(array('-'.$curYear, '/'.$curYear, ' '.$curYear, '.'.$curYear), '', $arResult["Post"]["DATE_PUBLISH_FORMATED"]);
					$arResult["Post"]["DATE_PUBLISH_DATE"] = str_replace(array('-'.$curYear, '/'.$curYear, ' '.$curYear, '.'.$curYear), '', $arResult["Post"]["DATE_PUBLISH_DATE"]);
				}
				$arResult["Post"]["DATE_PUBLISH_TIME"] = FormatDateFromDB(
					$arResult["Post"]["DATE_PUBLISH"],
					(
						mb_strpos($arParams["DATE_TIME_FORMAT_S"] ?? '', 'a') !== false
						|| (
							$arParams["DATE_TIME_FORMAT_S"] === 'FULL'
							&& IsAmPmMode()
						) !== false
							? (mb_strpos(FORMAT_DATETIME, 'TT') !== false ? 'G:MI TT': 'G:MI T')
							: 'GG:MI'
					)
				);
				if (strcasecmp(LANGUAGE_ID, 'EN') !== 0 && strcasecmp(LANGUAGE_ID, 'DE') !== 0)
				{
					$arResult["Post"]["DATE_PUBLISH_TIME"] = mb_strtolower($arResult["Post"]["DATE_PUBLISH_TIME"]);
				}
				$arResult["arUser"] = CBlogUser::GetUserInfo($arPost["AUTHOR_ID"], $arParams["PATH_TO_USER"], array("AVATAR_SIZE" => (isset($arParams["AVATAR_SIZE_COMMON"]) ? $arParams["AVATAR_SIZE_COMMON"] : $arParams["AVATAR_SIZE"]), "AVATAR_SIZE_COMMENT" => $arParams["AVATAR_SIZE_COMMENT"]));
				$arResult["arUser"]["isExtranet"] = (
					(int) $arPost["AUTHOR_ID"] > 0
					&& is_array($GLOBALS["arExtranetUserID"] ?? null)
					&& in_array($arPost["AUTHOR_ID"], $GLOBALS["arExtranetUserID"])
				);

				$arResult["arUser"]["isCollaber"] = false;
				if ($arResult["arUser"]["isExtranet"])
				{
					$arResult["arUser"]["isCollaber"] = User::isCollaber((int)$arPost["AUTHOR_ID"]);
				}

				if (!$arResult["bPublicPage"])
				{
					$arResult["arUser"]["url"] .= (
						mb_strpos($arResult["arUser"]["url"], '?') === false ? '?' : '&')
						."entityType=LOG_ENTRY&entityId=" . ($arParams["LOG_ID"] ?? null);
				}

				$arResult["Post"]["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("post_id"=> CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arPost["AUTHOR_ID"]));

				if ($arPost["CATEGORY_ID"] <> '')
				{
					$bHasTag = true;
					$arCategory = explode(",", $arPost["CATEGORY_ID"]);

					$blogCategoryList = [];
					$res = CBlogCategory::GetList([], [ '@ID' => $arCategory ]);
					while ($blogCategoryFields = $res->fetch())
					{
						$blogCategoryFields["~NAME"] = $blogCategoryFields["NAME"];
						$blogCategoryFields["NAME"] = htmlspecialcharsEx($blogCategoryFields["NAME"]);
						$blogCategoryFields["urlToCategory"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LOG_TAG"], array("tag" => urlencode($blogCategoryFields["NAME"])));
						$blogCategoryList[(int)$blogCategoryFields['ID']] = $blogCategoryFields;
					}

					$res = CBlogPostCategory::getList(
						[ 'ID' => 'ASC' ],
						[
							'@CATEGORY_ID' => $arCategory,
							'POST_ID' => $arPost['ID'],
						],
						false,
						false,
						[ 'CATEGORY_ID' ]);
					while ($blogPostCategoryFields = $res->fetch())
					{
						if (!isset($blogCategoryList[(int)$blogPostCategoryFields['CATEGORY_ID']]))
						{
							continue;
						}

						$arResult['Category'][] = $blogCategoryList[(int)$blogPostCategoryFields['CATEGORY_ID']];
					}
				}

				$SGClosedList = array();
				$bAll = false;
				$bCrmModuleIncluded = CModule::IncludeModule('crm');

				$arResult["Post"]["SPERM"] = [];
				if($arPost["HAS_SOCNET_ALL"] !== "Y")
				{
					$arSPERM = CBlogPost::GetSocnetPermsName($arResult["Post"]["ID"]);
					$arModuleEvents = array();
					$db_events = GetModuleEvents("socialnetwork", "OnSocNetLogFormatDestination");
					while ($arEvent = $db_events->Fetch())
					{
						$arModuleEvents[] = $arEvent;
					}

					$arUserId = array();

					foreach($arSPERM as $type => $v)
					{
						foreach($v as $vv)
						{
							$name = $link = $id = $CRMPrefix = "";
							$isExtranet = $isEmail = $isCollabEntity = false;

							if ($type === "SG")
							{
								if ($arSocNetGroup = CSocNetGroup::GetByID($vv["ENTITY_ID"]))
								{
									$SGClosedList[] = $arSocNetGroup["CLOSED"];

									$name = $arSocNetGroup["NAME"];
									$link = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $vv["ENTITY_ID"]));

									$groupSiteID = false;
									$rsGroupSite = CSocNetGroup::GetSite($vv["ENTITY_ID"]);

									while($arGroupSite = $rsGroupSite->Fetch())
									{
										if (
											!$arResult["bExtranetInstalled"]
											|| $arGroupSite["LID"] != CExtranet::GetExtranetSiteID()
										)
										{
											$groupSiteID = $arGroupSite["LID"];
											break;
										}
									}

									if ($groupSiteID)
									{
										$arTmp = CSocNetLogTools::ProcessPath(array("GROUP_URL" => $link), $user_id, $groupSiteID); // user_id is not important parameter
										$link = ($arTmp["URLS"]["GROUP_URL"] <> '' ? $arTmp["SERVER_NAME"].$arTmp["URLS"]["GROUP_URL"] : $link);
									}

									$isExtranet = (
										is_array($GLOBALS["arExtranetGroupID"] ?? null)
										&& in_array($vv["ENTITY_ID"], $GLOBALS["arExtranetGroupID"])
									);
									$isCollabEntity =
										$isExtranet
										&& CollabProvider::getInstance()->isCollab((int)$vv["ENTITY_ID"])
									;
									if (defined("BX_COMP_MANAGED_CACHE"))
									{
										$CACHE_MANAGER->RegisterTag("sonet_group_".(int)$vv["ENTITY_ID"]);
									}

									$entityType = 'project';
									$entityId = $vv['ENTITY_ID'];
								}
							}
							elseif ($type === "U")
							{
								if(in_array("US".$vv["ENTITY_ID"], $vv["ENTITY"]))
								{
									$name = "All";

									if (
										!$arResult["bExtranetSite"]
										&& defined("BITRIX24_PATH_COMPANY_STRUCTURE_VISUAL")
									)
									{
										$link = BITRIX24_PATH_COMPANY_STRUCTURE_VISUAL;
									}

									$bAll = true;

									$entityType = 'meta-user';
									$entityId = 'all-users';
								}
								else
								{
									$arTmpUser = array(
										"NAME" => $vv["~U_NAME"],
										"LAST_NAME" => $vv["~U_LAST_NAME"],
										"SECOND_NAME" => $vv["~U_SECOND_NAME"],
										"LOGIN" => $vv["~U_LOGIN"],
										"NAME_LIST_FORMATTED" => "",
									);
									$name = CUser::FormatName($arParams["NAME_TEMPLATE"], $arTmpUser, ($arParams["SHOW_LOGIN"] !== "N" ? true : false));
									$id = $vv["ENTITY_ID"];
									$link = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $vv["ENTITY_ID"]));
									$isExtranet = (
										is_array($GLOBALS["arExtranetUserID"] ?? null)
										&& in_array($vv["ENTITY_ID"], $GLOBALS["arExtranetUserID"])
									);
									$isCollabEntity = User::isCollaber((int)$id);
									$isEmail = (isset($vv["U_EXTERNAL_AUTH_ID"]) && $vv["U_EXTERNAL_AUTH_ID"] === 'email');
									if ($isEmail)
									{
										$link .= (mb_strpos($link, '?') === false ? '?' : '&')."entityType=LOG_ENTRY&entityId=".$arParams["LOG_ID"];
									}

									if (defined("BX_COMP_MANAGED_CACHE"))
									{
										$CACHE_MANAGER->RegisterTag("USER_NAME_".(int)$vv["ENTITY_ID"]);
									}
									$arUserId[] = $id;

									$entityType = 'user';
									$entityId = $vv['ENTITY_ID'];
								}
							}
							elseif($type === "DR")
							{
								$name = $vv["EL_NAME"];
								$id = $vv["ENTITY_ID"];
								$link = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_CONPANY_DEPARTMENT"], array("ID" => $vv["ENTITY_ID"]));
								$entityType = 'department';
								$entityId = $vv['ENTITY_ID'];
							}

							$arDestination = $arRights = array();
							$arDestinationParams = array(
								'MOBILE' => $arParams['MOBILE'] ?? '',
								'PATH_TO_CRMCONTACT' => (!empty($arParams['PATH_TO_CRMCONTACT']) ? $arParams['PATH_TO_CRMCONTACT'] : ''),
							);
							foreach ($arModuleEvents as $arEvent)
							{
								ExecuteModuleEventEx($arEvent, array(&$arDestination, $vv['~ENTITY'], $arRights, $arDestinationParams, true));
							}

							if (
								!empty($arDestination)
								&& !empty($arDestination[0])
							)
							{
								$name = $arDestination[0]['TITLE'];
								$link = $arDestination[0]['URL'];
								$id = $arDestination[0]['ID'];
								$isExtranet = false;
								$isEmail = false;
								$isCollabEntity = false;
								$CRMPrefix = $arDestination[0]['CRM_PREFIX'];
							}

							if($name <> '')
							{
								$arResult["Post"]["SPERM"][$type][$vv["ENTITY_ID"]] = array(
									"NAME" => $name,
									"URL" => $link,
									"ID" => $id,
									"IS_EXTRANET" => ($isExtranet ? "Y" : "N"),
									"IS_EMAIL" => ($isEmail ? "Y" : "N"),
									"IS_COLLAB" => ($isCollabEntity ? "Y" : "N"),
									"CRM_PREFIX" => $CRMPrefix,
									'entityType' => $entityType,
									'entityId' => $entityId,
								);
							}
						}
					}

					if (
						!empty($arUserId)
						&& IsModuleInstalled('crm')
					)
					{
						$res = \Bitrix\Main\UserTable::getList([
							'filter' => [
								'@ID' => $arUserId,
							],
							'order' => [
								'ID'=> 'ASC',
							],
							'select' => [ 'ID', 'UF_USER_CRM_ENTITY' ],
						]);

						while ($arUser = $res->fetch())
						{
							if (
								!empty($arUser["UF_USER_CRM_ENTITY"])
								&& isset($arResult["Post"]["SPERM"]["U"][$arUser["ID"]])
							)
							{
								$arResult["Post"]["SPERM"]["U"][$arUser["ID"]]["CRM_ENTITY"] = $arUser["UF_USER_CRM_ENTITY"];
							}
						}
					}

					if (
						count($arResult["Post"]["SPERM"]) == 1
						&& isset($arResult["Post"]["SPERM"]["U"])
						&& is_array($arResult["Post"]["SPERM"]["U"])
						&& count($arResult["Post"]["SPERM"]["U"]) == 1
						&& $bAll
					)
					{
						$bHasOnlyAll = true;
					}
				}
				else
				{
					$arResult["Post"]["SPERM"]["U"][1] = Array(
						"NAME" => "All",
						"URL" => (
							!$arResult["bExtranetSite"]
							&& defined("BITRIX24_PATH_COMPANY_STRUCTURE_VISUAL")
								? BITRIX24_PATH_COMPANY_STRUCTURE_VISUAL
								: ""
						),
						"ID" => "",
						'entityType' => 'meta-user',
						'entityId' => 'all-users',
					);
				}

				$arResult["Post"]["LIMITED_VIEW"] = ComponentHelper::getBlogPostLimitedViewStatus(array(
					'logId' => (int) ($arParams["LOG_ID"] ?? null),
					'postId' => (int) $arResult["Post"]["ID"],
					'authorId' => $arResult["Post"]["AUTHOR_ID"],
					'blogPostPerms' => $arResult["Post"]["SPERM"]
				));

				$arResult["Post"]["ONLY_CLOSED_GROUPS"] = (!empty($SGClosedList) && !in_array('N', $SGClosedList));

				$arFieldsHave = array();
				if($arPost["HAS_IMAGES"] == "")
					$arFieldsHave["HAS_IMAGES"] = ($bHasImg ? "Y" : "N");
				if($arPost["HAS_TAGS"] == "")
					$arFieldsHave["HAS_TAGS"] = ($bHasTag ? "Y" : "N");
				if($arPost["HAS_PROPS"] == "")
					$arFieldsHave["HAS_PROPS"] = ($bHasProps ? "Y" : "N");
				if($arPost["HAS_SOCNET_ALL"] == "")
					$arFieldsHave["HAS_SOCNET_ALL"] = ($bHasOnlyAll ? "Y" : "N");

				if (!empty($arFieldsHave))
				{
					CBlogPost::Update($arPost["ID"], $arFieldsHave);
				}

				if (
					$bAll
					|| $arPost["HAS_SOCNET_ALL"] === "Y"
				)
				{
					$arResult["Post"]["HAVE_ALL_IN_ADR"] = "Y";
				}

				if ($arParams["CACHE_TIME"] > 0)
				{
					$arCacheData = Array(
						"Post" => $arResult["Post"],
						"images" => $arResult["images"] ?? [],
						"Category" => $arResult["Category"] ?? [],
						"GRATITUDE" => $arResult["GRATITUDE"] ?? [],
						"POST_PROPERTIES" => $arResult["POST_PROPERTIES"],
						"arUser" => $arResult["arUser"],
						"Assets" => (isset($arResult["Assets"]) ? $arResult["Assets"] : array()),
					);
					if(defined("BX_COMP_MANAGED_CACHE"))
					{
						$CACHE_MANAGER->EndTagCache();
					}
					$cache->EndDataCache($arCacheData);
				}
			}

			if (
				!isset($arResult['URL_PREVIEW'])
				&& isset($arResult['POST_PROPERTIES']['DATA']['UF_BLOG_POST_URL_PRV'])
			)
			{
				$this->processUrlPreview([
					'fieldData' => $arResult['POST_PROPERTIES']['DATA']['UF_BLOG_POST_URL_PRV'],
					'postId' => $arPost['ID'],
				]);

				unset($arResult['POST_PROPERTIES']['DATA']['UF_BLOG_POST_URL_PRV']);
			}

			$arResult["arUser"]["urlToPostImportant"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST_IMPORTANT"], array("user_id"=> $arPost["AUTHOR_ID"]));

			$arResult["dest_users"] = array();
			foreach ($arResult["Post"]["SPERM"] as $key => $value)
			{
				foreach($value as $kk => $vv)
				{
					$arResult["PostSrc"]["SPERM"][$key][] = $kk;
					if($key === "U")
					{
						$arResult["dest_users"][] = $kk;
					}
				}
			}
			$arResult["PostSrc"]["HAVE_ALL_IN_ADR"] = ($arResult["Post"]["HAVE_ALL_IN_ADR"] ?? '');

			if (
				$arParams["CHECK_PERMISSIONS_DEST"] === "N"
				&& !CSocNetUser::IsCurrentUserModuleAdmin()
				&& is_object($USER)
			)
			{
				$arResult["Post"]["SPERM_HIDDEN"] = 0;
				$arGroupID = CSocNetLogTools::GetAvailableGroups(
					($arResult["bExtranetUser"] && !$arResult["bPublicPage"] ? "Y" : "N"),
					($arResult["bExtranetSite"] ? "Y" : "N")
				);

				if (
					!$arResult["bExtranetUser"]
					&& CModule::IncludeModule("extranet")
				)
				{
					$arAvailableExtranetUserID = CExtranet::GetMyGroupsUsersSimple(CExtranet::GetExtranetSiteID());
				}

				foreach($arResult["Post"]["SPERM"] as $group_code => $arBlogSPerm)
				{
					foreach($arBlogSPerm as $entity_id => $arBlogSPermDesc)
					{
						if (
							(
								$group_code === "SG"
								&& !in_array($entity_id, $arGroupID)
							)
							|| (
								$group_code === "DR"
								&& $arResult["bExtranetUser"]
								&& !$arResult["bPublicPage"]
							)
							|| (
								$group_code === "U"
								&& isset($arUserIdVisible) // current extranet user
								&& is_array($arUserIdVisible)
								&& !in_array($entity_id, $arUserIdVisible)
							)
							|| (
								$group_code === "U"
								&& isset($arBlogSPermDesc["IS_EXTRANET"])
								&& $arBlogSPermDesc["IS_EXTRANET"] === "Y"
								&& (
									$arResult["bPublicPage"]
									|| (
										isset($arAvailableExtranetUserID)
										&& is_array($arAvailableExtranetUserID)
										&& !in_array($entity_id, $arAvailableExtranetUserID)
									)
								)
							)
						)
						{
							unset($arResult["Post"]["SPERM"][$group_code][$entity_id]);
							$arResult["Post"]["SPERM_HIDDEN"]++;
							$arResult["PostSrc"]["SPERM_HIDDEN"][] = $group_code.$entity_id;
						}
					}
				}
			}

			$arResult["CommentPerm"] = Permissions::WRITE;

			if (
				(
					$arParams["CHECK_COMMENTS_PERMS"] === "Y"
					|| !$arResult["bFromList"]
				)
				&& !CSocNetUser::IsCurrentUserModuleAdmin()
				&& is_object($USER)
				&& $arResult['USER_ID'] !== (int)$arResult["Post"]["AUTHOR_ID"]
				&& !empty($arResult["Post"]["SPERM"]['SG']) // if has sonet groups
				&& count($arResult["Post"]["SPERM"]) === 1 // and only sonet groups
			)
			{
				$arResult["CommentPerm"] = CBlogComment::GetSocNetUserPerms($arResult["Post"]["ID"], $arResult["Post"]["AUTHOR_ID"]);
				$arResult["ReadOnly"] = (
					$arResult["ReadOnly"]
					&& $arResult["CommentPerm"] < Permissions::PREMODERATE
				);
			}

			$arResult["CanComment"] = (
				!isset($arResult["Post"]["ONLY_CLOSED_GROUPS"])
				|| !$arResult["Post"]["ONLY_CLOSED_GROUPS"]
				|| COption::GetOptionString("socialnetwork", "work_with_closed_groups", "N") === "Y"
			);

			if (!empty($arParams['SONET_GROUP_ID']))
			{
				if (
					$arResult["CanComment"]
					&& $arResult["ReadOnly"]
				)
				{
					$arResult["CanComment"] = \Bitrix\Socialnetwork\ComponentHelper::checkCanCommentInWorkgroup([
						'userId' => $arResult["USER_ID"],
						'workgroupId' => $arParams['SONET_GROUP_ID']
					]);
				}
			}
			else
			{
				$arResult["CanComment"] = (
					$arResult["CanComment"]
					&& !$arResult["ReadOnly"]
				);
			}

			$arResult["PostSrc"]["SPERM_NAME"] = $arResult["Post"]["SPERM"];

			if(
				$arResult["PostPerm"] > Permissions::MODERATE
				|| (
					$arResult["PostPerm"] >= Permissions::WRITE
					&& $arPost["AUTHOR_ID"] == $arResult["USER_ID"]
				)
			)
			{
				$arResult["urlToEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST_EDIT"], array("post_id"=>$arPost["ID"], "user_id" => $arPost["AUTHOR_ID"]));
				if (
					isset($arParams["TYPE"])
					&& in_array($arParams["TYPE"], ["DRAFT", "MODERATION"])
				)
				{
					$arResult["Post"]["urlToPost"] = $arResult["urlToEdit"];
				}
			}

			$arResult["urlToDelete"] = $arResult["urlToEdit"] ?? '';
			$arResult["urlToDelete"] .= (mb_strpos($arResult["urlToDelete"], "?") === false ? "?" : "&");
			$arResult["urlToDelete"] .= "delete_blog_post_id=#del_post_id#&ajax_blog_post_delete=Y"."&".bitrix_sessid_get();

			if(($arParams["FROM_LOG"] ?? null) !== "Y")
			{
				if($arResult["PostPerm"] >= Permissions::MODERATE)
				{
					$arResult["urlToHide"] = htmlspecialcharsex($APPLICATION->GetCurPageParam("hide=Y"."&".bitrix_sessid_get(), Array("sessid", "success", "hide", "delete")));
				}

				if($arResult["PostPerm"] >= Permissions::FULL)
				{
					if(!in_array($arParams["TYPE"], array("DRAFT", "MODERATION")))
					{
						$arResult["urlToDelete"] = htmlspecialcharsex($APPLICATION->GetCurPageParam("delete=Y"."&".bitrix_sessid_get(), Array("sessid", "delete", "hide", "success")));
					}
					$arResult["canDelete"] = "Y";
				}
			}
			elseif($arResult["PostPerm"] >= Permissions::FULL)
			{
				$arResult["canDelete"] = "Y";
			}

			if(
				$arParams["SHOW_RATING"] === "Y"
				&& !empty($arResult["Post"])
			)
			{
				$arResult['RATING'][$arResult["Post"]["ID"]] = (
						array_key_exists("RATING_ENTITY_ID", $arParams)
						&& (int)$arParams["RATING_ENTITY_ID"] > 0
						&& array_key_exists("RATING_TOTAL_VALUE", $arParams)
						&& is_numeric($arParams["RATING_TOTAL_VALUE"])
						&& array_key_exists("RATING_TOTAL_VOTES", $arParams)
						&& (int)$arParams["RATING_TOTAL_VOTES"] >= 0
						&& array_key_exists("RATING_TOTAL_POSITIVE_VOTES", $arParams)
						&& (int)$arParams["RATING_TOTAL_POSITIVE_VOTES"] >= 0
						&& array_key_exists("RATING_TOTAL_NEGATIVE_VOTES", $arParams)
						&& (int)$arParams["RATING_TOTAL_NEGATIVE_VOTES"] >= 0
						&& array_key_exists("RATING_USER_VOTE_VALUE", $arParams)
						&& is_numeric($arParams["RATING_USER_VOTE_VALUE"])
							? array(
								"USER_VOTE" => $arParams["RATING_USER_VOTE_VALUE"],
								"USER_HAS_VOTED" => ($arParams["RATING_USER_VOTE_VALUE"] == 0 ? "N" : "Y"),
								"TOTAL_VOTES" => $arParams["RATING_TOTAL_VOTES"],
								"TOTAL_POSITIVE_VOTES" => $arParams["RATING_TOTAL_POSITIVE_VOTES"],
								"TOTAL_NEGATIVE_VOTES" => $arParams["RATING_TOTAL_NEGATIVE_VOTES"],
								"TOTAL_VALUE" => $arParams["RATING_TOTAL_VALUE"]
							)
							: CRatings::GetRatingVoteResult('BLOG_POST', $arResult["Post"]["ID"])
					);
			}

			if ($arParams["IS_UNREAD"] ?? false)
				$arResult["Post"]["new"] = "Y";

			if ($arParams["IS_HIDDEN"] ?? false)
				$arResult["Post"]["hidden"] = "Y";

			$arResult["Post"]["IS_IMPORTANT"] = false;
			if (
				isset($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_IMPRTNT"])
				&& is_array($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_IMPRTNT"])
				&& (int)$arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_IMPRTNT"]["VALUE"] > 0
			)
			{
				$arResult["Post"]["IS_IMPORTANT"] = true;
				unset($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_IMPRTNT"]);
				$arResult["Post"]["IMPORTANT"] = array();
				if ($USER->IsAuthorized())
				{
					$arResult["Post"]["IMPORTANT"] = array(
						"COUNT" => 0,
						"IS_READ" => false,
						"USER" => array()
					);

					$cache = new CPHPCache;
					$cache_path = ComponentHelper::getBlogPostCacheDir(array(
						'TYPE' => 'post',
						'POST_ID' => $arPost["ID"]
					));
					$cache_id = "blog_socnet_post_read_" . $arResult['USER_ID'];

					if ($cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
						$arResult["Post"]["IMPORTANT"] = $cache->GetVars();
					else
					{
						$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
						if (defined("BX_COMP_MANAGED_CACHE"))
						{
							$CACHE_MANAGER->StartTagCache($cache_path);
							$CACHE_MANAGER->RegisterTag("BLOG_POST_IMPRTNT".$arPost["ID"]);
						}
						$db_user = CUser::GetById($arResult['USER_ID']);
						$arResult["Post"]["IMPORTANT"]["USER"] = $db_user->Fetch();

						$db_res = CBlogUserOptions::GetList(
							array(
								"ID" => "ASC"
							),
							array(
								"POST_ID" => $arResult["Post"]["ID"],
								"NAME" => "BLOG_POST_IMPRTNT",
								"VALUE" => "Y",
								"USER_ACTIVE" => "Y"
							),
							array(
								"bCount" => true
							)
						);
						if ($db_res && ($res = $db_res->Fetch()) && $res["CNT"] > 0)
						{
							$arResult["Post"]["IMPORTANT"]["COUNT"] = $res["CNT"];
							$arResult["Post"]["IMPORTANT"]["IS_READ"] = CBlogUserOptions::GetOption(
								$arPost["ID"],
								"BLOG_POST_IMPRTNT",
								"N",
								$arResult['USER_ID']
							);
						}

						if(defined("BX_COMP_MANAGED_CACHE"))
							$CACHE_MANAGER->EndTagCache();
						$cache->EndDataCache($arResult["Post"]["IMPORTANT"]);
					}
				}
			}

			if (
				isset($arResult["GRATITUDE"])
				&& isset($arResult["GRATITUDE"]["USERS_FULL"])
				&& is_array($arResult["GRATITUDE"]["USERS_FULL"])
				&& !empty($arResult["GRATITUDE"]["USERS_FULL"])
				&& isset($arUserIdVisible)
				&& is_array($arUserIdVisible)
			)
			{
				foreach($arResult["GRATITUDE"]["USERS_FULL"] as $key => $arUserTmp)
				{
					if (!in_array($arUserTmp["ID"], $arUserIdVisible))
					{
						$arResult["GRATITUDE"]["USERS_FULL"][$key]["URL"] = false;
					}
				}
			}

			$arResult["CONTENT_ID"] = (!empty($arParams["CONTENT_ID"]) ? $arParams["CONTENT_ID"] : 'BLOG_POST-'.(int)$arResult["Post"]["ID"]);
			if (isset($arParams["CONTENT_VIEW_CNT"]))
			{
				$arResult["CONTENT_VIEW_CNT"] = (int)$arParams["CONTENT_VIEW_CNT"];
			}
			else
			{
				if (
					($contentViewData = \Bitrix\Socialnetwork\Item\UserContentView::getViewData(array(
						'contentId' => array($arResult["CONTENT_ID"])
					)))
					&& !empty($contentViewData[$arResult["CONTENT_ID"]])
				)
				{
					$arResult["CONTENT_VIEW_CNT"] = (int)$contentViewData[$arResult["CONTENT_ID"]]["CNT"];
				}
				else
				{
					$arResult["CONTENT_VIEW_CNT"] = 0;
				}
			}

			$arResult['LOG_ID_TOKEN'] = (
				($arParams['LOG_ID'] ?? 0) > 0
				&& $arResult['USER_ID'] > 0
					? (new \Bitrix\Main\Engine\ActionFilter\Service\Token($arResult['USER_ID']))->generate($arParams['LOG_ID'])
					: ''
			);
		}
		else
		{
			$arResult["FATAL_MESSAGE"] = GetMessage("B_B_MES_NO_RIGHTS")."<br />";
			$arResult["FATAL_CODE"] = "NO_RIGHTS";
		}
	}
	elseif (!$arResult["bFromList"])
	{
		$arResult["FATAL_MESSAGE"] = GetMessage("B_B_MES_NO_POST");
		$arResult["FATAL_CODE"] = "NO_POST";
		CHTTP::SetStatus("404 Not Found");
	}
}
else
{
	$arResult["FATAL_MESSAGE"] = GetMessage("B_B_MES_NO_BLOG");
	$arResult["FATAL_CODE"] = "NO_BLOG";
	CHTTP::SetStatus("404 Not Found");
}

include_once('destination.php');

if (
	!isset($arParams["RETURN_ERROR"])
	|| $arParams["RETURN_ERROR"] !== "Y"
	|| empty($arResult["FATAL_MESSAGE"])
)
{
	if (
		isset($arParams["SITE_TEMPLATE_ID"])
		&& $arParams["SITE_TEMPLATE_ID"] <> ''
	)
	{
		$this->setSiteTemplateId($arParams["SITE_TEMPLATE_ID"]);
	}

	$arResult['CONTENT_VIEW_KEY_SIGNED'] = (string)($arParams['CONTENT_VIEW_KEY_SIGNED'] ?? (
		(is_object($USER) && $USER->isAuthorized())
		&& (string) ($arResult['CONTENT_ID'] ?? null) !== ''
			? (new Token((int)$USER->getId()))->generate($arResult['CONTENT_ID'])
			: ''
	));

	$this->IncludeComponentTemplate();
}

if (($arParams["RETURN_DATA"] ?? '') === "Y")
{
	return array(
		"BLOG_DATA" => $arResult["Blog"],
		"POST_DATA" => $arResult["PostSrc"],
		"ERROR" => (
			!empty($arResult["FATAL_MESSAGE"])
				? $arResult["FATAL_MESSAGE"]
				: false
		),
		"ERROR_CODE" => (
			!empty($arResult["FATAL_CODE"])
				? $arResult["FATAL_CODE"]
				: false
		)
	);
}
