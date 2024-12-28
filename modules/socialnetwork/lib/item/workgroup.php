<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Main;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Socialnetwork\Internals\Registry\GroupRegistry;
use Bitrix\Socialnetwork\Item\Workgroup\Type;
use Bitrix\Socialnetwork\Integration\Intranet\Structure\WorkgroupDepartmentsSynchronizer;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\Helper;

Loc::loadMessages(__FILE__);

class Workgroup implements Main\Type\Contract\Arrayable, Main\Type\Contract\Jsonable
{
	protected array $fields;
	protected static $groupsIdToCheckList = [];

	public static function createFromId(int $groupId = 0): static
	{
		return new static(['ID' => $groupId]);
	}

	public function __construct(array $fields = [])
	{
		$this->fields = $fields;
	}

	/**
	 * @use GroupRegistry::get
	 *
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public static function getById($groupId = 0, $useCache = true): bool|static
	{
		$groupId = (int)$groupId;

		if ($groupId <= 0)
		{
			return false;
		}

		$useCache = (bool)$useCache;

		$registry = GroupRegistry::getInstance();

		if (!$useCache)
		{
			$registry->invalidate($groupId);
		}

		$group = $registry->get($groupId);

		if ($group === null)
		{
			return false; // disgusting! compatability...
		}

		return $group;
	}

	public function setFields(array $fields = []): void
	{
		$this->fields = $fields;
	}

	public function getFields(): array
	{
		return $this->fields;
	}

	public function getId(): int
	{
		return (int)($this->fields['ID'] ?? 0);
	}

	public function getName(): string
	{
		return (string)($this->fields['NAME'] ?? '');
	}

	public function getDescription(): string
	{
		return (string)($this->fields['DESCRIPTION'] ?? '');
	}

	public function getChatId(): int
	{
		return (int)($this->fields['CHAT_ID'] ?? 0);
	}

	public function getDialogId(): string
	{
		return (string)($this->fields['DIALOG_ID'] ?? '');
	}

	public function getImageId(): int
	{
		return (int)($this->fields['IMAGE_ID'] ?? 0);
	}

	public function getOwnerId(): int
	{
		return (int)($this->fields['OWNER_ID'] ?? 0);
	}

	public function getSiteId(): string
	{
		return (string)($this->fields['SITE_ID'] ?? '');
	}

	public function getSiteIds(): array
	{
		return (array)($this->fields['SITE_IDS'] ?? []);
	}

	public function getType(): ?Type
	{
		$type = $this->fields['TYPE'] ?? null;
		if ($type instanceof Type)
		{
			return $type;
		}

		return Type::tryFrom($type);
	}

	public function getSynchronizedDepartmentIds(): array
	{
		$departments = $this->fields['UF_SG_DEPT'] ?? [];
		if ($departments === [])
		{
			return [];
		}

		$departmentIds = $departments['VALUE'] ?? [];
		if (empty($departmentIds))
		{
			return [];
		}

		Main\Type\Collection::normalizeArrayValuesByInt($departmentIds, false);

		return $departmentIds;
	}

	public function getUserMemberIds(): array
	{
		$memberIds = $this->fields['MEMBERS'] ?? [];
		if ($memberIds === [])
		{
			return [];
		}

		Main\Type\Collection::normalizeArrayValuesByInt($memberIds, false);

		return $memberIds;
	}

	public function getInvitedMemberIds(): array
	{
		$requestedIds = $this->fields['INVITED_MEMBERS'] ?? [];
		if ($requestedIds === [])
		{
			return [];
		}

		Main\Type\Collection::normalizeArrayValuesByInt($requestedIds, false);

		return $requestedIds;
	}

	public function getModeratorMemberIds(): array
	{
		$moderatorIds = $this->fields['MODERATOR_MEMBERS'] ?? [];
		if ($moderatorIds === [])
		{
			return [];
		}

		Main\Type\Collection::normalizeArrayValuesByInt($moderatorIds, false);

		return $moderatorIds;
	}

	public function getOrdinaryMembers(): array
	{
		$ordinaryMemberIds = $this->fields['ORDINARY_MEMBERS'] ?? [];
		if ($ordinaryMemberIds === [])
		{
			return [];
		}

		Main\Type\Collection::normalizeArrayValuesByInt($ordinaryMemberIds, false);

		return $ordinaryMemberIds;
	}

	public function getMemberIdsWithRole(): array
	{
		$invited = array_fill_keys($this->getInvitedMemberIds(), UserToGroupTable::ROLE_REQUEST);
		$ordinary = array_fill_keys($this->getOrdinaryMembers(), UserToGroupTable::ROLE_USER);
		$moderators = array_fill_keys($this->getModeratorMemberIds(), UserToGroupTable::ROLE_MODERATOR);
		$owner = [$this->getOwnerId() => UserToGroupTable::ROLE_OWNER];

		return $invited + $ordinary + $moderators + $owner;
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

	public function isCollab(): bool
	{
		return $this->getType() === Type::Collab;
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

	/**
	 * @deprecated
	 * @use WorkgroupDeptSynchronizer::syncDeptConnection
	 */
	public function syncDeptConnection($exclude = false): void
	{
		$currentUserId = (int)CurrentUser::get()->getId();

		WorkgroupDepartmentsSynchronizer::getInstance()->synchronize($this, $currentUserId, $exclude);
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
				'SITE_ID' => $groupSiteId,
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
			'DOMAIN' => $domainName,
		];
	}

	public function toArray(): array
	{
		return $this->getFields();
	}

	public function toJson($options = 0): array
	{
		return $this->toArray();
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
				'=UF_SG_DEPT' => $sectionId,
			),
			'select' => array('ID', 'UF_SG_DEPT'),
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
				'UF_SG_DEPT' => $departmentListNew,
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
					'ID' => $groupId,
				),
				'select' => $fieldsList,
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
						'ID' => (int)$groupFieldsList['OWNER_ID'],
					),
					'select' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'EMAIL'),
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
			'fields' => $fields,
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

			$connection->query("UPDATE ".WorkgroupTable::getTableName()." SET SEARCH_INDEX = CASE WHEN " . $connection->getSqlHelper()->getSha1Function('SEARCH_INDEX') . " = '{$encryptedValue}' THEN SEARCH_INDEX ELSE '{$value}' END WHERE ID = {$groupId}");
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
}
