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
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\FeatureTable;
use Bitrix\Socialnetwork\FeaturePermTable;

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
		$groupId = (int)$groupId;

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
			&& $this->fields['PROJECT'] === 'Y'
		);
	}

	public function isScrumProject(): bool
	{
		return (!empty($this->fields['SCRUM_OWNER_ID']));
	}

	public function getDefaultSprintDuration(): int
	{
		return ($this->fields['SCRUM_SPRINT_DURATION'] ? $this->fields['SCRUM_SPRINT_DURATION'] : 0);
	}

	public function getScrumMaster(): int
	{
		return ($this->fields['SCRUM_MASTER_ID'] ? $this->fields['SCRUM_MASTER_ID'] : 0);
	}

	public function getScrumTaskResponsible(): string
	{
		if ($this->fields['SCRUM_TASK_RESPONSIBLE'])
		{
			$scrumTaskResponsible = $this->fields['SCRUM_TASK_RESPONSIBLE'];
			$availableResponsibleTypes = ['A', 'M'];
			return (
				in_array($scrumTaskResponsible, $availableResponsibleTypes) ? $scrumTaskResponsible : 'A'
			);
		}

		return 'A';
	}

	public static function getListSprintDuration(): array
	{
		$oneWeek = \DateInterval::createFromDateString('1 week')->format('%d') * 86400;
		$twoWeek = \DateInterval::createFromDateString('2 weeks')->format('%d') * 86400;
		$threeWeek = \DateInterval::createFromDateString('3 weeks')->format('%d') * 86400;
		$fourWeek = \DateInterval::createFromDateString('4 weeks')->format('%d') * 86400;

		return [
			$oneWeek => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM_SPRINT_DURATION_ONE_WEEK'),
			$twoWeek => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM_SPRINT_DURATION_TWO_WEEK'),
			$threeWeek => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM_SPRINT_DURATION_THREE_WEEK'),
			$fourWeek => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM_SPRINT_DURATION_FOUR_WEEK'),
		];
	}

	public static function getScrumTaskResponsibleList(): array
	{
		return [
			'A' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM_TASK_RESPONSIBLE_AUTHOR'),
			'M' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM_TASK_RESPONSIBLE_MASTER')
		];
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

	public function syncDeptConnection($exclude = false)
	{
		global $USER;

		if (!ModuleManager::isModuleInstalled('intranet'))
		{
			return;
		}

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
			&& Loader::includeModule('intranet')
		)
		{
			$workgroupsToSync = Option::get('socialnetwork', 'workgroupsToSync', "");
			$workgroupsToSync = ($workgroupsToSync !== "" ? @unserialize($workgroupsToSync, [ 'allowed_classes' => false ]) : []);
			if (!is_array($workgroupsToSync))
			{
				$workgroupsToSync = [];
			}
			$workgroupsToSync[] = array(
				'groupId' => $groupFields["ID"],
				'initiatorId' => (is_object($USER) ? $USER->getId() : $groupFields['OWNER_ID']),
				'exclude' => $exclude
			);
			Option::set('socialnetwork', 'workgroupsToSync', serialize($workgroupsToSync));
			\Bitrix\Socialnetwork\Update\WorkgroupDeptSync::bind(1);
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
			$groupUrlTemplate = "#GROUPS_PATH#".mb_substr($groupUrlTemplate, mb_strlen($workgroupsPage), mb_strlen($groupUrlTemplate) - mb_strlen($workgroupsPage));

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
			$serverName = (mb_strpos($groupUrl, "http://") === 0 || mb_strpos($groupUrl, "https://") === 0 ? "" : $tmp["SERVER_NAME"]);
			$domainName = (mb_strpos($groupUrl, "http://") === 0 || mb_strpos($groupUrl, "https://") === 0 ? "" : (isset($tmp["DOMAIN"]) && !empty($tmp["DOMAIN"]) ? "//".$tmp["DOMAIN"] : ""));
		}

		return array(
			'URL' => $groupUrl,
			'SERVER_NAME' => $serverName,
			'DOMAIN' => $domainName
		);
	}

	public static function onBeforeIBlockSectionUpdate($section)
	{
		if (
			!isset($section['ID'])
			|| (int)$section['ID'] <= 0
			|| !isset($section['IBLOCK_ID'])
			|| (int)$section['IBLOCK_ID'] <= 0
			|| $section['IBLOCK_ID'] != Option::get('intranet', 'iblock_structure', 0)
			|| (
				isset($section['ACTIVE'])
				&& $section['ACTIVE'] === 'N'
			)
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
			|| (int)$section['ID'] <= 0
			|| !isset($section['IBLOCK_ID'])
			|| (int)$section['IBLOCK_ID'] <= 0
			|| $section['IBLOCK_ID'] != Option::get('intranet', 'iblock_structure', 0)
		)
		{
			return true;
		}

		$oldGroupsIdToCheckList = self::$groupsIdToCheckList;
		$newGroupsIdToCheckList = [];

		if (
			isset($section['ACTIVE'])
			&& $section['ACTIVE'] === 'N'
		)
		{
			self::disconnectSection($section['ID']);
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
			}
		}

		if (!empty($oldGroupsIdToCheckList))
		{
			$oldGroupsIdToCheckList = array_unique($oldGroupsIdToCheckList);
			foreach($oldGroupsIdToCheckList as $groupId)
			{
				$groupItem = self::getById($groupId, false);
				$groupItem->syncDeptConnection(true);
			}
		}

		if (!empty($newGroupsIdToCheckList))
		{
			$newGroupsIdToCheckList = array_unique($newGroupsIdToCheckList);
			foreach($newGroupsIdToCheckList as $groupId)
			{
				$groupItem = self::getById($groupId, false);
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
			|| (int)$section['IBLOCK_ID'] <= 0
			|| $section['IBLOCK_ID'] != Option::get('intranet', 'iblock_structure', 0)
			|| (
				isset($section['ACTIVE'])
				&& $section['ACTIVE'] === 'N'
			)
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

	public static function onAfterIBlockSectionDelete($section)
	{
		if(
			!isset($section['ID'])
			|| (int)$section['ID'] <= 0
			|| !isset($section['IBLOCK_ID'])
			|| (int)$section['IBLOCK_ID'] <= 0
			|| $section['IBLOCK_ID'] != Option::get('intranet', 'iblock_structure', 0)
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
				$groupItem = self::getById($groupId, false);
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

			$groupItem = self::getById($group['ID'], false);
			$groupItem->syncDeptConnection(true);
		}
	}

	public static function getTypes($params = array())
	{
		static $intranetInstalled = null;
		static $extranetInstalled = null;
		static $landingInstalled = null;

		if ($intranetInstalled === null)
		{
			$intranetInstalled = ModuleManager::isModuleInstalled('intranet');
		}

		if ($extranetInstalled === null)
		{
			$extranetInstalled = (
				ModuleManager::isModuleInstalled('extranet')
				&& Option::get("extranet", "extranet_site") <> ''
			);
		}

		if ($landingInstalled === null)
		{
			$landingInstalled = ModuleManager::isModuleInstalled('landing');
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

		$entityOptions = (
			!empty($params)
			&& is_array($params['entityOptions'])
			&& !empty($params['entityOptions'])
				? $params['entityOptions']
				: []
		);

		$fullMode = (
			!empty($params)
			&& isset($params["fullMode"])
			&& $params["fullMode"]
		);

		$result = [];
		$sort = 0;

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
				if (self::checkEntityOption([ 'project', 'open', '!extranet', '!landing' ], $entityOptions))
				{
					$result['project-open'] = array(
						'SORT' => $sort = $sort + 10,
						'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_OPEN'),
						'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_OPEN_DESC'),
						'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_OPEN_DESC2'),
						'VISIBLE' => 'Y',
						'OPENED' => 'Y',
						'PROJECT' => 'Y',
						'EXTERNAL' => 'N',
						'TILE_CLASS' => 'social-group-tile-item-cover-open social-group-tile-item-icon-project-open'
					);
				}

				if (self::checkEntityOption([ 'project', '!open', '!extranet', '!landing' ], $entityOptions))
				{
					$result['project-closed'] = array(
						'SORT' => $sort = $sort + 10,
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

				if (
					Option::get('tasks', 'tasks_scrum_enabled', 'N') === 'Y'
					&& self::checkEntityOption([ 'project', 'scrum', '!extranet', '!landing' ], $entityOptions)
				)
				{
					$result['project-scrum'] = [
						'SORT' => $sort = $sort + 10,
						'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM'),
						'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM_DESC'),
						'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM_DESC'),
						'VISIBLE' => 'N',
						'OPENED' => 'N',
						'PROJECT' => 'Y',
						'SCRUM_PROJECT' => 'Y',
						'EXTERNAL' => 'N',
						'TILE_CLASS' => 'social-group-tile-item-cover-scrum social-group-tile-item-icon-project-scrum'
					];
				}

				if (
					$fullMode
					&& self::checkEntityOption([ 'project', '!open', '!extranet', '!landing' ], $entityOptions)
				)
				{
					$result['project-closed-visible'] = array(
						'SORT' => $sort = $sort + 10,
						'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_CLOSED_VISIBLE'),
						'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_CLOSED_VISIBLE_DESC'),
						'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_CLOSED_VISIBLE_DESC'),
						'VISIBLE' => 'Y',
						'OPENED' => 'N',
						'PROJECT' => 'Y',
						'EXTERNAL' => 'N',
						'TILE_CLASS' => ''
					);
				}
			}

			if (
				$extranetInstalled
				&& self::checkEntityOption([ 'project', 'extranet', '!landing' ], $entityOptions)
			)
			{
				$result['project-external'] = array(
					'SORT' => $sort = $sort + 10,
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
			if (self::checkEntityOption([ '!project', 'open', '!extranet', '!landing' ], $entityOptions))
			{
				$result['group-open'] = array(
					'SORT' => $sort = $sort + 10,
					'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_OPEN'),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_OPEN_DESC'),
					'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_OPEN_DESC2'),
					'VISIBLE' => 'Y',
					'OPENED' => 'Y',
					'PROJECT' => 'N',
					'EXTERNAL' => 'N',
					'TILE_CLASS' => 'social-group-tile-item-cover-open social-group-tile-item-icon-group-open'
				);
			}

			if (self::checkEntityOption([ '!project', '!open', '!extranet', '!landing' ], $entityOptions))
			{
				$result['group-closed'] = array(
					'SORT' => $sort = $sort + 10,
					'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED'),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED_DESC'),
					'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED_DESC'),
					'VISIBLE' => 'N',
					'OPENED' => 'N',
					'PROJECT' => 'N',
					'EXTERNAL' => 'N',
					'TILE_CLASS' => 'social-group-tile-item-cover-close social-group-tile-item-icon-group-close'
				);
				if ($fullMode)
				{
					$result['group-closed-visible'] = array(
						'SORT' => $sort = $sort + 10,
						'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED_VISIBLE'),
						'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED_VISIBLE_DESC'),
						'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED_VISIBLE_DESC'),
						'VISIBLE' => 'Y',
						'OPENED' => 'N',
						'PROJECT' => 'N',
						'EXTERNAL' => 'N',
						'TILE_CLASS' => ''
					);
				}
			}
		}

		if (
			$extranetInstalled
			&& self::checkEntityOption([ '!project', 'extranet', '!landing' ], $entityOptions)
		)
		{
			$result['group-external'] = array(
				'SORT' => $sort = $sort + 10,
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

		if (
			$landingInstalled
			&& self::checkEntityOption([ '!project', 'landing', '!extranet' ], $entityOptions)
		)
		{
			$result['group-landing'] = array(
				'SORT' => $sort = $sort + 10,
				'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_LANDING'),
				'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_LANDING_DESC'),
				'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_LANDING_DESC'),
				'VISIBLE' => 'N',
				'OPENED' => 'N',
				'PROJECT' => 'N',
				'EXTERNAL' => 'N',
				'LANDING' => 'Y',
				'TILE_CLASS' => 'social-group-tile-item-cover-public social-group-tile-item-icon-group-public'
			);
		}

		return $result;
	}

	public static function getPresets($params = array())
	{
		static $intranetInstalled = null;
		static $extranetInstalled = null;
		static $landingInstalled = null;

		if ($intranetInstalled === null)
		{
			$intranetInstalled = ModuleManager::isModuleInstalled('intranet');
		}

		if ($extranetInstalled === null)
		{
			$extranetInstalled = (
				ModuleManager::isModuleInstalled('extranet')
				&& Option::get("extranet", "extranet_site") <> ''
			);
		}

		if ($landingInstalled === null)
		{
			$landingInstalled = ModuleManager::isModuleInstalled('landing');
		}

		$currentExtranetSite = (
			!empty($params)
			&& isset($params["currentExtranetSite"])
			&& $params["currentExtranetSite"]
		);

		$entityOptions = (
			!empty($params)
			&& is_array($params['entityOptions'])
			&& !empty($params['entityOptions'])
				? $params['entityOptions']
				: []
		);

		$fullMode = (
			!empty($params)
			&& isset($params["fullMode"])
			&& $params["fullMode"]
		);

		$result = [];
		$sort = 0;

		$useProjects = $intranetInstalled && self::checkEntityOption([ 'project' ], $entityOptions);

		if (!$currentExtranetSite)
		{
			if (self::checkEntityOption([ 'open', '!extranet', '!landing' ], $entityOptions))
			{
				$result['project-open'] = [
					'SORT' => $sort += 10,
					'NAME' => ($intranetInstalled ? Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_OPEN') : Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_OPEN')),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_OPEN_DESC'),
					'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_OPEN_DESC2'),
					'VISIBLE' => 'Y',
					'OPENED' => 'Y',
					'PROJECT' => ($useProjects ? 'Y' : 'N' ),
					'EXTERNAL' => 'N',
					'TILE_CLASS' => 'social-group-tile-item-cover-open ' . ($intranetInstalled ? 'social-group-tile-item-icon-project-open' : 'social-group-tile-item-icon-group-open')
				];
			}

			if (self::checkEntityOption([ '!open', '!extranet', '!landing' ], $entityOptions))
			{
				$result['project-closed'] = [
					'SORT' => $sort += 10,
					'NAME' => ($intranetInstalled ? Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_CLOSED') : Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED')),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_CLOSED_DESC'),
					'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_CLOSED_DESC'),
					'VISIBLE' => 'N',
					'OPENED' => 'N',
					'PROJECT' => ($useProjects ? 'Y' : 'N' ),
					'EXTERNAL' => 'N',
					'TILE_CLASS' => 'social-group-tile-item-cover-close ' . ($intranetInstalled ? 'social-group-tile-item-icon-project-close' : 'social-group-tile-item-icon-group-close')
				];
			}

			if (
				$useProjects
				&& Option::get('tasks', 'tasks_scrum_enabled', 'N') === 'Y'
				&& self::checkEntityOption([ 'project', 'scrum', '!extranet', '!landing' ], $entityOptions)
			)
			{
				$result['project-scrum'] = [
					'SORT' => $sort += 10,
					'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM'),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM_DESC'),
					'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM_DESC'),
					'VISIBLE' => 'N',
					'OPENED' => 'N',
					'PROJECT' => 'Y',
					'SCRUM_PROJECT' => 'Y',
					'EXTERNAL' => 'N',
					'TILE_CLASS' => 'social-group-tile-item-cover-scrum social-group-tile-item-icon-project-scrum'
				];
			}

			if (
				$fullMode
				&& self::checkEntityOption([ '!open', '!extranet', '!landing' ], $entityOptions)
			)
			{
				$result['project-closed-visible'] = [
					'SORT' => $sort += 10,
					'NAME' => ($intranetInstalled ? Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_CLOSED_VISIBLE') : Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED_VISIBLE')),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_CLOSED_VISIBLE_DESC'),
					'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_CLOSED_VISIBLE_DESC'),
					'VISIBLE' => 'Y',
					'OPENED' => 'N',
					'PROJECT' => ($useProjects ? 'Y' : 'N' ),
					'EXTERNAL' => 'N',
					'TILE_CLASS' => ''
				];
			}
		}

		if (
			$extranetInstalled
			&& self::checkEntityOption([ 'extranet', '!landing' ], $entityOptions)
		)
		{
			$result['project-external'] = [
				'SORT' => $sort += 10,
				'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_EXTERNAL'),
				'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_EXTERNAL_DESC'),
				'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_EXTERNAL_DESC'),
				'VISIBLE' => 'N',
				'OPENED' => 'N',
				'PROJECT' => ($useProjects ? 'Y' : 'N' ),
				'EXTERNAL' => 'Y',
				'TILE_CLASS' => 'social-group-tile-item-cover-outer social-group-tile-item-icon-project-outer'
			];
		}

		if (
			$landingInstalled
			&& self::checkEntityOption([ '!project', 'landing', '!extranet' ], $entityOptions)
		)
		{
			$result['group-landing'] = [
				'SORT' => $sort += 10,
				'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_LANDING'),
				'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_LANDING_DESC'),
				'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_LANDING_DESC'),
				'VISIBLE' => 'N',
				'OPENED' => 'N',
				'PROJECT' => 'N',
				'EXTERNAL' => 'N',
				'LANDING' => 'Y',
				'TILE_CLASS' => 'social-group-tile-item-cover-public social-group-tile-item-icon-group-public'
			];
		}

		return $result;
	}

	private static function checkEntityOption(array $keysList = [], array $entityOptions = [])
	{
		$result = true;

		foreach($keysList as $key)
		{
			if (
				!empty($entityOptions)
				&& (
					(
						isset($entityOptions[$key])
						&& !$entityOptions[$key]
					)
					|| (
						preg_match('/^\!(\w+)$/', $key, $matches)
						&& isset($entityOptions[$matches[1]])
						&& $entityOptions[$matches[1]]
					)
				)
			)
			{
				$result = false;
				break;
			}
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
				$params['fields']['OPENED'] == $type['OPENED']
				&& (
					isset($params['fields']['VISIBLE'])
					&& $params['fields']['VISIBLE'] == $type['VISIBLE']
				)
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
					$content .= ' '.implode(' ', $tagList);
				}
			}

			if (
				!empty($groupFieldsList['OWNER_ID'])
				&& (int)$groupFieldsList['OWNER_ID'] > 0
			)
			{
				$res = Main\UserTable::getList(array(
					'filter' => array(
						'ID' => (int)$groupFieldsList['OWNER_ID']
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

			$value = $DB->forSql($content);
			$encryptedValue = sha1($content);

			$connection->query("UPDATE ".WorkgroupTable::getTableName()." SET SEARCH_INDEX = IF(SHA1(SEARCH_INDEX) = '{$encryptedValue}', SEARCH_INDEX, '{$value}') WHERE ID = {$groupId}");
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
			\CBitrix24::getLicenseType() !== 'project'
			|| !Option::get('socialnetwork', 'demo_edit_features', false)
		)
		{

			return $result;
		}

		$result = false;

		return $result;
	}


	/**
	 * returns array of workgroups filtered by access permissions of a user, only for the current site
	 * @param array $params
	 * @return array
	 */
	public static function getByFeatureOperation(array $params = []): array
	{
		global $USER;

		$result = [];

		$feature = (isset($params['feature']) ? $params['feature'] : '');
		$operation = (isset($params['operation']) ? $params['operation'] : '');
		$userId = (isset($params['userId']) ? (int)$params['userId'] : (is_object($USER) && $USER instanceof \CUser ? $USER->getId() : 0));

		if (
			$feature == ''
			|| $operation == ''
			|| $userId <= 0
		)
		{
			return $result;
		}

		$featuresSettings = \CSocNetAllowed::getAllowedFeatures();
		if (
			empty($featuresSettings)
			|| empty($featuresSettings[$feature])
			|| empty($featuresSettings[$feature]['allowed'])
			|| !in_array(FeatureTable::FEATURE_ENTITY_TYPE_GROUP, $featuresSettings[$feature]['allowed'])
			|| empty($featuresSettings[$feature]['operations'])
			|| empty($featuresSettings[$feature]['operations'][$operation])
			|| empty($featuresSettings[$feature]['operations'][$operation][FeatureTable::FEATURE_ENTITY_TYPE_GROUP])
		)
		{
			return $result;
		}

		$defaultRole = $featuresSettings[$feature]['operations'][$operation][FeatureTable::FEATURE_ENTITY_TYPE_GROUP];

		$query = new \Bitrix\Main\Entity\Query(WorkgroupTable::getEntity());
		$query->addFilter('=ACTIVE', 'Y');

		if (
			(
				!is_array($featuresSettings[$feature]['minoperation'])
				|| !in_array($operation, $featuresSettings[$feature]['minoperation'])
			)
			&& Option::get('socialnetwork', 'work_with_closed_groups', 'N') !== 'Y'
		)
		{
			$query->addFilter('!=CLOSED', 'Y');
		}

		$query->addSelect('ID');

		$query->registerRuntimeField(
			'',
			new \Bitrix\Main\Entity\ReferenceField('F',
				FeatureTable::getEntity(),
				[
					'=ref.ENTITY_TYPE' => new SqlExpression('?s', FeatureTable::FEATURE_ENTITY_TYPE_GROUP),
					'=ref.ENTITY_ID' => 'this.ID',
					'=ref.FEATURE' => new SqlExpression('?s', $feature),
				],
				[ 'join_type' => 'LEFT' ]
			)
		);
		$query->addSelect('F.ID', 'FEATURE_ID');

		$query->registerRuntimeField(
			'',
			new \Bitrix\Main\Entity\ReferenceField('FP',
				FeaturePermTable::getEntity(),
				[
					'=ref.FEATURE_ID' => 'this.FEATURE_ID',
					'=ref.OPERATION_ID' => new SqlExpression('?s', $operation),
				],
				[ 'join_type' => 'LEFT' ]
			)
		);

		$query->registerRuntimeField(new \Bitrix\Main\Entity\ExpressionField(
			'PERM_ROLE_CALCULATED',
			'CASE WHEN %s IS NULL THEN \''.$defaultRole.'\' ELSE %s END',
			[ 'FP.ROLE', 'FP.ROLE' ]
		));

		$query->registerRuntimeField(
			'',
			new \Bitrix\Main\Entity\ReferenceField('UG',
				UserToGroupTable::getEntity(),
				[
					'=ref.GROUP_ID' => 'this.ID',
					'=ref.USER_ID' => new \Bitrix\Main\DB\SqlExpression($userId),
				],
				[ 'join_type' => 'LEFT' ]
			)
		);

		$query->registerRuntimeField(new \Bitrix\Main\Entity\ExpressionField(
			'HAS_ACCESS',
			'CASE 
				WHEN 
					(
						%s NOT IN (\''.FeaturePermTable::PERM_OWNER.'\', \''.FeaturePermTable::PERM_MODERATOR.'\', \''.FeaturePermTable::PERM_USER.'\')
						OR %s >= %s
					) THEN \'Y\' 
					ELSE \'N\' 
			END',
			[
				'PERM_ROLE_CALCULATED',
				'PERM_ROLE_CALCULATED', 'UG.ROLE'
			]
		));

		$query->addFilter('=HAS_ACCESS', 'Y');

		$res = $query->exec();

		while ($row = $res->fetch())
		{
			$result[] = [
				'ID' => (int) $row['ID']
			];
		}

		return $result;
	}
}
