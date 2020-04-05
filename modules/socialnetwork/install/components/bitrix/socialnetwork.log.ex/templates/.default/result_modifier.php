<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (IsModuleInstalled('tasks'))
{
	$userPage = \Bitrix\Main\Config\Option::get('socialnetwork', 'user_page', SITE_DIR.'company/personal/');
	$workgroupPage = \Bitrix\Main\Config\Option::get('socialnetwork', 'workgroups_page', SITE_DIR.'workgroups/');

	$arParams['PATH_TO_USER_TASKS'] = (!empty($arParams['PATH_TO_USER_TASKS']) ? $arParams['PATH_TO_USER_TASKS'] : $userPage.'user/#user_id#/tasks/');
	$arParams['PATH_TO_USER_TASKS_TASK'] = (!empty($arParams['PATH_TO_USER_TASKS_TASK']) ? $arParams['PATH_TO_USER_TASKS_TASK'] : $userPage.'user/#user_id#/tasks/task/#action#/#task_id#/');
	$arParams['PATH_TO_GROUP_TASKS'] = (!empty($arParams['PATH_TO_GROUP_TASKS']) ? $arParams['PATH_TO_GROUP_TASKS'] : $workgroupPage.'group/#group_id#/tasks/');
	$arParams['PATH_TO_GROUP_TASKS_TASK'] = (!empty($arParams['PATH_TO_GROUP_TASKS_TASK']) ? $arParams['PATH_TO_GROUP_TASKS_TASK'] : $workgroupPage.'group/#group_id#/tasks/task/#action#/#task_id#/');
	$arParams['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'] = (!empty($arParams['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW']) ? $arParams['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'] : $userPage.'user/#user_id#/tasks/projects/');
	$arParams['PATH_TO_USER_TASKS_TEMPLATES'] = (!empty($arParams['PATH_TO_USER_TASKS_TEMPLATES']) ? $arParams['PATH_TO_USER_TASKS_TEMPLATES'] : $userPage.'user/#user_id#/tasks/templates/');
	$arParams['PATH_TO_USER_TEMPLATES_TEMPLATE'] = (!empty($arParams['PATH_TO_USER_TEMPLATES_TEMPLATE']) ? $arParams['PATH_TO_USER_TEMPLATES_TEMPLATE'] : $userPage.'user/#user_id#/tasks/templates/template/#action#/#template_id#/');
}


$formTargetId = false;
$informerTargetId = false;
if (defined("BITRIX24_INDEX_PAGE"))
{
	$formTargetId = "topblock";
	$informerTargetId = "inside_pagetitle";
}
else
{
	if (isset($arParams["FORM_TARGET_ID"]))
	{
		$formTargetId = $arParams["FORM_TARGET_ID"];
	}

	if (isset($arParams["INFORMER_TARGET_ID"]))
	{
		$informerTargetId = $arParams["INFORMER_TARGET_ID"];
	}
}

$arResult['TOP_RATING_DATA'] = (
	\Bitrix\Main\ModuleManager::isModuleInstalled('intranet')
	&& !empty($arResult["arLogTmpID"])
		? \Bitrix\Socialnetwork\ComponentHelper::getLivefeedRatingData(array(
			'logId' => array_unique($arResult["arLogTmpID"]),
		))
		: array()
);

$arResult["FORM_TARGET_ID"] = $formTargetId;
$arResult["INFORMER_TARGET_ID"] = $informerTargetId;