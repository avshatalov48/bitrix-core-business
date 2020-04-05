<?
namespace Bitrix\Socialnetwork\Integration\Main\UISelector;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

class EmailUsers extends \Bitrix\Main\UI\Selector\EntityBase
{
	public function getData($params = array())
	{
		$result = array(
			'ITEMS' => array(),
			'ITEMS_LAST' => array(),
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

		$lastUserList = array();
		if(!empty($lastItems[Handler::ENTITY_TYPE_USERS]))
		{
			foreach ($lastItems[Handler::ENTITY_TYPE_USERS] as $value)
			{
				$lastUserList[] = str_replace('U', '', $value);
			}
		}

		if (!empty($lastUserList))
		{
			$usersList = \CSocNetLogDestination::getUsers(array(
				'id' => $lastUserList,
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