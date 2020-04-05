<?
namespace Bitrix\Socialnetwork\Integration\Main\UISelector;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

class EmailUsers extends \Bitrix\Main\UI\Selector\EntityBase
{
	public function getData($params = array())
	{
		global $USER;

		$result = array(
			'ITEMS' => array(),
			'ITEMS_LAST' => array(),
			'ITEMS_HIDDEN' => array(),
			'ADDITIONAL_INFO' => array(
				'SORT_SELECTED' => 200
			)
		);

		if (!ModuleManager::isModuleInstalled('mail'))
		{
			return $result;
		}

		$options = (!empty($params['options']) ? $params['options'] : array());

		$lastItems = (!empty($params['lastItems']) ? $params['lastItems'] : array());
		$selectedItems = (!empty($params['selectedItems']) ? $params['selectedItems'] : array());

		$lastUserList = array();
		if(!empty($lastItems[Handler::ENTITY_TYPE_USERS]))
		{
			foreach ($lastItems[Handler::ENTITY_TYPE_USERS] as $value)
			{
				$lastUserList[] = str_replace('U', '', $value);
			}
		}

		$selectedUserList = array();
		if(!empty($selectedItems[Handler::ENTITY_TYPE_USERS]))
		{
			foreach ($selectedItems[Handler::ENTITY_TYPE_USERS] as $value)
			{
				$selectedUserList[] = str_replace('U', '', $value);
			}
		}

		if (
			!empty($lastUserList)
			|| !empty($selectedUserList)
		)
		{
			$usersList = \CSocNetLogDestination::getUsers(array(
				'id' => array_merge($lastUserList, $selectedUserList),
				'CRM_ENTITY' => ModuleManager::isModuleInstalled('crm')
			));

			$crmInstalled = ModuleManager::isModuleInstalled('crm');

			foreach($usersList as $key => $user)
			{
				if (
					(
						!empty($user['isEmail'])
						&& $user['isEmail'] == 'Y'
					)
					&& (
						empty($user['isCrmEmail'])
						|| $user['isCrmEmail'] != 'Y'
						|| empty($options['allowSearchCrmEmailUsers'])
						|| $options['allowSearchCrmEmailUsers'] == 'N'
						|| !$crmInstalled
					)
				)
				{
					$result['ITEMS'][$key] = $user;
					$result["ITEMS_LAST"][] = $key;
				}
			}

			if (
				!empty($selectedUserList)
				&& $USER->isAuthorized()
				&& !\CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false)
			)
			{
				$mySelectedEmailUserIdList = [];
				$finderDestFilter = array(
					"USER_ID" => $USER->getId(),
					"=CODE_TYPE" => "U",
					"=CODE_USER.EXTERNAL_AUTH_ID" => 'email',
				);
				$finderDestSelect = array(
					'CODE_USER_ID'
				);
				$res = \Bitrix\Main\FinderDestTable::getList(array(
					'order' => array(),
					'filter' => $finderDestFilter,
					'group' => array("CODE_USER_ID"),
					'select' => $finderDestSelect
				));
				while ($userFields = $res->fetch())
				{
					if (!empty($userFields))
					{
						$mySelectedEmailUserIdList[] = 'U'.$userFields['CODE_USER_ID'];
					}
				}

				foreach($selectedUserList as $selectedUserId)
				{
					$code = 'U'.$selectedUserId;
					if (
						isset($result['ITEMS'][$code])
						&& isset($result['ITEMS'][$code])
						&& isset($result['ITEMS'][$code]['isEmail'])
						&& $result['ITEMS'][$code]['isEmail'] == 'Y'
						&& !in_array($code, $mySelectedEmailUserIdList)
					)
					{
						$result['ITEMS_HIDDEN'][] = $code;
						unset($result['ITEMS'][$code]);
					}
				}
				$result["ITEMS_LAST"] = array_filter(
					$result["ITEMS_LAST"],
					function ($item) use ($mySelectedEmailUserIdList)
					{
						return in_array($item, $mySelectedEmailUserIdList);
					}
				);
			}
		}

		return $result;
	}

	public function getTabList($params = array())
	{
		$result = array();
		$options = (!empty($params['options']) ? $params['options'] : array());

		if (
			isset($options['addTab'])
			&& $options['addTab'] == 'Y'
			&& ModuleManager::isModuleInstalled('mail')
		)
		{
			$result[] = array(
				'id' => 'emailusers',
				'name' => Loc::getMessage('MAIN_UI_SELECTOR_TAB_EMAIL_USERS'),
				'sort' => 100
			);
		}

		return $result;
	}

	public function getItemName($itemCode = '')
	{
		return \Bitrix\Socialnetwork\Integration\Main\UISelector\Users::getUserName($itemCode);
	}
}