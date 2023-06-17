<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

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

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!Loader::includeModule("blog"))
{
	ShowError(Loc::getMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}
if (!Loader::includeModule("socialnetwork"))
{
	ShowError(Loc::getMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["BLOG_VAR"] = $arParams["BLOG_VAR"] ?? '';
$arParams["PAGE_VAR"] = $arParams["PAGE_VAR"] ?? '';
$arParams["USER_VAR"] = $arParams["USER_VAR"] ?? '';
$arParams["POST_VAR"] = $arParams["POST_VAR"] ?? '';

if($arParams["BLOG_VAR"] == '')
{
	$arParams["BLOG_VAR"] = "blog";
}
if($arParams["PAGE_VAR"] == '')
{
	$arParams["PAGE_VAR"] = "page";
}
if($arParams["POST_VAR"] == '')
{
	$arParams["POST_VAR"] = "id";
}
if($arParams["USER_VAR"] == '')
{
	$arParams["USER_VAR"] = "id";
}

$arParams["PATH_TO_DRAFT"] = trim($arParams["PATH_TO_DRAFT"]);
if($arParams["PATH_TO_DRAFT"] == '')
{
	$arParams["PATH_TO_DRAFT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=draft&".$arParams["BLOG_VAR"]."=#blog#");
}

$arParams["PATH_TO_POST_EDIT"] = trim($arParams["PATH_TO_POST_EDIT"]);
if($arParams["PATH_TO_POST_EDIT"] == '')
{
	$arParams["PATH_TO_POST_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post_edit&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");
}

$arParams["PATH_TO_MODERATION"] = trim($arParams["PATH_TO_MODERATION"]);
if($arParams["PATH_TO_MODERATION"] == '')
{
	$arParams["PATH_TO_MODERATION"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=moderation&".$arParams["BLOG_VAR"]."=#blog#");
}

$arParams["PATH_TO_TAGS"] = trim($arParams["PATH_TO_TAGS"] ?? '');
if($arParams["PATH_TO_TAGS"] == '')
{
	$arParams["PATH_TO_TAGS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=tags&".$arParams["BLOG_VAR"]."=#blog#");
}

$arParams["USER_ID"] = (int)$arParams["USER_ID"];
$userId = (int)$USER->GetID();
$arParams["SOCNET_GROUP_ID"] = (int) ($arParams["SOCNET_GROUP_ID"] ?? 0);

$groupMode = ($arParams["SOCNET_GROUP_ID"] > 0);

if(!is_array($arParams["GROUP_ID"]))
{
	$arParams["GROUP_ID"] = [ $arParams["GROUP_ID"] ];
}
$arParams["GROUP_ID"] = array_filter($arParams["GROUP_ID"], function($v) { return $v > 0; });

$currentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();
$arResult["PostPerm"] = BLOG_PERMS_DENY;

if (($arParams["SET_TITLE"] ?? '') !== "N")
{
	$APPLICATION->SetTitle(Loc::getMessage("BM_BLOG_POST"));
}

if ($arParams["USER_ID"] > 0)
{
	$res = CUser::GetByID($arParams["USER_ID"]);
	$userFields = $res->Fetch();
	if (
		!empty($userFields)
		&& ($arParams["SET_TITLE"] ?? null) !== "N"
		&& CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "blog")
	)
	{
		if ($arParams["NAME_TEMPLATE"] == '')
		{
			$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
		}

		$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
			[ "#NOBR#", "#/NOBR#" ],
			"",
			$arParams["NAME_TEMPLATE"]
		);

		$strTitleFormatted = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $userFields, ($arParams['SHOW_LOGIN'] !== "N"));

		if($arParams["USER_ID"] === $userId)
		{
			$APPLICATION->SetTitle(Loc::getMessage("BM_BLOG_POST"));
		}
		else
		{
			if ($arParams["HIDE_OWNER_IN_TITLE"] === "Y")
			{
				$APPLICATION->SetPageProperty("title", $strTitleFormatted.": ".Loc::getMessage("BM_BLOG_POST"));
				$APPLICATION->SetTitle(Loc::getMessage("BM_BLOG_POST"));
			}
			else
			{
				$APPLICATION->SetTitle($strTitleFormatted.": ".Loc::getMessage("BM_BLOG_POST"));
			}
		}
	}
}

if(!(
	$groupMode
	|| (
		$userId > 0
		&& (
			$arParams["USER_ID"] === $userId
			|| $currentUserIsAdmin
			|| CMain::getGroupRight("blog") >= "W"
		)
	)
))
{
	return;
}

if($groupMode)
{
	$arGroup = CSocNetGroup::GetByID($arParams["SOCNET_GROUP_ID"]);
	if(
		!empty($arGroup)
		&& $groupMode
		&& CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog")
	)
	{
		if (CSocNetFeaturesPerms::CanPerformOperation($userId, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "full_post", $currentUserIsAdmin) || CMain::GetGroupRight("blog") >= "W")
		{
			$arResult["PostPerm"] = BLOG_PERMS_FULL;
		}
		elseif (CSocNetFeaturesPerms::CanPerformOperation($userId, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "moderate_post", $currentUserIsAdmin))
		{
			$arResult["PostPerm"] = BLOG_PERMS_MODERATE;
		}

		if (($arParams["SET_TITLE"] ?? '') !== "N")
		{
			$APPLICATION->SetTitle(Loc::getMessage("BM_BLOG_POST"));
		}

		$arResult["PATH_TO_4ME_ALL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], [
			"user_id" => $arParams["USER_ID"],
			"group_id" => $arParams["SOCNET_GROUP_ID"]
		]);
	}
}
elseif(
	$arParams["USER_ID"] > 0
	&& !empty($userFields)
	&& CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "blog")
)
{
	$arResult["showAll"] = "Y";
	$arResult["PostPerm"] = BLOG_PERMS_FULL;
	$arResult["PATH_TO_4ME_ALL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], [
		"user_id" => $arParams["USER_ID"]
	]);

	if ($arParams["USER_ID"] === $userId)
	{
		$arResult["PATH_TO_4ME"] = $arResult["PATH_TO_MINE"] = $arResult["PATH_TO_4ME_ALL"];
		$arResult["PATH_TO_4ME"] .= (mb_strpos($arResult["PATH_TO_4ME"], "?") === false ? "?" : "&");

		$arResult["PATH_TO_MINE"] = $arResult["PATH_TO_4ME"]."mine=Y";
		$arResult["PATH_TO_4ME"] .= "forme=Y";
	}

	$arResult["forme"] = $_REQUEST["forme"] ?? null;

	if (($_REQUEST["forme"] ?? null) == '')
	{
		$arResult["forme"] = "ALL";
	}

	if (($_REQUEST["mine"] ?? null) === "Y")
	{
		$arResult["forme"] = "";
	}


	$arResult["show4MeAll"] = ($arParams["USER_ID"] === $userId ? "Y" : "N");
	$arResult["show4Me"] = ($arParams["USER_ID"] === $userId ? "Y" : "N");
}

if($arResult["PostPerm"] >= BLOG_PERMS_WRITE)
{
	if(!$groupMode)
	{
		if ($arParams["USER_ID"] === $userId)
		{
			$arResult["urlToDraft"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_DRAFT"], [
				"user_id" => $arParams["USER_ID"]
			]);
			$res = CBlogPost::GetList(
				[],
				[
					"AUTHOR_ID" => $userId,
					"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_DRAFT,
					"BLOG_USE_SOCNET" => "Y",
					"GROUP_ID" => $arParams["GROUP_ID"],
					"GROUP_SITE_ID" => SITE_ID,
				],
				[ "COUNT" => "ID" ],
				false,
				[ "ID" ]
			);
			if($posts = $res->Fetch())
			{
				$arResult["CntToDraft"] = $posts["ID"];
			}
		}

		$arResult["urlToModeration"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MODERATION"], [
			"user_id" => $arParams["USER_ID"]
		]);
		$res = CBlogPost::GetList(
			[],
			[
				"AUTHOR_ID" => $arParams["USER_ID"],
				"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_READY,
				"BLOG_USE_SOCNET" => "Y",
				"GROUP_ID" => $arParams["GROUP_ID"],
				"GROUP_SITE_ID" => SITE_ID,
			],
			[ "COUNT" => "ID" ],
			false,
			[ "ID" ]
		);
		if($posts = $res->Fetch())
		{
			$arResult["CntToModerate"] = $posts["ID"];
		}

		$arResult["urlToTags"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TAGS"], [
			"user_id" => $arParams["USER_ID"]
		]);

		$arBlog = \Bitrix\Blog\Item\Blog::getByUser([
			"GROUP_ID" => $arParams["GROUP_ID"],
			"SITE_ID" => SITE_ID,
			"USER_ID" => $arParams["USER_ID"],
			"USE_SOCNET" => "Y"
		]);

		if ($arBlog)
		{
			$res = CBlogCategory::getList(
				[],
				[
					"BLOG_ID" => $arBlog["ID"]
				],
				[ "COUNT" => "ID" ],
				false,
				[ "ID" ]
			);
			if ($tags = $res->Fetch())
			{
				$arResult["CntTags"] = $tags["ID"];
			}
		}
	}
	else
	{
		if($arResult["PostPerm"] >= BLOG_PERMS_MODERATE)
		{
			$arResult["urlToModeration"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MODERATION"], [
				"group_id" => $arParams["SOCNET_GROUP_ID"]
			]);
			$res = CBlogPost::GetList(
				[],
				[
					"SOCNET_GROUP_ID" => $arParams["SOCNET_GROUP_ID"],
					"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_READY,
					"BLOG_USE_SOCNET" => "Y",
					"GROUP_ID" => $arParams["GROUP_ID"],
					"GROUP_SITE_ID" => SITE_ID,
				],
				[ "COUNT" => "ID", "SOCNET_GROUP_ID" ],
				false,
				[ "ID", "SOCNET_GROUP_ID" ]
			);
			if($posts = $res->Fetch())
			{
				$arResult["CntToModerate"] = $posts["CNT"];
				$arResult["showAll"] = "Y";
			}
		}
	}
}

$arResult["page"] = "all";
if (($arParams["CURRENT_PAGE"] ?? null) === "moderation")
{
	$arResult["page"] = "moderation";
}
elseif(($arParams["CURRENT_PAGE"] ?? null) === "draft")
{
	$arResult["page"] = "draft";
}
elseif(($arParams["CURRENT_PAGE"] ?? null) === "tags")
{
	$arResult["page"] = "tags";
}
elseif(($_REQUEST["mine"] ?? null) === "Y")
{
	$arResult["page"] = "mine";
}
elseif(($arResult["forme"] ?? null) === "Y")
{
	$arResult["page"] = "forme";
}

$this->IncludeComponentTemplate();
?>