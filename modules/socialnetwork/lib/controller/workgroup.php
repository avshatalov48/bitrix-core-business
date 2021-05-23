<?
namespace Bitrix\Socialnetwork\Controller;

use Bitrix\Main\Error;
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
}