<?
namespace Bitrix\Socialnetwork\Integration\Main\UISelector;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

class CrmEmailUsers extends \Bitrix\Main\UI\Selector\EntityBase
{
	public function getData($params = array())
	{
		$result = array(
			'ITEMS' => array(),
			'ITEMS_LAST' => array(),
			'ADDITIONAL_INFO' => array(
				'GROUPS_LIST' => array(
					'crmemailusers' => array(
						'TITLE' => Loc::getMessage('MAIN_UI_SELECTOR_TITLE_CRM_EMAIL_USERS'),
						'TYPE_LIST' => array(Handler::ENTITY_TYPE_CRMEMAILUSERS),
						'DESC_LESS_MODE' => 'Y',
						'SORT' => 15
					)
				),
				'SORT_SELECTED' => 200
			)
		);

		if (
			!ModuleManager::isModuleInstalled('mail')
			|| !ModuleManager::isModuleInstalled('crm')
		)
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

			foreach($usersList as $key => $user)
			{
				if (
					!empty($user['isCrmEmail'])
					&& $user['isCrmEmail'] == 'Y'
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
			&& ModuleManager::isModuleInstalled('crm')
		)
		{
			$result[] = array(
				'id' => 'crmemailusers',
				'name' => Loc::getMessage('MAIN_UI_SELECTOR_TAB_CRM_EMAIL_USERS'),
				'sort' => 110
			);
		}

		return $result;
	}

	public function search($params = array())
	{
		$result = array(
			'ITEMS' => array(),
			'ADDITIONAL_INFO' => array()
		);

		$entityOptions = (!empty($params['options']) ? $params['options'] : array());
		$requestFields = (!empty($params['requestFields']) ? $params['requestFields'] : array());
		$commonOptions = (!empty($requestFields['options']) ? $requestFields['options'] : array());

		$search = $requestFields['searchString'];

		if ($entityOptions['allowSearchCrmEmailUsers'] == 'Y')
		{
			$crmEntities = \CSocNetLogDestination::searchCrmEntities(array(
				"SEARCH" => $search,
				"NAME_TEMPLATE" => \Bitrix\Socialnetwork\Integration\Main\UISelector\Users::getNameTemplate($commonOptions['userNameTemplate'])
			));
			foreach($crmEntities as $crmEntity)
			{
				$crmEntity['id'] = 'UE'.$crmEntity['email'];
				$result["ITEMS"][$crmEntity['id']] = $crmEntity;
			}
		}

		return $result;
	}

	public function getItemName($itemCode = '')
	{
		return \Bitrix\Socialnetwork\Integration\Main\UISelector\Users::getUserName($itemCode);
	}
}