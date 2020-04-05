<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Socialnetwork\Integration\Main\UISelector;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class Entities
{
	const EXTRANET_CONTEXT_EXTERNAL = 'E';
	const EXTRANET_CONTEXT_INTERNAL = 'I';

	public static function getData($options = array())
	{
		$result = array(
			'ITEMS' => array(),
			'ITEMS_LAST' => array(),
			'DEST_SORT' => array()
		);

		$filterParams = array(
			"DEST_CONTEXT" => $options["context"],
			"ALLOW_EMAIL_INVITATION" => (isset($options["allowEmailInvitation"]) && $options["allowEmailInvitation"] == "Y")
		);

		if (!empty($options['contextCode']))
		{
			$filterParams["CODE_TYPE"] = $options['contextCode'];
		}

		$destSortData = \CSocNetLogDestination::getDestinationSort($filterParams);

		$result["DEST_SORT"] = $destSortData;

		$lastItems = $items = array();
		\CSocNetLogDestination::fillLastDestination(
			$destSortData,
			$lastItems,
			array(
				"EMAILS" => (isset($options["allowAddUser"]) ? $options["allowAddUser"] : 'N')
			)
		);

		if (
			!Handler::isExtranetUser()
			&& \Bitrix\Socialnetwork\ComponentHelper::getAllowToAllDestination()
			&& (!isset($options["enableAll"]) || $options["enableAll"] != 'N')
		)
		{
			$lastItems['GROUPS'] = array(
				'UA' => true
			);
		}

		$result["ITEMS_LAST"] = $lastItems;

		if ($options["enableSonetgroups"] != 'N')
		{
			$limitReached = false;
			$sonetGroupsList = \Bitrix\Socialnetwork\ComponentHelper::getSonetGroupAvailable(array(
				'limit' => 100
			), $limitReached);

			$result["SONETGROUPS_LIMITED"] = ($limitReached ? 'Y' : 'N');

			if(
				!empty($lastItems['SONETGROUPS'])
				&& !empty($sonetGroupsList)
			)
			{
				$lastSonetGroupsList = array();
				foreach ($lastItems['SONETGROUPS'] as $value)
				{
					if (!array_key_exists($value, $sonetGroupsList))
					{
						$lastSonetGroupsList[] = intval(substr($value, 2));
					}
				}
				if (!empty($lastSonetGroupsList))
				{
					$sonetGroupsAdditionalList = \CSocNetLogDestination::getSocnetGroup(array(
						'features' => array("blog", array("premoderate_post", "moderate_post", "write_post", "full_post")),
						'id' => $lastSonetGroupsList
					));
					if (!empty($sonetGroupsAdditionalList))
					{
						$sonetGroupsList = array_merge($sonetGroupsList, $sonetGroupsAdditionalList);
					}
				}
			}
			$items['SONETGROUPS'] = $sonetGroupsList;
		}

		if (Handler::isExtranetUser())
		{
			$result['EXTRANET_USER'] = 'Y';
			$items['USERS'] = \CSocNetLogDestination::getExtranetUser();
		}
		else
		{
			$lastUserList = array();
			if(!empty($lastItems['USERS']))
			{
				foreach ($lastItems['USERS'] as $value)
				{
					$lastUserList[] = str_replace('U', '', $value);
				}
			}

			$result['EXTRANET_USER'] = 'N';
			if (!empty($lastUserList))
			{
				$items['USERS'] = \CSocNetLogDestination::getUsers(array(
					'id' => $lastUserList,
					'CRM_ENTITY' => ModuleManager::isModuleInstalled('crm')
				));
				if (
					isset($options['extranetContext'])
					&& in_array($options['extranetContext'], array(self::EXTRANET_CONTEXT_INTERNAL, self::EXTRANET_CONTEXT_EXTERNAL))
				)
				{
					foreach($items['USERS'] as $key => $value)
					{
						if (isset($value["isExtranet"]))
						{
							if (
								(
									$value["isExtranet"] == 'Y'
									&& $options['extranetContext'] == self::EXTRANET_CONTEXT_INTERNAL
								)
								|| (
									$value["isExtranet"] == 'N'
									&& $options['extranetContext'] == self::EXTRANET_CONTEXT_EXTERNAL
								)
							)
							{
								unset($items['USERS'][$key]);
							}
						}
					}
				}
			}

			if ($options["allowEmailInvitation"] == "Y")
			{
//				\Bitrix\Socialnetwork\ComponentHelper::fillSelectedUsersToInvite($_POST, $arParams, $arResult);
				\CSocNetLogDestination::fillEmails($items);
			}
		}

		if (
			$options["enableDepartments"] == "Y"
			&& ModuleManager::isModuleInstalled('intranet')
			&& !Handler::isExtranetUser()
		)
		{
			$structure = \CSocNetLogDestination::getStucture(array("LAZY_LOAD" => true));
			$items['DEPARTMENT'] = $structure['department'];
			$items['DEPARTMENT_RELATION'] = $structure['department_relation'];
		}

		$result["ITEMS"] = $items;

		$lastItems = array();
		foreach($result["ITEMS_LAST"] as $group => $items)
		{
			$i = 0;
			foreach($result["ITEMS_LAST"][$group] as $key => $value)
			{
				$lastItems[$group][$key] = ++$i;
			}
		}
		$result["ITEMS_LAST"] = $lastItems;

		return $result;
	}

	public static function getList($params = array())
	{
		$itemsSelected = $params['itemsSelected'];

		$entities = array(
			'USERS' => array(),
			'SONETGROUPS' => array(),
			'GROUPS' => array(),
			'DEPARTMENTS' => array()
		);

		$sonetGroupIdList = $userIdList = $departmentIdList = array();
		foreach ($itemsSelected as $code => $entityGroup)
		{
			if ($entityGroup == 'users')
			{
				$userIdList[] = str_replace('U', '', $code);
			}
			elseif ($entityGroup == 'sonetgroups')
			{
				$sonetGroupIdList[] = str_replace('SG', '', $code);
			}
			elseif ($entityGroup == 'department')
			{
				$departmentIdList[] = str_replace('DR', '', $code);
			}
		}

		if (!empty($userIdList))
		{
			$entities['USERS'] = self::getUsers(array('id' => $userIdList));
		}

		if (!empty($sonetGroupIdList))
		{
			$entities['SONETGROUPS'] = self::getSonetgroups(array('id' => $sonetGroupIdList));
		}

		if (!empty($departmentIdList))
		{
			$entities['DEPARTMENTS'] = self::getDepartments(array('id' => $departmentIdList));
		}

		if (
			!Handler::isExtranetUser()
			&& \Bitrix\Socialnetwork\ComponentHelper::getAllowToAllDestination()
		)
		{
			$entities['GROUPS'] = array(
				'UA' => array(
					'id' => 'UA',
					'name' => (
					ModuleManager::isModuleInstalled('intranet')
						? Loc::getMessage("MPF_DESTINATION_3")
						: Loc::getMessage("MPF_DESTINATION_4")
					)
				)
			);
		}
		else
		{
			$entities['GROUPS'] = array();
		}

		return $entities;
	}

	protected static function getUsers($params = array())
	{
		return \CSocNetLogDestination::getUsers($params);
	}

	protected static function getSonetgroups($params = array())
	{
		return \CSocNetLogDestination::getSocnetGroup($params);
	}

	protected static function getDepartments($params = array())
	{
		$result = array();
		if (
			!empty($params['id'])
			&& is_array($params['id'])
			&& Loader::includeModule('intranet')
		)
		{
			$departmentsList = \CIntranetUtils::getDepartmentsData($params['id']);
			foreach($departmentsList as $id => $name)
			{
				$result['DR'.$id] = array(
					'id' => 'DR'.$id,
					'entityId' => $id,
					'name' => htmlspecialcharsbx($name),
					'parent' => false
				);
			}
		}

		return $result;
	}

	public static function getDepartmentData($requestFields = array())
	{
		return array(
			'USERS' => \CSocNetLogDestination::getUsers(
				array(
					'deportament_id' => $requestFields['DEPARTMENT_ID'],
					"NAME_TEMPLATE" => Handler::getNameTemplate($requestFields)
				)
			),
			'dataOnly' => true
		);
	}
}
