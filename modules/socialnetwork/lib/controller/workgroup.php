<?php
namespace Bitrix\Socialnetwork\Controller;

use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class Workgroup extends \Bitrix\Socialnetwork\Controller\Base
{
	public function getAction(array $params = [])
	{
		$groupId = (isset($params['groupId']) ? (int)($params['groupId']) : 0);

		if ($groupId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_WORKGROUP_EMPTY'), 'SONET_CONTROLLER_WORKGROUP_EMPTY'));
			return null;
		}

		$filter = [
			'ID' => $groupId
		];

		if (!\CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false))
		{
			$filter['CHECK_PERMISSIONS'] = $this->getCurrentUser()->getId();
		}

		$res = \CSocNetGroup::getList([], $filter, false, false, ['ID']);
		if ($groupFields = $res->fetch())
		{
			$groupItem = \Bitrix\Socialnetwork\Item\Workgroup::getById($groupFields['ID']);
			return $groupItem->getFields();
		}

		$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_WORKGROUP_NOT_FOUND'), 'SONET_CONTROLLER_WORKGROUP_NOT_FOUND'));
		return null;
	}

	public function setFavoritesAction(array $params = [])
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$value = (isset($params['value']) && in_array($params['value'], [ 'Y', 'N' ]) ? $params['value'] : false);
		$getAdditionalResultData = (bool)($params['getAdditionalResultData'] ?? false);

		if ($groupId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_WORKGROUP_EMPTY'), 'SONET_CONTROLLER_WORKGROUP_EMPTY'));
			return null;
		}

		if ($value === false)
		{
			$this->addError(new Error('SONET_CONTROLLER_WORKGROUP_INCORRECT_VALUE', 'SONET_CONTROLLER_WORKGROUP_INCORRECT_VALUE'));
			return null;
		}

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('SONET_CONTROLLER_MODULE_NOT_INSTALLED', 'SONET_CONTROLLER_MODULE_NOT_INSTALLED'));
			return null;
		}

		try
		{
			$res = \Bitrix\Socialnetwork\Item\WorkgroupFavorites::set([
				'GROUP_ID' => $groupId,
				'USER_ID' => $this->getCurrentUser(),
				'VALUE' => $value,
			]);
		}
		catch (\Exception $e)
		{
			$this->addError(new Error($e->getMessage(), $e->getCode()));
			return null;
		}

		if (!$res)
		{
			$this->addError(new Error('SONET_CONTROLLER_WORKGROUP_ACTION_FAILED', 'SONET_CONTROLLER_WORKGROUP_ACTION_FAILED'));
			return null;
		}

		if ($getAdditionalResultData)
		{
			$groupItem = \Bitrix\Socialnetwork\Item\Workgroup::getById($groupId);
			$groupFields = $groupItem->getFields();
			$groupUrlData = $groupItem->getGroupUrlData([
				'USER_ID' => $this->getCurrentUser(),
			]);

			$groupSiteList = [];
			$resSite = \Bitrix\Socialnetwork\WorkgroupSiteTable::getList([
				'filter' => [
					'=GROUP_ID' => $groupId
				],
				'select' => [ 'SITE_ID' ],
			]);
			while ($groupSite = $resSite->fetch())
			{
				$groupSiteList[] = $groupSite['SITE_ID'];
			}
		}

		$result = [
			'ID' => $groupId,
			'RESULT' => $value,
		];

		if ($getAdditionalResultData)
		{
			$result['NAME'] = $groupFields['NAME'];
			$result['URL'] = $groupUrlData["URL"];
			$result['EXTRANET'] = (
				Loader::includeModule('extranet')
				&& \CExtranet::isIntranetUser()
				&& in_array(\CExtranet::getExtranetSiteId(), $groupSiteList)
					? 'Y'
					: 'N'
			);
		}

		return $result;
	}
}