<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Main;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Intranet\Internals\UserSubordinationTable;

Loc::loadMessages(__FILE__);

class Workgroup
{
	const UF_ENTITY_ID = "SONET_GROUP";

	private $fields;
	static $groupsIdToCheckList = array();

	public function __construct()
	{
		$this->fields = array();
	}

	public static function getById($groupId = 0, $useCache = true)
	{
		global $USER_FIELD_MANAGER;

		static $cachedFields = array();

		$groupItem = false;
		$groupId = intval($groupId);

		if ($groupId > 0)
		{
			$groupItem = new Workgroup;
			$groupFields = array();

			if ($useCache && isset($cachedFields[$groupId]))
			{
				$groupFields = $cachedFields[$groupId];
			}
			else
			{
				$res = WorkgroupTable::getList(array(
					'filter' => array('=ID' => $groupId)
				));
				if ($fields = $res->fetch())
				{
					$groupFields = $fields;

					if ($groupFields['DATE_CREATE'] instanceof \Bitrix\Main\Type\DateTime)
					{
						$groupFields['DATE_CREATE'] = $groupFields['DATE_CREATE']->toString();
					}
					if ($groupFields['DATE_UPDATE'] instanceof \Bitrix\Main\Type\DateTime)
					{
						$groupFields['DATE_UPDATE'] = $groupFields['DATE_UPDATE']->toString();
					}
					if ($groupFields['DATE_ACTIVITY'] instanceof \Bitrix\Main\Type\DateTime)
					{
						$groupFields['DATE_ACTIVITY'] = $groupFields['DATE_ACTIVITY']->toString();
					}

					$uf = $USER_FIELD_MANAGER->getUserFields(self::UF_ENTITY_ID, $groupId, false, 0);
					if (is_array($uf))
					{
						$groupFields = array_merge($groupFields, $uf);
					}
				}

				$cachedFields[$groupId] = $groupFields;
			}

			$groupItem->setFields($groupFields);
		}

		return $groupItem;
	}

	public function setFields($fields = array())
	{
		$this->fields = $fields;
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function isProject()
	{
		return (
			isset($this->fields['PROJECT'])
			&& $this->fields['PROJECT'] == 'Y'
		);
	}

	private static function getSubDepartments($departmentList = array())
	{
		$result = array();

		if (
			is_array($departmentList)
			&& Loader::includeModule('iblock')
		)
		{
			foreach ($departmentList as $departmentId)
			{
				$res = \CIBlockSection::getList(
					array(),
					array(
						"ID" => intval($departmentId)
					),
					false,
					array("ID", "IBLOCK_ID", "LEFT_MARGIN", "RIGHT_MARGIN")
				);

				if ($rootSection = $res->fetch())
				{
					$filter = array(
						"IBLOCK_ID" => $rootSection["IBLOCK_ID"],
						"ACTIVE" => "Y",
						">=LEFT_MARGIN" => $rootSection["LEFT_MARGIN"],
						"<=RIGHT_MARGIN" => $rootSection["RIGHT_MARGIN"]
					);

					$res2 = \CIBlockSection::getList(
						array("left_margin"=>"asc"),
						$filter,
						false,
						array("ID")
					);

					while ($section = $res2->fetch())
					{
						$result[] = intval($section["ID"]);
					}
				}
			}

			$result = array_unique($result);
		}

		return $result;
	}

	public function syncDeptConnection()
	{
		if (!ModuleManager::isModuleInstalled('intranet'))
		{
			return;
		}

		$newUserList = $oldUserList = array();
		$oldRelationList = array();
		$groupFields = $this->getFields();

		if (
			empty($groupFields)
			|| empty($groupFields["ID"])
		)
		{
			return;
		}

		if (
			isset($groupFields['UF_SG_DEPT'])
			&& isset($groupFields['UF_SG_DEPT']['VALUE'])
			&& !empty($groupFields['UF_SG_DEPT']['VALUE'])
		)
		{
			$newDeptList = array_map('intval', $groupFields['UF_SG_DEPT']['VALUE']);
			$res = \CIntranetUtils::getDepartmentEmployees($newDeptList, true);
			while ($departmentMember = $res->fetch())
			{
				if ($departmentMember["ID"] != $groupFields["OWNER_ID"])
				{
					$newUserList[] = $departmentMember["ID"];
				}
			}
			$newUserList = array_map('intval', array_unique($newUserList));
		}

		$res = UserToGroupTable::getList(array(
			'filter' => array(
				'=GROUP_ID' => intval($groupFields["ID"]),
				'@ROLE' => array(UserToGroupTable::ROLE_OWNER, UserToGroupTable::ROLE_MODERATOR, UserToGroupTable::ROLE_USER),
				'AUTO_MEMBER' => 'Y'
			),
			'select' => array('ID', 'USER_ID')
		));
		while($relation = $res->fetch())
		{
			$oldUserList[] = $relation['USER_ID'];
			$oldRelationList[$relation['USER_ID']] = $relation['ID'];
		}
		$oldUserList = array_map('intval', array_unique($oldUserList));

		$userListPlus = array_diff($newUserList, $oldUserList);
		$userListMinus = array_diff($oldUserList, $newUserList);

		foreach($userListMinus as $userId)
		{
			if (isset($oldRelationList[$userId]))
			{
				UserToGroup::changeRelationAutoMembership(array(
					'RELATION_ID' => $oldRelationList[$userId],
					'VALUE' => 'N'
				));
			}
		}

		$changeList = $addList = array();

		if (!empty($userListPlus))
		{
			$memberList = array();
			$res = UserToGroupTable::getList(array(
				'filter' => array(
					'=GROUP_ID' => intval($groupFields["ID"]),
					'@USER_ID' => $userListPlus,
					'@ROLE' => array(UserToGroupTable::ROLE_OWNER, UserToGroupTable::ROLE_MODERATOR, UserToGroupTable::ROLE_USER),
				),
				'select' => array('ID', 'USER_ID')
			));
			while($relation = $res->fetch())
			{
				$memberList[] = $relation['USER_ID'];
			}
			$userListPlus = array_diff($userListPlus, $memberList);
			if (!empty($userListPlus))
			{
				$res = UserToGroupTable::getList(array(
					'filter' => array(
						'=GROUP_ID' => intval($groupFields["ID"]),
						'@USER_ID' => $userListPlus,
						'@ROLE' => array(UserToGroupTable::ROLE_REQUEST, UserToGroupTable::ROLE_BAN),
						'AUTO_MEMBER' => 'N'
					),
					'select' => array('ID', 'USER_ID', 'GROUP_ID')
				));
				while($relation = $res->fetch())
				{
					$changeList[] = intval($relation['USER_ID']);
					UserToGroup::changeRelationAutoMembership(array(
						'RELATION_ID' => intval($relation['ID']),
						'USER_ID' => intval($relation['USER_ID']),
						'GROUP_ID' => intval($relation['GROUP_ID']),
						'ROLE' => UserToGroupTable::ROLE_USER,
						'VALUE' => 'Y'
					));
				}

				$addList = array_diff($userListPlus, $changeList);

				foreach($addList as $addUserId)
				{
					UserToGroup::addRelationAutoMembership(array(
						'USER_ID' => $addUserId,
						'GROUP_ID' => intval($groupFields["ID"]),
						'ROLE' => UserToGroupTable::ROLE_USER,
						'VALUE' => 'Y'
					));
				}
			}
		}

		if (
			!empty($changeList)
			|| !empty($addList)
		)
		{
			\CSocNetGroup::setStat($groupFields["ID"]);
		}
	}

	public function getGroupUrlData($params = array())
	{
		static $cache = array();

		$groupFields = $this->getFields();
		$userId = (isset($params['USER_ID']) ? intval($params['USER_ID']) : false);

		if (
			!empty($cache)
			&& !empty($cache[$groupFields["ID"]])
		)
		{
			$groupUrlTemplate = $cache[$groupFields['ID']]['URL_TEMPLATE'];
			$groupSiteId = $cache[$groupFields['ID']]['SITE_ID'];
		}
		else
		{
			$groupSiteId = \CSocNetGroup::getDefaultSiteId($groupFields["ID"], $groupFields["SITE_ID"]);
			$workgroupsPage = Option::get("socialnetwork", "workgroups_page", "/workgroups/", SITE_ID);
			$groupUrlTemplate = Option::get("socialnetwork", "group_path_template", "/workgroups/group/#group_id#/", SITE_ID);
			$groupUrlTemplate = "#GROUPS_PATH#".substr($groupUrlTemplate, strlen($workgroupsPage), strlen($groupUrlTemplate) - strlen($workgroupsPage));

			$cache[$groupFields["ID"]] = array(
				'URL_TEMPLATE' => $groupUrlTemplate ,
				'SITE_ID' => $groupSiteId
			);
		}

		$groupUrl = str_replace(array("#group_id#", "#GROUP_ID#"), $groupFields["ID"], $groupUrlTemplate);
		$serverName = $domainName = '';

		if ($userId)
		{
			$tmp = \CSocNetLogTools::processPath(
				array(
					"GROUP_URL" => $groupUrl
				),
				$userId,
				$groupSiteId
			);

			$groupUrl = $tmp["URLS"]["GROUP_URL"];
			$serverName = (strpos($groupUrl, "http://") === 0 || strpos($groupUrl, "https://") === 0 ? "" : $tmp["SERVER_NAME"]);
			$domainName = (strpos($groupUrl, "http://") === 0 || strpos($groupUrl, "https://") === 0 ? "" : (isset($tmp["DOMAIN"]) && !empty($tmp["DOMAIN"]) ? "//".$tmp["DOMAIN"] : ""));
		}

		return array(
			'URL' => $groupUrl,
			'SERVER_NAME' => $serverName,
			'DOMAIN' => $domainName
		);
	}

	private static function getDelayedSubordination()
	{
		return (
			Loader::includeModule('intranet')
			&& method_exists('Bitrix\Intranet\Internals\UserSubordinationTable', 'getDelay')
			&& UserSubordinationTable::getDelayed()
		);
	}

	public static function onBeforeIBlockSectionUpdate($section)
	{
		if (
			!isset($section['ID'])
			|| intval($section['ID']) <= 0
			|| !isset($section['IBLOCK_ID'])
			|| intval($section['IBLOCK_ID']) <= 0
			|| $section['IBLOCK_ID'] != Option::get('intranet', 'iblock_structure', 0)
			|| (
				isset($section['ACTIVE'])
				&& $section['ACTIVE'] == 'N'
			)
			|| self::getDelayedSubordination()
		)
		{
			return true;
		}

		$rootSectionIdList = array();
		$res = \CIBlockSection::getNavChain($section['IBLOCK_ID'], $section['ID'], array('ID'));
		while ($rootSection = $res->fetch())
		{
			if ($rootSection['ID'] != $section['ID'])
			{
				$rootSectionIdList[] = $rootSection['ID'];
			}
		}

		if (!empty($rootSectionIdList))
		{
			$groupList = UserToGroup::getConnectedGroups($rootSectionIdList);
			self::$groupsIdToCheckList = array_merge(self::$groupsIdToCheckList, $groupList);
		}

		return true;
	}

	public static function onAfterIBlockSectionUpdate($section)
	{
		if(
			!isset($section['ID'])
			|| intval($section['ID']) <= 0
			|| !isset($section['IBLOCK_ID'])
			|| intval($section['IBLOCK_ID']) <= 0
			|| $section['IBLOCK_ID'] != Option::get('intranet', 'iblock_structure', 0)
			|| self::getDelayedSubordination()
		)
		{
			return true;
		}

		$groupsToCheck = array();
		if (
			isset($section['ACTIVE'])
			&& $section['ACTIVE'] == 'N'
		)
		{
			self::disconnectSection($section['ID']);
			$groupsToCheck = self::$groupsIdToCheckList;
		}
		else
		{
			$res = \CIBlockSection::getNavChain($section['IBLOCK_ID'], $section['ID'], array('ID'));
			while ($rootSection = $res->fetch())
			{
				if ($rootSection['ID'] != $section['ID'])
				{
					$rootSectionIdList[] = $rootSection['ID'];
				}
			}

			if (!empty($rootSectionIdList))
			{
				$newGroupsIdToCheckList = UserToGroup::getConnectedGroups($rootSectionIdList);
				if (!empty($newGroupsIdToCheckList))
				{
					$groupsToCheck = array_merge(self::$groupsIdToCheckList, $newGroupsIdToCheckList);
				}
			}
		}

		if (!empty($groupsToCheck))
		{
			$groupsToCheck = array_unique($groupsToCheck);
			foreach($groupsToCheck as $groupId)
			{
				$groupItem = \Bitrix\Socialnetwork\Item\Workgroup::getById($groupId, false);
				$groupItem->syncDeptConnection();
			}
		}

		return true;
	}

	public static function onBeforeIBlockSectionDelete($sectionId)
	{
		if (intval($sectionId) <= 0)
		{
			return true;
		}

		$res = \CIBlockSection::getList(array(), array('ID'=> $sectionId), false, array('ID', 'IBLOCK_ID'));
		if (
			!($section = $res->fetch())
			|| !isset($section['IBLOCK_ID'])
			|| intval($section['IBLOCK_ID']) <= 0
			|| $section['IBLOCK_ID'] != Option::get('intranet', 'iblock_structure', 0)
			|| (
				isset($section['ACTIVE'])
				&& $section['ACTIVE'] == 'N'
			)
			|| self::getDelayedSubordination()
		)
		{
			return true;
		}

		$rootSectionIdList = array();
		$res = \CIBlockSection::getNavChain($section['IBLOCK_ID'], $section['ID'], array('ID'));
		while ($rootSection = $res->fetch())
		{
			if ($rootSection['ID'] != $section['ID'])
			{
				$rootSectionIdList[] = $rootSection['ID'];
			}
		}

		if (!empty($rootSectionIdList))
		{
			$groupList = UserToGroup::getConnectedGroups($rootSectionIdList);
			self::$groupsIdToCheckList = array_merge(self::$groupsIdToCheckList, $groupList);
		}

		return true;
	}

	function onAfterIBlockSectionDelete($section)
	{
		if(
			!isset($section['ID'])
			|| intval($section['ID']) <= 0
			|| !isset($section['IBLOCK_ID'])
			|| intval($section['IBLOCK_ID']) <= 0
			|| $section['IBLOCK_ID'] != Option::get('intranet', 'iblock_structure', 0)
			|| self::getDelayedSubordination()
		)
		{
			return true;
		}

		self::disconnectSection($section['ID']);

		if (!empty(self::$groupsIdToCheckList))
		{
			$groupsToCheck = array_unique(self::$groupsIdToCheckList);
			foreach($groupsToCheck as $groupId)
			{
				$groupItem = \Bitrix\Socialnetwork\Item\Workgroup::getById($groupId, false);
				$groupItem->syncDeptConnection();
			}
		}

		return true;

	}

	private static function disconnectSection($sectionId)
	{
		$groupList = array();
		$res = WorkgroupTable::getList(array(
			'filter' => array(
				'=UF_SG_DEPT' => $sectionId
			),
			'select' => array('ID', 'UF_SG_DEPT')
		));
		while($group = $res->fetch())
		{
			$groupList[] = $group;
		}

		foreach($groupList as $group)
		{
			$departmentListOld = array_map('intval',  $group['UF_SG_DEPT']);
			$departmentListNew = array_diff($departmentListOld, array($sectionId));

			\CSocNetGroup::update($group['ID'], array(
				'UF_SG_DEPT' => $departmentListNew
			));

			$groupItem = \Bitrix\Socialnetwork\Item\Workgroup::getById($group['ID'], false);
			$groupItem->syncDeptConnection();
		}
	}

	public static function getTypes($params = array())
	{
		static $intranetInstalled = null;
		static $extranetInstalled = null;

		if ($intranetInstalled === null)
		{
			$intranetInstalled = ModuleManager::isModuleInstalled('intranet');
		}

		if ($extranetInstalled === null)
		{
			$extranetInstalled = (
				ModuleManager::isModuleInstalled('extranet')
				&& strlen(Option::get("extranet", "extranet_site")) > 0
			);
		}

		$currentExtranetSite = (
			!empty($params)
			&& isset($params["currentExtranetSite"])
			&& $params["currentExtranetSite"]
		);

		$categoryList = (
			!empty($params)
			&& is_array($params["category"])
			&& !empty($params["category"])
				? $params["category"]
				: array()
		);

		$result = array();

		if (
			$intranetInstalled
			&& (
				empty($categoryList)
				|| in_array('projects', $categoryList)
			)
		)
		{
			if (!$currentExtranetSite)
			{
				$result['project-open'] = array(
					'SORT' => '10',
					'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_OPEN'),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_OPEN_DESC'),
					'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_OPEN_DESC2'),
					'VISIBLE' => 'Y',
					'OPENED' => 'Y',
					'PROJECT' => 'Y',
					'EXTERNAL' => 'N',
					'TILE_CLASS' => 'social-group-tile-item-cover-open social-group-tile-item-icon-project-open'
				);
				$result['project-closed'] = array(
					'SORT' => '20',
					'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_CLOSED'),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_CLOSED_DESC'),
					'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_CLOSED_DESC'),
					'VISIBLE' => 'N',
					'OPENED' => 'N',
					'PROJECT' => 'Y',
					'EXTERNAL' => 'N',
					'TILE_CLASS' => 'social-group-tile-item-cover-close social-group-tile-item-icon-project-close'
				);
			}

			if ($extranetInstalled)
			{
				$result['project-external'] = array(
					'SORT' => '30',
					'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_EXTERNAL'),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_EXTERNAL_DESC'),
					'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_EXTERNAL_DESC'),
					'VISIBLE' => 'N',
					'OPENED' => 'N',
					'PROJECT' => 'Y',
					'EXTERNAL' => 'Y',
					'TILE_CLASS' => 'social-group-tile-item-cover-outer social-group-tile-item-icon-project-outer'
				);
			}
		}

		if (
			!$currentExtranetSite
			&& (
				empty($categoryList)
				|| in_array('groups', $categoryList)
			)
		)
		{
			$result['group-open'] = array(
				'SORT' => '40',
				'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_OPEN'),
				'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_OPEN_DESC'),
				'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_OPEN_DESC2'),
				'VISIBLE' => 'Y',
				'OPENED' => 'Y',
				'PROJECT' => 'N',
				'EXTERNAL' => 'N',
				'TILE_CLASS' => 'social-group-tile-item-cover-open social-group-tile-item-icon-group-open'
			);
			$result['group-closed'] = array(
				'SORT' => '40',
				'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED'),
				'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED_DESC'),
				'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED_DESC'),
				'VISIBLE' => 'N',
				'OPENED' => 'N',
				'PROJECT' => 'N',
				'EXTERNAL' => 'N',
				'TILE_CLASS' => 'social-group-tile-item-cover-close social-group-tile-item-icon-group-close'
			);
		}

		if (
			$extranetInstalled
			&& (
				empty($categoryList)
				|| in_array('groups', $categoryList)
			)
		)
		{
			$result['group-external'] = array(
				'SORT' => '40',
				'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_EXTERNAL'),
				'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_EXTERNAL_DESC'),
				'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_EXTERNAL_DESC'),
				'VISIBLE' => 'N',
				'OPENED' => 'N',
				'PROJECT' => 'N',
				'EXTERNAL' => 'Y',
				'TILE_CLASS' => 'social-group-tile-item-cover-outer social-group-tile-item-icon-group-outer'
			);
		}

		return $result;
	}

	public static function getTypeCodeByParams($params)
	{
		$result = false;

		if (empty($params['fields']))
		{
			return $result;
		}

		$typesList = (
			!empty($params['typesList'])
				? $params['typesList']
				: self::getTypes($params)
		);

		foreach($typesList as $code => $type)
		{
			if (
				$params['fields']['VISIBLE'] == $type['VISIBLE']
				&& $params['fields']['PROJECT'] == $type['PROJECT']
				&& $params['fields']['EXTERNAL'] == $type['EXTERNAL']
			)
			{
				$result = $code;
				break;
			}
		}

		return $result;
	}

	public static function getTypeByCode($params = array())
	{
		$result = false;

		if (
			!is_array($params)
			|| empty($params['code'])
		)
		{
			return $result;
		}

		$code = $params['code'];
		$typesList = (
			!empty($params['typesList'])
				? $params['typesList']
				: self::getTypes($params)
		);

		if (
			!empty($typesList)
			&& is_array($typesList)
			&& !empty($typesList[$code])
		)
		{
			$result = $typesList[$code];
		}

		return $result;
	}

	private static function getGroupContent($params = array())
	{
		static $fieldsList = null;;

		$content = '';

		$groupId = (isset($params['id']) ? intval($params['id']) : 0);

		if ($groupId <= 0)
		{
			return $content;
		}

		if ($fieldsList === null)
		{
			$fieldsList = self::getContentFieldsList();
		}

		if (
			isset($params['fields'])
			&& is_array($params['fields'])
			&& ($diff = array_diff($fieldsList, array_keys($params['fields'])))
			&& empty($diff)
		)
		{
			$groupFieldsList = $params['fields'];
		}
		else
		{
			$res = WorkgroupTable::getList(array(
				'filter' => array(
					'ID' => $groupId
				),
				'select' => $fieldsList
			));
			$groupFieldsList = $res->fetch();
		}

		if (!empty($groupFieldsList))
		{
			$content .= $groupFieldsList['NAME'];
			if (!empty($groupFieldsList['DESCRIPTION']))
			{
				$content .= ' '.$groupFieldsList['DESCRIPTION'];
			}

			if (!empty($groupFieldsList['KEYWORDS']))
			{
				$keywordList = explode(",", $groupFieldsList["KEYWORDS"]);
				$tagList = array();
				foreach($keywordList as $keyword)
				{
					$tagList[] = trim($keyword);
					$tagList[] = '#'.trim($keyword);
				}
				if (!empty($tagList))
				{
					$content .= ' '.join(' ', $tagList);
				}
			}

			if (
				!empty($groupFieldsList['OWNER_ID'])
				&& intval($groupFieldsList['OWNER_ID']) > 0
			)
			{
				$res = Main\UserTable::getList(array(
					'filter' => array(
						'ID' => intval($groupFieldsList['OWNER_ID'])
					),
					'select' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'EMAIL')
				));
				if ($userFields = $res->fetch())
				{
					$content .= ' '.\CUser::formatName(\CSite::getNameFormat(null, $groupFieldsList['SITE_ID']), $userFields, true);
				}
			}
		}

		return $content;
	}

	public static function setIndex($params = array())
	{
		global $DB;

		static $connection = null;

		if (!is_array($params))
		{
			return;
		}

		$fields = (isset($params['fields']) ? $params['fields'] : array());

		if (
			!is_array($fields)
			|| empty($fields)
		)
		{
			return;
		}

		$groupId = (isset($fields['ID']) ? intval($fields['ID']) : 0);

		if ($groupId <= 0)
		{
			return;
		}

		$content = self::getGroupContent(array(
			'id' => $groupId,
			'fields' => $fields
		));

		$content = self::prepareToken($content);

		$event = new Main\Event(
			'socialnetwork',
			'onWorkgroupIndexGetContent',
			array(
				'groupId' => $groupId,
			)
		);
		$event->send();

		foreach($event->getResults() as $eventResult)
		{
			if($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS)
			{
				$eventParams = $eventResult->getParameters();

				if (
					is_array($eventParams)
					&& isset($eventParams['content'])
				)
				{
					$eventContent = $eventParams['content'];
					if (Main\Loader::includeModule('search'))
					{
						$eventContent = \CSearch::killTags($content);
					}
					$eventContent = trim(str_replace(
						array("\r", "\n", "\t"),
						" ",
						$eventContent
					));

					$eventContent = self::prepareToken($eventContent);
					if (!empty($eventContent))
					{
						$content .= ' '.$eventContent;
					}
				}
			}
		}

		if (!empty($content))
		{
			if ($connection === null)
			{
				$connection = \Bitrix\Main\Application::getConnection();
			}

			$connection->query("UPDATE ".WorkgroupTable::getTableName()." SET SEARCH_INDEX = '{$DB->forSql($content)}' WHERE ID = {$groupId}");
		}
	}

	public static function getContentFieldsList()
	{
		return array('NAME', 'DESCRIPTION', 'OWNER_ID', 'KEYWORDS', 'SITE_ID');
	}

	public static function prepareToken($str)
	{
		return str_rot13($str);
	}

	public static function getInitiatePermOptionsList($params = array())
	{
		$project = (
			is_array($params)
			&& isset($params['project'])
			&& $params['project']
		);

		return array(
			UserToGroupTable::ROLE_OWNER => Loc::getMessage($project ? "SOCIALNETWORK_ITEM_WORKGROUP_IP_OWNER_PROJECT" : "SOCIALNETWORK_ITEM_WORKGROUP_IP_OWNER"),
			UserToGroupTable::ROLE_MODERATOR => Loc::getMessage($project ? "SOCIALNETWORK_ITEM_WORKGROUP_IP_MOD_PROJECT" : "SOCIALNETWORK_ITEM_WORKGROUP_IP_MOD"),
			UserToGroupTable::ROLE_USER => GetMessage($project ? "SOCIALNETWORK_ITEM_WORKGROUP_IP_USER_PROJECT" : "SOCIALNETWORK_ITEM_WORKGROUP_IP_USER"),
		);
	}

	public static function getEditFeaturesAvailability()
	{
		static $result = null;

		if ($result !== null)
		{
			return $result;
		}

		$result = true;

		if (!ModuleManager::isModuleInstalled('bitrix24'))
		{
			return $result;
		}

		if (\CBitrix24::isNfrLicense())
		{
			return $result;
		}

		if (\CBitrix24::isDemoLicense())
		{
			return $result;
		}

		if (
			!in_array(\CBitrix24::getLicenseType(), array('project'), true)
			|| !Option::get('socialnetwork', 'demo_edit_features', false)
		)
		{

			return $result;
		}

		$result = false;

		return $result;
	}
}
