<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if (
	$arResult["SHOW_FULL_FORM"]
	&& $arParams["B_CALENDAR"]
	&& empty($arResult["Post"])
	&& !isset($arParams["DISPLAY"])
	&& !$arResult["bExtranetUser"]
)
{
	$arResult["PostToShow"]["FEED_DESTINATION_CALENDAR"] = $arResult["PostToShow"]["FEED_DESTINATION"];

	$arResult["DEST_SORT_CALENDAR"] = CSocNetLogDestination::GetDestinationSort(array(
		"DEST_CONTEXT" => "CALENDAR",
		"ALLOW_EMAIL_INVITATION" => false
	));
	$arResult["PostToShow"]["FEED_DESTINATION_CALENDAR"]['LAST'] = array();
	CSocNetLogDestination::fillLastDestination($arResult["DEST_SORT_CALENDAR"], $arResult["PostToShow"]["FEED_DESTINATION_CALENDAR"]['LAST']);

	$arDestUser = array();

	if(!empty($arResult["PostToShow"]["FEED_DESTINATION_CALENDAR"]['LAST']['USERS']))
	{
		foreach ($arResult["PostToShow"]["FEED_DESTINATION_CALENDAR"]['LAST']['USERS'] as $value)
		{
			$arDestUser[] = str_replace('U', '', $value);
		}
	}

	$arResult["PostToShow"]["FEED_DESTINATION_CALENDAR"]['USERS'] = CSocNetLogDestination::GetUsers(Array('id' => $arDestUser));
}

if (
	$arResult["SHOW_FULL_FORM"]
	&& $arResult["BLOG_POST_TASKS"]
)
{
	$userPage = \Bitrix\Main\Config\Option::get('socialnetwork', 'user_page', SITE_DIR.'company/personal/');
	$workgroupPage = \Bitrix\Main\Config\Option::get('socialnetwork', 'workgroups_page', SITE_DIR.'workgroups/');

	$arParams['PATH_TO_USER_PROFILE'] = (!empty($arParams['PATH_TO_USER_PROFILE']) ? $arParams['PATH_TO_USER_PROFILE'] : $workgroupPage.'user/#user_id#/');
	$arParams['PATH_TO_GROUP'] = (!empty($arParams['PATH_TO_GROUP']) ? $arParams['PATH_TO_GROUP'] : $workgroupPage.'group/#group_id#/');
	$arParams['PATH_TO_USER_TASKS'] = (!empty($arParams['PATH_TO_USER_TASKS']) ? $arParams['PATH_TO_USER_TASKS'] : $userPage.'user/#user_id#/tasks/');
	$arParams['PATH_TO_USER_TASKS_TASK'] = (!empty($arParams['PATH_TO_USER_TASKS_TASK']) ? $arParams['PATH_TO_USER_TASKS_TASK'] : $userPage.'user/#user_id#/tasks/task/#action#/#task_id#/');
	$arParams['PATH_TO_GROUP_TASKS'] = (!empty($arParams['PATH_TO_GROUP_TASKS']) ? $arParams['PATH_TO_GROUP_TASKS'] : $workgroupPage.'group/#group_id#/tasks/');
	$arParams['PATH_TO_GROUP_TASKS_TASK'] = (!empty($arParams['PATH_TO_GROUP_TASKS_TASK']) ? $arParams['PATH_TO_GROUP_TASKS_TASK'] : $workgroupPage.'group/#group_id#/tasks/task/#action#/#task_id#/');
	$arParams['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'] = (!empty($arParams['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW']) ? $arParams['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'] : $userPage.'user/#user_id#/tasks/projects/');
	$arParams['PATH_TO_USER_TASKS_TEMPLATES'] = (!empty($arParams['PATH_TO_USER_TASKS_TEMPLATES']) ? $arParams['PATH_TO_USER_TASKS_TEMPLATES'] : $userPage.'user/#user_id#/tasks/templates/');
	$arParams['PATH_TO_USER_TEMPLATES_TEMPLATE'] = (!empty($arParams['PATH_TO_USER_TEMPLATES_TEMPLATE']) ? $arParams['PATH_TO_USER_TEMPLATES_TEMPLATE'] : $userPage.'user/#user_id#/tasks/templates/template/#action#/#template_id#/');
	$arParams['TASK_SUBMIT_BACKURL'] = $APPLICATION->GetCurPageParam(isset($arParams["LOG_EXPERT_MODE"]) && $arParams["LOG_EXPERT_MODE"] == 'Y' ? "taskIdCreated=#task_id#" : "", array(
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
	));
}

if (
	isset($_GET["taskIdCreated"])
	&& intval($_GET["taskIdCreated"]) > 0
)
{
	$_SESSION["SL_TASK_ID_CREATED"] = intval($_GET["taskIdCreated"]);
	LocalRedirect($APPLICATION->GetCurPageParam("", array("taskIdCreated", "EVENT_TYPE", "EVENT_TASK_ID", "EVENT_OPTION")));
}

$arResult["SHOW_BLOG_FORM_TARGET"] = isset($arParams["SHOW_BLOG_FORM_TARGET"]) && $arParams["SHOW_BLOG_FORM_TARGET"];