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
use Bitrix\Socialnetwork\Helper;

Loc::loadMessages(__FILE__);

class Workgroup
{
	protected const UF_ENTITY_ID = 'SONET_GROUP';

	private $fields;
	protected static $groupsIdToCheckList = [];

	public function __construct()
	{
		$this->fields = array();
	}

	public static function getById($groupId = 0, $useCache = true)
	{
		global $USER_FIELD_MANAGER;

		static $cachedFields = [];

		$groupItem = false;
		$groupId = (int)$groupId;

		if ($groupId > 0)
		{
			$groupItem = new Workgroup;
			$groupFields = [];

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

	public function setFields($fields = array()): void
	{
		$this->fields = $fields;
	}

	public function getFields(): array
	{
		return $this->fields;
	}

	public function isProject(): bool
	{
		return (
			isset($this->fields['PROJECT'])
			&& $this->fields['PROJECT'] === 'Y'
		);
	}

	public function isScrumProject(): bool
	{
		return (!empty($this->fields['SCRUM_MASTER_ID']));
	}

	public function getDefaultSprintDuration(): int
	{
		return ($this->fields['SCRUM_SPRINT_DURATION'] ? : 0);
	}

	public function getScrumMaster(): int
	{
		return ($this->fields['SCRUM_MASTER_ID'] ? : 0);
	}

	public function getScrumTaskResponsible(): string
	{
		if ($this->fields['SCRUM_TASK_RESPONSIBLE'])
		{
			$scrumTaskResponsible = $this->fields['SCRUM_TASK_RESPONSIBLE'];
			$availableResponsibleTypes = ['A', 'M'];
			return (
				in_array($scrumTaskResponsible, $availableResponsibleTypes, true) ? $scrumTaskResponsible : 'A'
			);
		}

		return 'A';
	}

	public function syncDeptConnection($exclude = false): void
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
			isset($groupFields['UF_SG_DEPT']['VALUE'])
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
			$workgroupsToSync = $this->reduceSyncList($workgroupsToSync);
			Option::set('socialnetwork', 'workgroupsToSync', serialize($workgroupsToSync));
			\Bitrix\Socialnetwork\Update\WorkgroupDeptSync::bind(1);
		}
	}

	public function getGroupUrlData($params = array())
	{
		static $cache = array();

		$groupFields = $this->getFields();
		$userId = (int)($params['USER_ID'] ?? 0);

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
			$groupUrlTemplate = Helper\Path::get('group_path_template');
			$groupUrlTemplate = "#GROUPS_PATH#".mb_substr($groupUrlTemplate, mb_strlen($workgroupsPage), mb_strlen($groupUrlTemplate) - mb_strlen($workgroupsPage));

			$cache[$groupFields["ID"]] = array(
				'URL_TEMPLATE' => $groupUrlTemplate ,
				'SITE_ID' => $groupSiteId
			);
		}

		$groupUrl = str_replace(array("#group_id#", "#GROUP_ID#"), $groupFields["ID"], $groupUrlTemplate);
		$serverName = $domainName = '';

		if ($userId > 0)
		{
			$tmp = \CSocNetLogTools::processPath(
				[
					'GROUP_URL' => $groupUrl,
				],
				$userId,
				$groupSiteId
			);

			$groupUrl = $tmp["URLS"]["GROUP_URL"];
			$serverName = (mb_strpos($groupUrl, "http://") === 0 || mb_strpos($groupUrl, "https://") === 0 ? "" : $tmp["SERVER_NAME"]);
			$domainName = (mb_strpos($groupUrl, "http://") === 0 || mb_strpos($groupUrl, "https://") === 0 ? "" : (isset($tmp["DOMAIN"]) && !empty($tmp["DOMAIN"]) ? "//".$tmp["DOMAIN"] : ""));
		}

		return [
			'URL' => $groupUrl,
			'SERVER_NAME' => $serverName,
			'DOMAIN' => $domainName
		];
	}

	public static function onBeforeIBlockSectionUpdate($section)
	{
		if (
			!isset($section['ID'], $section['IBLOCK_ID'])
			|| (int)$section['ID'] <= 0
			|| (int)$section['IBLOCK_ID'] <= 0
			|| (
				isset($section['ACTIVE'])
				&& $section['ACTIVE'] === 'N'
			)
			|| (int)$section['IBLOCK_ID'] !== (int)Option::get('intranet', 'iblock_structure', 0)
		)
		{
			return true;
		}

		$rootSectionIdList = [];
		$res = \CIBlockSection::getNavChain($section['IBLOCK_ID'], $section['ID'], array('ID'));
		while ($rootSection = $res->fetch())
		{
			if ((int)$rootSection['ID'] !== (int)$section['ID'])
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
			!isset($section['ID'], $section['IBLOCK_ID'])
			|| (int)$section['ID'] <= 0
			|| (int)$section['IBLOCK_ID'] <= 0
			|| (int)$section['IBLOCK_ID'] !== (int)Option::get('intranet', 'iblock_structure', 0)
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
				if ((int)$rootSection['ID'] !== (int)$section['ID'])
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
		if ((int)$sectionId <= 0)
		{
			return true;
		}

		$res = \CIBlockSection::getList(
			[],
			[ 'ID' => $sectionId ],
			false,
			[ 'ID', 'IBLOCK_ID' ]
		);
		if (
			!($section = $res->fetch())
			|| !isset($section['IBLOCK_ID'])
			|| (int)$section['IBLOCK_ID'] <= 0
			|| (
				isset($section['ACTIVE'])
				&& $section['ACTIVE'] === 'N'
			)
			|| (int)$section['IBLOCK_ID'] !== (int)Option::get('intranet', 'iblock_structure', 0)
		)
		{
			return true;
		}

		$rootSectionIdList = [];
		$res = \CIBlockSection::getNavChain($section['IBLOCK_ID'], $section['ID'], array('ID'));
		while ($rootSection = $res->fetch())
		{
			if ((int)$rootSection['ID'] !== (int)$section['ID'])
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

	public static function onAfterIBlockSectionDelete($section): bool
	{
		if(
			!isset($section['ID'], $section['IBLOCK_ID'])
			|| (int)$section['ID'] <= 0
			|| (int)$section['IBLOCK_ID'] <= 0
			|| (int)$section['IBLOCK_ID'] !== (int)Option::get('intranet', 'iblock_structure', 0)
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

	private static function disconnectSection($sectionId): void
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

	public static function getTypes($params = []): array
	{
		return Helper\Workgroup::getTypes($params);
	}

	public static function getPresets($params = array()): array
	{
		return Helper\Workgroup::getPresets($params);
	}

	private static function getGroupContent($params = array()): string
	{
		static $fieldsList = null;;

		$content = '';

		$groupId = (int)($params['id'] ?? 0);

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

	public static function setIndex($params = array()): void
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
			if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS)
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
						$eventContent = \CSearch::killTags($eventContent);
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

	public static function getContentFieldsList(): array
	{
		return [ 'NAME', 'DESCRIPTION', 'OWNER_ID', 'KEYWORDS', 'SITE_ID' ];
	}

	public static function prepareToken($str)
	{
		return str_rot13($str);
	}

	public static function getInitiatePermOptionsList(array $params = []): array
	{
		$ownerValue = Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_IP_OWNER');
		$moderatorsValue = Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_IP_MOD');
		$userValue = Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_IP_USER');

		if (
			isset($params['scrum'])
			&& $params['scrum']
		)
		{
			$ownerValue = Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_IP_OWNER_SCRUM2');
			$moderatorsValue = Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_IP_MOD_SCRUM2');
			$userValue = Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_IP_USER_SCRUM');
		}
		elseif (
			isset($params['project'])
			&& $params['project']
		)
		{
			$ownerValue = Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_IP_OWNER_PROJECT');
			$moderatorsValue = Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_IP_MOD_PROJECT');
			$userValue = Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_IP_USER_PROJECT');
		}

		return [
			UserToGroupTable::ROLE_OWNER => $ownerValue,
			UserToGroupTable::ROLE_MODERATOR => $moderatorsValue,
			UserToGroupTable::ROLE_USER => $userValue,
		];
	}

	public static function getSpamPermOptionsList(): array
	{
		return [
			UserToGroupTable::ROLE_OWNER => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_IP_OWNER'),
			UserToGroupTable::ROLE_MODERATOR => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_IP_MOD'),
			UserToGroupTable::ROLE_USER => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_IP_USER'),
			SONET_ROLES_ALL => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_IP_ALL'),
		];
	}

	/**
	 * returns array of workgroups filtered by access permissions of a user, only for the current site
	 * @param array $params
	 * @return array
	 */
	public static function getByFeatureOperation(array $params = []): array
	{
		return Helper\Workgroup::getByFeatureOperation($params);
	}

	public static function getListSprintDuration(): array
	{
		return Helper\Workgroup::getListSprintDuration();
	}

	public static function getScrumTaskResponsibleList(): array
	{
		return Helper\Workgroup::getScrumTaskResponsibleList();
	}

	public static function getTypeCodeByParams($params)
	{
		return Helper\Workgroup::getTypeCodeByParams($params);
	}

	public static function getTypeByCode($params = [])
	{
		return Helper\Workgroup::getTypeByCode($params);
	}

	public static function getEditFeaturesAvailability()
	{
		return Helper\Workgroup::getEditFeaturesAvailability();
	}

	public static function canWorkWithClosedWorkgroups(): bool
	{
		static $optionValue = null;
		if ($optionValue === null)
		{
			$optionValue = Option::get('socialnetwork', 'work_with_closed_groups', 'N');
		}

		return ($optionValue === 'Y');
	}

	private function reduceSyncList(array $workgroupsToSync = []): array
	{
		$result = [];

		foreach ($workgroupsToSync as $workgroupData)
		{
			$workgroupId = (int) $workgroupData['groupId'];
			$result[$workgroupId] = $workgroupData;
		}

		return array_values($result);
	}
}
