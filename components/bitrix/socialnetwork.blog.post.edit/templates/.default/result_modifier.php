<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\UI\EntitySelector;

if (
	$arResult["SHOW_FULL_FORM"]
	&& $arResult["BLOG_POST_TASKS"]
)
{
	$userPage = Option::get('socialnetwork', 'user_page', SITE_DIR.'company/personal/');
	$workgroupPage = Option::get('socialnetwork', 'workgroups_page', SITE_DIR.'workgroups/');

	$arParams['PATH_TO_USER_PROFILE'] = (!empty($arParams['PATH_TO_USER_PROFILE']) ? $arParams['PATH_TO_USER_PROFILE'] : $workgroupPage.'user/#user_id#/');
	$arParams['PATH_TO_GROUP'] = (!empty($arParams['PATH_TO_GROUP']) ? $arParams['PATH_TO_GROUP'] : $workgroupPage.'group/#group_id#/');
	$arParams['PATH_TO_USER_TASKS'] = (!empty($arParams['PATH_TO_USER_TASKS']) ? $arParams['PATH_TO_USER_TASKS'] : $userPage.'user/#user_id#/tasks/');
	$arParams['PATH_TO_USER_TASKS_TASK'] = (!empty($arParams['PATH_TO_USER_TASKS_TASK']) ? $arParams['PATH_TO_USER_TASKS_TASK'] : $userPage.'user/#user_id#/tasks/task/#action#/#task_id#/');
	$arParams['PATH_TO_GROUP_TASKS'] = (!empty($arParams['PATH_TO_GROUP_TASKS']) ? $arParams['PATH_TO_GROUP_TASKS'] : $workgroupPage.'group/#group_id#/tasks/');
	$arParams['PATH_TO_GROUP_TASKS_TASK'] = (!empty($arParams['PATH_TO_GROUP_TASKS_TASK']) ? $arParams['PATH_TO_GROUP_TASKS_TASK'] : $workgroupPage.'group/#group_id#/tasks/task/#action#/#task_id#/');
	$arParams['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'] = (!empty($arParams['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW']) ? $arParams['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'] : $userPage.'user/#user_id#/tasks/projects/');
	$arParams['PATH_TO_USER_TASKS_SCRUM_OVERVIEW'] = (!empty($arParams['PATH_TO_USER_TASKS_SCRUM_OVERVIEW']) ? $arParams['PATH_TO_USER_TASKS_SCRUM_OVERVIEW'] : $userPage.'user/#user_id#/tasks/scrum/');
	$arParams['PATH_TO_USER_TASKS_TEMPLATES'] = (!empty($arParams['PATH_TO_USER_TASKS_TEMPLATES']) ? $arParams['PATH_TO_USER_TASKS_TEMPLATES'] : $userPage.'user/#user_id#/tasks/templates/');
	$arParams['PATH_TO_USER_TEMPLATES_TEMPLATE'] = (!empty($arParams['PATH_TO_USER_TEMPLATES_TEMPLATE']) ? $arParams['PATH_TO_USER_TEMPLATES_TEMPLATE'] : $userPage.'user/#user_id#/tasks/templates/template/#action#/#template_id#/');
	$arParams['TASK_SUBMIT_BACKURL'] = $APPLICATION->GetCurPageParam(isset($arParams["LOG_EXPERT_MODE"]) && $arParams["LOG_EXPERT_MODE"] === 'Y' ? "taskIdCreated=#task_id#" : "", [
		"flt_created_by_id",
		"flt_group_id",
		"flt_to_user_id",
		"flt_date_datesel",
		"flt_date_days",
		"flt_date_from",
		"flt_date_to",
		"flt_date_to",
		"preset_filter_id",
		"sessid",
		"bxajaxid",
		"logajax"
	]);
}

if (
	isset($_GET["taskIdCreated"])
	&& (int)$_GET["taskIdCreated"] > 0
)
{
	$_SESSION["SL_TASK_ID_CREATED"] = (int)$_GET["taskIdCreated"];
	LocalRedirect($APPLICATION->GetCurPageParam("", [ "taskIdCreated", "EVENT_TYPE", "EVENT_TASK_ID", "EVENT_OPTION", "EVENT_OPTIONS" ]));
}

$arResult["SHOW_BLOG_FORM_TARGET"] = isset($arParams["SHOW_BLOG_FORM_TARGET"]) && $arParams["SHOW_BLOG_FORM_TARGET"];
if (
	isset($arResult["POST_PROPERTIES"]["DATA"]["UF_IMPRTANT_DATE_END"]["VALUE"])
	&& $arResult["POST_PROPERTIES"]["DATA"]["UF_IMPRTANT_DATE_END"]["VALUE"]
)
{
	$postImportantTillDate = new \Bitrix\Main\Type\DateTime($arResult["POST_PROPERTIES"]["DATA"]["UF_IMPRTANT_DATE_END"]["VALUE"]);
	$postImportantTillDate = $postImportantTillDate->add("1D");
	$arResult["POST_PROPERTIES"]["DATA"]["UF_IMPRTANT_DATE_END"]["VALUE"] = $postImportantTillDate->format(\Bitrix\Main\Type\Date::convertFormatToPhp(CSite::GetDateFormat('SHORT')));
}

if (is_array($arResult["REMAIN_IMPORTANT_TILL"]))
{
	$arResult["REMAIN_IMPORTANT_DEFAULT_OPTION"] = reset($arResult["REMAIN_IMPORTANT_TILL"]);
	foreach ($arResult["REMAIN_IMPORTANT_TILL"] as $key => $attributesForPopupList)
	{
		if ($attributesForPopupList["VALUE"] === "CUSTOM")
		{
			$arResult["REMAIN_IMPORTANT_TILL"][$key]["CLASS"] = "js-custom-date-end";
		}
		else
		{
			$arResult["REMAIN_IMPORTANT_TILL"][$key]["CLASS"] = "";
			if ($attributesForPopupList["VALUE"] === "WEEK")
			{
				$arResult["REMAIN_IMPORTANT_DEFAULT_OPTION"]['TEXT_KEY'] = $arResult["REMAIN_IMPORTANT_TILL"][$key]["TEXT_KEY"];
				$arResult["REMAIN_IMPORTANT_DEFAULT_OPTION"]['VALUE'] = $arResult["REMAIN_IMPORTANT_TILL"][$key]["VALUE"];
			}
		}
	}
}

$arResult['bVarsFromForm'] = (
	array_key_exists("POST_MESSAGE", $_REQUEST)
	|| (string)$arResult["ERROR_MESSAGE"] !== ''
	|| (
		isset($arResult["needShow"])
		&& $arResult["needShow"]
	)
);
$arResult['tabActive'] = ($arResult['bVarsFromForm'] ? $_REQUEST["changePostFormTab"] : "message");

$arResult['tabs'] = [];
$gratCurrentUsersList = $arResult['selectedGratitudeEntities'] = [];

if (
	(
		(
			isset($arParams["PAGE_ID"])
			&& $arParams["PAGE_ID"] === "user_blog_post_edit_grat"
		)
		|| (
			!empty($arResult["PostToShow"]["GRATS"])
			&& (
				!isset($arParams["PAGE_ID"])
				|| $arParams["PAGE_ID"] !== "user_blog_post_edit_profile"
			)
			&& is_array($arResult["PostToShow"]["GRATS"])
		)
	)
	&& ModuleManager::isModuleInstalled("intranet")
)
{
	$arResult['tabs'][] = 'grat';

	if (
		!empty($arResult["PostToShow"]["GRAT_CURRENT"]["ID"])
		|| !empty($arResult["PostToShow"]["GRAT_CURRENT"]["USERS"])
		|| (
			isset($arParams["PAGE_ID"])
			&& in_array($arParams["PAGE_ID"], [ 'user_blog_post_edit_grat', 'user_grat' ])
		)
	)
	{
		$arResult['tabActive'] = "grat";
	}

	if (
		array_key_exists("GRAT_CURRENT", $arResult["PostToShow"])
		&& is_array(($arResult["PostToShow"]["GRAT_CURRENT"]["USERS"] ?? null))
	)
	{
		foreach($arResult["PostToShow"]["GRAT_CURRENT"]["USERS"] as $grat_user_id)
		{
			$gratCurrentUsersList["U".$grat_user_id] = 'users';
		}
	}
	elseif (
		isset($arParams["PAGE_ID"])
		&& in_array($arParams["PAGE_ID"], [ 'user_blog_post_edit_grat', 'user_grat' ])
	)
	{
		$gratCurrentUsersList["U".(!empty($_REQUEST['gratUserId']) ? (int)$_REQUEST['gratUserId'] : $arParams['USER_ID'])] = 'users';
	}

	$arResult['selectedGratitudeEntities'] = EntitySelector\Converter::sortEntities(EntitySelector\Converter::convertFromFinderCodes(array_keys($gratCurrentUsersList)));
}

if ($arResult["BLOG_POST_TASKS"])
{
	$arResult['tabs'][] = 'tasks';
}

if (
	$arParams["B_CALENDAR"]
	&& empty($arResult["Post"])
	&& !isset($arParams["DISPLAY"])
	&& !$arResult["bExtranetUser"]
)
{
	$arResult['tabs'][] = 'calendar';
}

if (
	$arResult["BLOG_POST_LISTS"]
	&& empty($arResult["Post"])
	&& !isset($arParams["DISPLAY"])
	&& !$arResult["bExtranetUser"]
)
{
	$arResult['tabs'][] = 'lists';
}

if (
	empty($arResult["Post"])
	&& array_key_exists("UF_BLOG_POST_FILE", $arResult["POST_PROPERTIES"]["DATA"])
)
{
	$arResult['tabs'][] = 'file';
}

if (
	array_key_exists("UF_BLOG_POST_VOTE", $arResult["POST_PROPERTIES"]["DATA"])
	&& (
		!isset($arParams["PAGE_ID"])
		|| !in_array($arParams["PAGE_ID"], [ "user_blog_post_edit_profile", "user_blog_post_edit_grat", "user_blog_post_edit_post" ])
	)
)
{
	$arResult['tabs'][] = 'vote';

	if (
		!$arResult['bVarsFromForm']
		&& !!$arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_VOTE"]["VALUE"]
	)
	{
		$arResult['tabActive'] = "vote";
	}
}

if (
	!$arResult['bVarsFromForm']
	&& array_key_exists("UF_BLOG_POST_IMPRTNT", $arResult["POST_PROPERTIES"]["DATA"])
	&& !!$arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_IMPRTNT"]["VALUE"]

)
{
	$arResult['tabActive'] = "important";
}

if ($arParams['ID'] == 0)
{
	if (empty($arResult['PostToShow']['TITLE']) && !empty($_GET['TITLE']))
	{
		$arResult['PostToShow']['TITLE'] = htmlspecialcharsbx($_GET['TITLE']);
	}

	if (empty($arResult['POST_PROPERTIES']['DATA']['UF_MAIL_MESSAGE']['VALUE']) && !empty($_GET['UF_MAIL_MESSAGE']))
	{
		$arResult['POST_PROPERTIES']['DATA']['UF_MAIL_MESSAGE']['VALUE'] = (int) $_GET['UF_MAIL_MESSAGE'];
	}
}
