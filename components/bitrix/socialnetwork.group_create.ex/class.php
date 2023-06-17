<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\SiteTable;
use Bitrix\Socialnetwork\ComponentHelper;

final class SocialnetworkGroupCreate extends \Bitrix\Socialnetwork\Component\WorkgroupForm
{
	public function onPrepareComponentParams($params)
	{
		if (
			isset($params['LID'])
			&& !empty($params['LID'])
		)
		{
			$res = SiteTable::getList([
				'filter' => [
					'=LID' => $params["LID"],
					'=ACTIVE' => 'Y',
				],
				'select' => [ 'LID' ]
			]);
			if ($res->fetch())
			{
				$this->setSiteId($params['LID']);
			}
		}

		$params['GROUP_ID'] = (
			(int) ($_REQUEST['SONET_GROUP_ID'] ?? 0) > 0
				? (int) $_REQUEST['SONET_GROUP_ID']
				: (int) ($params['GROUP_ID'] ?? null)
		);

		$params['SET_NAV_CHAIN'] = (($params['SET_NAV_CHAIN'] ?? null) === 'N' ? 'N' : 'Y');

		$params['IUS_INPUT_NAME'] = 'ius_ids';
		$params['IUS_INPUT_NAME_SUSPICIOUS'] = 'ius_susp';
		$params['IUS_INPUT_NAME_STRING'] = 'users_list_string_ius';
		$params['IUS_INPUT_NAME_EXTRANET'] = 'ius_ids_extranet';
		$params['IUS_INPUT_NAME_SUSPICIOUS_EXTRANET'] = 'ius_susp_extranet';
		$params['IUS_INPUT_NAME_STRING_EXTRANET'] = 'users_list_string_ius_extranet';

		if (strlen($params['NAME_TEMPLATE'] ?? '') <= 0)
		{
			$params['NAME_TEMPLATE'] = \CSite::getNameFormat();
		}

		$params['USE_KEYWORDS'] = (($params['USE_KEYWORDS'] ?? null) !== 'N' ? 'Y' : 'N');

		$params['PROJECT_OPTIONS'] = (isset($params['PROJECT_OPTIONS']) && is_array($params['PROJECT_OPTIONS']) ? $params['PROJECT_OPTIONS'] : []);
		foreach (array_keys($params['PROJECT_OPTIONS']) as $key)
		{
			if (in_array((string)$key, ['extranet', 'features', 'project', 'open', 'landing', 'tourId', 'scrum' ], true))
			{
				continue;
			}

			unset($params['PROJECT_OPTIONS'][$key]);
		}

		$preset = (!empty($_GET['preset']) ? $_GET['preset'] : false);
		if (
			$preset
			&& empty($params['PROJECT_OPTIONS'])
		)
		{
			$type = \Bitrix\Socialnetwork\Helper\Workgroup::getTypeByCode([
				'code' => $preset,
				'fullMode' => true,
			]);

			$params['PROJECT_OPTIONS'] = [];
			if (!empty($type['LANDING']))
			{
				$params['PROJECT_OPTIONS']['landing'] = ($type['LANDING'] === 'Y');
			}
			if (!empty($type['PROJECT']))
			{
				$params['PROJECT_OPTIONS']['project'] = ($type['PROJECT'] === 'Y');
			}
			if (!empty($type['SCRUM_PROJECT']))
			{
				$params['PROJECT_OPTIONS']['scrum'] = ($type['SCRUM_PROJECT'] === 'Y');
			}
		}

		if (!isset($params['PROJECT_OPTIONS']['scrum']))
		{
			$params['PROJECT_OPTIONS']['scrum'] = false;
		}

		$this->onPrepareComponentPathParams($params);

		return $params;
	}

	protected function onPrepareComponentPathParams(&$params): void
	{
		global $APPLICATION;

		if (strLen($params['USER_VAR'] ?? '') <= 0)
		{
			$params['USER_VAR'] = 'user_id';
		}

		if (strLen($params['PAGE_VAR'] ?? '') <= 0)
		{
			$params['PAGE_VAR'] = "page";
		}

		if (strLen($params['GROUP_VAR'] ?? '') <= 0)
		{
			$params['GROUP_VAR'] = 'group_id';
		}

		$params['PATH_TO_USER'] = trim($params['PATH_TO_USER'] ?? '');
		if ($params['PATH_TO_USER'] === '')
		{
			$params['PATH_TO_USER'] = htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=user&' . $params['USER_VAR'] . '=#user_id#');
		}

		$params['PATH_TO_GROUP'] = trim($params['PATH_TO_GROUP'] ?? '');
		if ($params['PATH_TO_GROUP'] === '')
		{
			$params['PATH_TO_GROUP'] = htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=group&' . $params['GROUP_VAR'] . '=#group_id#');
		}

		$workgroupSEFUrl = ComponentHelper::getWorkgroupSEFUrl();
		if ($workgroupSEFUrl !== '')
		{
			$params['PATH_TO_GROUP_GENERAL'] = $workgroupSEFUrl . 'group/#group_id#/general/';
		}

		$params['PATH_TO_GROUP_EDIT'] = trim($params['PATH_TO_GROUP_EDIT'] ?? '');
		if ($params['PATH_TO_GROUP_EDIT'] === '')
		{
			$params['PATH_TO_GROUP_EDIT'] = htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=group_edit&' . $params['GROUP_VAR'] . '=#group_id#');
		}

		$params['PATH_TO_GROUP_CREATE'] = trim($params['PATH_TO_GROUP_CREATE'] ?? '');
		if ($params['PATH_TO_GROUP_CREATE'] === '')
		{
			$params['PATH_TO_GROUP_CREATE'] = htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=group_create&' . $params['USER_VAR'] . '=#user_id#');
		}
	}

	protected function listKeysSignedParameters()
	{
		return [];
	}

	public function executeComponent()
	{
		$this->arResult = $this->prepareData();

		return $this->__includeComponent();
	}
}
