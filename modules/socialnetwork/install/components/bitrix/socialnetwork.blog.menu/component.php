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

use \Bitrix\Blog\Item\Permissions;

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}
if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["POST_VAR"])<=0)
	$arParams["POST_VAR"] = "id";
if(strLen($arParams["USER_VAR"])<=0)
	$arParams["USER_VAR"] = "id";

$arParams["PATH_TO_DRAFT"] = trim($arParams["PATH_TO_DRAFT"]);
if(strlen($arParams["PATH_TO_DRAFT"])<=0)
	$arParams["PATH_TO_DRAFT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=draft&".$arParams["BLOG_VAR"]."=#blog#");

$arParams["PATH_TO_POST_EDIT"] = trim($arParams["PATH_TO_POST_EDIT"]);
if(strlen($arParams["PATH_TO_POST_EDIT"]) <= 0)
{
	$arParams["PATH_TO_POST_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post_edit&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");
}

$arParams["PATH_TO_MODERATION"] = trim($arParams["PATH_TO_MODERATION"]);
if(strlen($arParams["PATH_TO_MODERATION"]) <= 0)
{
	$arParams["PATH_TO_MODERATION"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=moderation&".$arParams["BLOG_VAR"]."=#blog#");
}

$arParams["PATH_TO_TAGS"] = trim($arParams["PATH_TO_TAGS"]);
if(strlen($arParams["PATH_TO_TAGS"]) <= 0)
{
	$arParams["PATH_TO_TAGS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=tags&".$arParams["BLOG_VAR"]."=#blog#");
}

$arParams["USER_ID"] = IntVal($arParams["USER_ID"]);

$user_id = IntVal($USER->GetID());
$arParams["SOCNET_GROUP_ID"] = IntVal($arParams["SOCNET_GROUP_ID"]);

$bGroupMode = false;
if(IntVal($arParams["SOCNET_GROUP_ID"]) > 0)
{
	$bGroupMode = true;
}
if(!is_array($arParams["GROUP_ID"]))
{
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
}
foreach($arParams["GROUP_ID"] as $k=>$v)
{
	if(IntVal($v) <= 0)
	{
		unset($arParams["GROUP_ID"][$k]);
	}
}

$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();
$arResult["PostPerm"] = BLOG_PERMS_DENY;

if($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(GetMessage("BM_BLOG_POST"));
if(IntVal($arParams["USER_ID"]) > 0)
{
	$dbUser = CUser::GetByID($arParams["USER_ID"]);
	$arUser = $dbUser->Fetch();
	if (
		!empty($arUser)
		&& $arParams["SET_TITLE"] != "N"
		&& CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "blog")
	)
	{
		if (strlen($arParams["NAME_TEMPLATE"]) <= 0)
		{
			$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
		}

		$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
			array("#NOBR#", "#/NOBR#"),
			array("", ""),
			$arParams["NAME_TEMPLATE"]
		);

		$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;
		$strTitleFormatted = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arUser, $bUseLogin);

		if($arParams["USER_ID"] == $user_id)
		{
			$APPLICATION->SetTitle(GetMessage("BM_BLOG_POST"));
		}
		else
		{
			if ($arParams["HIDE_OWNER_IN_TITLE"] == "Y")
			{
				$APPLICATION->SetPageProperty("title", $strTitleFormatted.": ".GetMessage("BM_BLOG_POST"));
				$APPLICATION->SetTitle(GetMessage("BM_BLOG_POST"));
			}
			else
			{
				$APPLICATION->SetTitle($strTitleFormatted.": ".GetMessage("BM_BLOG_POST"));
			}
		}
	}
}

if(!(
	$bGroupMode
	|| (
		$user_id > 0
		&& (
			IntVal($arParams["USER_ID"]) == $user_id
			|| CSocNetUser::isCurrentUserModuleAdmin()
			|| $APPLICATION->getGroupRight("blog") >= "W"
		)
	)
))
{

	return;
}

if($bGroupMode)
{
	$arGroup = CSocNetGroup::GetByID($arParams["SOCNET_GROUP_ID"]);
	if(
		!empty($arGroup)
		&& $bGroupMode
		&& CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog")
	)
	{
		if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "full_post", $bCurrentUserIsAdmin) || $APPLICATION->GetGroupRight("blog") >= "W")
		{
			$arResult["PostPerm"] = BLOG_PERMS_FULL;
		}
		elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "moderate_post", $bCurrentUserIsAdmin))
		{
			$arResult["PostPerm"] = BLOG_PERMS_MODERATE;
		}

		if ($arParams["SET_TITLE"] != "N")
		{
			$APPLICATION->SetTitle(GetMessage("BM_BLOG_POST"));
		}

		$arResult["PATH_TO_4ME_ALL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("user_id" => $arParams["USER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));
	}
}
elseif(
	IntVal($arParams["USER_ID"]) > 0
	&& !empty($arUser)
	&& CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "blog")
)
{
	$arResult["showAll"] = "Y";
	$arResult["PostPerm"] = BLOG_PERMS_FULL;
	$arResult["PATH_TO_4ME_ALL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("user_id" => $arParams["USER_ID"]));

	if ($arParams["USER_ID"] == $user_id)
	{
		$arResult["PATH_TO_4ME"] = $arResult["PATH_TO_MINE"] = $arResult["PATH_TO_4ME_ALL"];
		$arResult["PATH_TO_4ME"] .= (strpos($arResult["PATH_TO_4ME"], "?") === false ? "?" : "&");

		$arResult["PATH_TO_MINE"] = $arResult["PATH_TO_4ME"]."mine=Y";
		$arResult["PATH_TO_4ME"] .= "forme=Y";
	}

	$arResult["forme"] = $_REQUEST["forme"];

	if(strlen($_REQUEST["forme"]) <= 0)
	{
		$arResult["forme"] = "ALL";
	}

	if($_REQUEST["mine"] == "Y")
	{
		$arResult["forme"] = "";
	}


	$arResult["show4MeAll"] = ($arParams["USER_ID"] == $user_id ? "Y" : "N");
	$arResult["show4Me"] = ($arParams["USER_ID"] == $user_id ? "Y" : "N");
}

if($arResult["PostPerm"] >= BLOG_PERMS_WRITE)
{
	if(!$bGroupMode)
	{
		$arResult["urlToDraft"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_DRAFT"], array("user_id" => $arParams["USER_ID"]));
		$dbPost = CBlogPost::GetList(
			array(),
			Array(
				"AUTHOR_ID" => $user_id,
				"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_DRAFT,
				"BLOG_USE_SOCNET" => "Y",
				"GROUP_ID" => $arParams["GROUP_ID"],
				"GROUP_SITE_ID" => SITE_ID,
			),
			Array("COUNT" => "ID"),
			false,
			Array("ID")
		);
		if($arPost = $dbPost->Fetch())
		{
			$arResult["CntToDraft"] = $arPost["ID"];
		}

		$arResult["urlToModeration"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MODERATION"], array("user_id" => $arParams["USER_ID"]));
		$dbPost = CBlogPost::GetList(
			array(),
			array(
					"AUTHOR_ID" => $arParams["USER_ID"],
					"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_READY,
					"BLOG_USE_SOCNET" => "Y",
					"GROUP_ID" => $arParams["GROUP_ID"],
					"GROUP_SITE_ID" => SITE_ID,
				),
			Array("COUNT" => "ID"),
			false,
			Array("ID")
		);
		if($arPost = $dbPost->Fetch())
		{
			$arResult["CntToModerate"] = $arPost["ID"];
		}

		$arResult["urlToTags"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TAGS"], array("user_id" => $arParams["USER_ID"]));

		$arBlog = \Bitrix\Blog\Item\Blog::getByUser(array(
			"GROUP_ID" => $arParams["GROUP_ID"],
			"SITE_ID" => SITE_ID,
			"USER_ID" => $arParams["USER_ID"],
			"USE_SOCNET" => "Y"
		));

		if ($arBlog)
		{
			$res = CBlogCategory::getList(
				array(),
				array("BLOG_ID" => $arBlog["ID"]),
				array("COUNT" => "ID"),
				false,
				array("ID")
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
			$arResult["urlToModeration"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MODERATION"], array("group_id" => $arParams["SOCNET_GROUP_ID"]));
			$dbPost = CBlogPost::GetList(
				array(),
				array(
						"SOCNET_GROUP_ID" => $arParams["SOCNET_GROUP_ID"], 
						"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_READY,
						"BLOG_USE_SOCNET" => "Y",
						"GROUP_ID" => $arParams["GROUP_ID"],
						"GROUP_SITE_ID" => SITE_ID,
					),
				Array("COUNT" => "ID", "SOCNET_GROUP_ID"),
				false,
				Array("ID", "SOCNET_GROUP_ID")
			);
			if($arPost = $dbPost->Fetch())
			{
				$arResult["CntToModerate"] = $arPost["CNT"];
				$arResult["showAll"] = "Y";
			}
		}
	}
}

$arResult["page"] = "all";
if($arParams["CURRENT_PAGE"] == "moderation")
	$arResult["page"] = "moderation";
elseif($arParams["CURRENT_PAGE"] == "draft")
	$arResult["page"] = "draft";
elseif($arParams["CURRENT_PAGE"] == "tags")
	$arResult["page"] = "tags";
elseif($_REQUEST["mine"] == "Y")
	$arResult["page"] = "mine";
elseif($arResult["forme"] == "Y")
	$arResult["page"] = "forme";

$this->IncludeComponentTemplate();
?>