<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;
use Bitrix\Socialnetwork\Component\WorkgroupList;
use Bitrix\Socialnetwork\Helper;
use Bitrix\Tasks\Util\Restriction\Bitrix24Restriction\Limit\ProjectLimit;
use Bitrix\Tasks\Util\Restriction\Bitrix24Restriction\Limit\ScrumLimit;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

Loc::loadMessages(__FILE__);

$componentClassName = get_class($this->getComponent());

$arResult['TOOLBAR_BUTTONS'] = [];

if (
	$arParams['USER_ID'] === $arResult['CURRENT_USER_ID']
	&& Helper\Workgroup\Access::canCreate()
)
{
	$createProjectUrl = str_replace(
		[ '#id#', '#ID#', '#USER_ID#', '#user_id#' ],
		$arResult['CURRENT_USER_ID'],
		$arParams['PATH_TO_GROUP_CREATE']
	);

	$isProjectLimitExceeded = !Helper\Feature::isFeatureEnabled(Helper\Feature::PROJECTS_GROUPS);
	if (Helper\Feature::canTurnOnTrial(Helper\Feature::PROJECTS_GROUPS))
	{
		$isProjectLimitExceeded = false;
	}
	if ($isProjectLimitExceeded && Loader::includeModule('tasks'))
	{
		$createProjectUrl = 'javascript:' . ProjectLimit::getLimitLockClick(ProjectLimit::getFeatureId());
	}

	if (
		$arParams['MODE'] === WorkgroupList::MODE_TASKS_SCRUM
		&& Loader::includeModule('tasks')
	)
	{
		$isScrumLimited = ScrumLimit::isLimitExceeded() || !ScrumLimit::isFeatureEnabled();
		if (ScrumLimit::canTurnOnTrial())
		{
			$isScrumLimited = false;
		}
		if ($isScrumLimited)
		{
			$createProjectUrl = 'javascript:' . ScrumLimit::getLimitLockClick(ScrumLimit::getFeatureId());
		}
		else
		{
			$uri = new Uri($createProjectUrl);
			$uri->addParams([
				'PROJECT_OPTIONS' => [
					'scrum' => true,
				],
			]);

			$createProjectUrl = $uri->getUri();
		}
	}

	$arResult['TOOLBAR_BUTTONS'][] = [
		'TYPE' => 'ADD',
		'TITLE' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_TEMPLATE_BUTTON_CREATE_TITLE'),
		'LINK' => $createProjectUrl,
	];
}
