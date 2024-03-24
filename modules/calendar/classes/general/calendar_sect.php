<?

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\Model\SectionModel;
use Bitrix\Calendar\Access\SectionAccessController;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Calendar\Sync\Factories\FactoriesCollection;
use Bitrix\Calendar\Sync\Factories\FactoryInterface;
use Bitrix\Calendar\Sync\Google\Dictionary;
use Bitrix\Calendar\Sync\Managers\Synchronization;
use Bitrix\Calendar\Sync\Util\Context;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Calendar\UserSettings;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Calendar\Util;
use Bitrix\Calendar\Rooms;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Security\Random;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\Emoji;

class CCalendarSect
{
	public const EXTERNAL_TYPE_LOCAL = 'local';
	public const OPERATION_VIEW_TIME = 'calendar_view_time';
	public const OPERATION_VIEW_TITLE = 'calendar_view_title';
	public const OPERATION_VIEW_FULL = 'calendar_view_full';
	public const OPERATION_ADD = 'calendar_add';
	public const OPERATION_EDIT = 'calendar_edit';
	public const OPERATION_EDIT_SECTION = 'calendar_edit_section';
	public const OPERATION_EDIT_ACCESS = 'calendar_edit_access';

	public static $sendPush = true;

	private static
		$sections,
		$Permissions = [],
		$userSectionPermissions = [],
		$arOp = [],
		$bClearOperationCache = false,
		$authHashiCal = null, // for login by hash
		$Fields = [],
		$useOrmFilter = true
	;

	private static function GetFields()
	{
		global $DB;
		if (empty(self::$Fields))
		{
			self::$Fields = [
				"ID" => ["FIELD_NAME" => "CS.ID", "FIELD_TYPE" => "int"],
				"NAME" => ["FIELD_NAME" => "CS.NAME", "FIELD_TYPE" => "string"],
				"XML_ID" => ["FIELD_NAME" => "CS.XML_ID", "FIELD_TYPE" => "string"],
				"EXTERNAL_ID" => ["FIELD_NAME" => "CS.EXTERNAL_ID", "FIELD_TYPE" => "string"],
				"ACTIVE" => ["FIELD_NAME" => "CS.ACTIVE", "FIELD_TYPE" => "string"],
				"COLOR" => ["FIELD_NAME" => "CS.COLOR", "FIELD_TYPE" => "string"],
				"SORT" => ["FIELD_NAME" => "CS.SORT", "FIELD_TYPE" => "int"],
				"CAL_TYPE" => ["FIELD_NAME" => "CS.CAL_TYPE", "FIELD_TYPE" => "string", "PROCENT" => "N"],
				"OWNER_ID" => ["FIELD_NAME" => "CS.OWNER_ID", "FIELD_TYPE" => "int"],
				"CREATED_BY" => ["FIELD_NAME" => "CS.CREATED_BY", "FIELD_TYPE" => "int"],
				"PARENT_ID" => ["FIELD_NAME" => "CS.PARENT_ID", "FIELD_TYPE" => "int"],
				"TIMESTAMP_X" => [
					"~FIELD_NAME" => "CS.TIMESTAMP_X",
					"FIELD_NAME" => $DB->DateToCharFunction("CS.TIMESTAMP_X") . ' as TIMESTAMP_X',
					"FIELD_TYPE" => "date"
				],
				"DATE_CREATE" => [
					"~FIELD_NAME" => "CS.DATE_CREATE",
					"FIELD_NAME" => $DB->DateToCharFunction("CS.DATE_CREATE") . ' as DATE_CREATE',
					"FIELD_TYPE" => "date"
				],
				"DAV_EXCH_CAL" => ["FIELD_NAME" => "CS.DAV_EXCH_CAL", "FIELD_TYPE" => "string"],
				// Exchange calendar
				"DAV_EXCH_MOD" => ["FIELD_NAME" => "CS.DAV_EXCH_MOD", "FIELD_TYPE" => "string"],
				// Exchange calendar modification label
				"CAL_DAV_CON" => ["FIELD_NAME" => "CS.CAL_DAV_CON", "FIELD_TYPE" => "string"],
				// CalDAV connection
				"CAL_DAV_CAL" => ["FIELD_NAME" => "CS.CAL_DAV_CAL", "FIELD_TYPE" => "string"],
				// CalDAV calendar
				"CAL_DAV_MOD" => ["FIELD_NAME" => "CS.CAL_DAV_MOD", "FIELD_TYPE" => "string"],
				// CalDAV calendar modification label
				"IS_EXCHANGE" => ["FIELD_NAME" => "CS.IS_EXCHANGE", "FIELD_TYPE" => "string"],
				"SYNC_TOKEN" => ["FIELD_NAME" => "CS.SYNC_TOKEN", "FIELD_TYPE" => "string"],
				"PAGE_TOKEN" => ["FIELD_NAME" => "CS.PAGE_TOKEN", "FIELD_TYPE" => "string"],
			];
		}
		return self::$Fields;
	}

	private static function getSectionFields(): array
	{
		return [
			'ID',
			'NAME',
			'XML_ID',
			'EXTERNAL_ID',
			'ACTIVE',
			'COLOR',
			'SORT',
			'CAL_TYPE',
			'OWNER_ID',
			'CREATED_BY',
			'PARENT_ID',
			'TIMESTAMP_X',
			'DATE_CREATE',
			'DAV_EXCH_CAL',
			'DAV_EXCH_MOD',
			'CAL_DAV_CON',
			'CAL_DAV_CAL',
			'CAL_DAV_MOD',
			'IS_EXCHANGE',
			'SYNC_TOKEN',
			'PAGE_TOKEN',
			'GAPI_CALENDAR_ID',
		];
	}

	public static function GetList($params = [])
	{
		$result = false;
		$checkPermissions = ($params['checkPermissions'] ?? null) !== false;
		$params['joinTypeInfo'] = (bool)($params['joinTypeInfo'] ?? null);
		$params['checkPermissions'] = $checkPermissions;
		$params['getPermissions'] = ($params['getPermissions'] ?? null) !== false;
		$userId = ($params['userId'] ?? false) ? (int)$params['userId'] : CCalendar::GetCurUserId();
		$params['userId'] = $userId;
		$cacheEnabled = CCalendar::CacheTime() > 0;

		if ($cacheEnabled)
		{
			$cache = new CPHPCache;
			$cacheId = 'section_list_'.serialize($params).(CCalendar::IsSocnetAdmin() ? 'socnet_admin' : '');
			$cachePath = CCalendar::CachePath().'section_list';

			if ($cache->InitCache(CCalendar::CacheTime(), $cacheId, $cachePath))
			{
				$res = $cache->GetVars();
				$result = $res["arResult"];
				$sectionIdList = $res["arSectionIds"];
				$permissions = $res["permissions"];
				if (is_array($permissions))
				{
					foreach($res["permissions"] as $sectionId => $perms)
					{
						self::$Permissions[$sectionId] = $perms;
					}
				}
			}
		}

		if (!$cacheEnabled || !isset($sectionIdList))
		{
			if (self::$useOrmFilter)
			{
				$sectionList = self::getListOrm($params);
			}
			else
			{
				$sectionList = self::getListOld($params);
			}

			$result = [];
			$sectionIdList = [];
			$checkedConnections = [];
			$isExchangeEnabled = CCalendar::IsExchangeEnabled();
			$isCalDAVEnabled = CCalendar::IsCalDAVEnabled();

			foreach ($sectionList as $section)
			{
				$sectId = (int)$section['ID'];

				if (in_array($sectId, $sectionIdList, true))
				{
					continue;
				}

				if ($checkPermissions)
				{
					self::HandlePermission($section);
				}

				$sectionType = $section['CAL_TYPE'];

				// Outlook js
				if (
					$sectionType !== Rooms\Manager::TYPE
					&& CCalendar::IsIntranetEnabled()
				)
				{
					$section['OUTLOOK_JS'] = 'needAction';
				}

				unset($section['ACCESS_CODE'], $section['TASK_ID']);

				$sectionIdList[] = $sectId;

				$section['EXPORT'] = [
					'ALLOW' => true,
					'LINK' => self::GetExportLink($section['ID'], $sectionType, $section['OWNER_ID'])
				];

				if ($sectionType === 'user')
				{
					$section['IS_EXCHANGE'] = $section['DAV_EXCH_CAL'] && $isExchangeEnabled;
					if ($section['CAL_DAV_CON'] && $isCalDAVEnabled)
					{
						$connectionId = (int)$section["CAL_DAV_CON"];

						if (isset($checkedConnections[$connectionId]))
						{
							$section['CAL_DAV_CON'] = $checkedConnections[$connectionId] ? $connectionId : false;
						}
						else
						{
							$connection = CDavConnection::GetList(
								["ID" => "ASC"],
								["ID" => $connectionId]
							);

							if ($connection)
							{
								$section['CAL_DAV_CON'] = (int)$connection["ID"];
							}
							else
							{
								$section['CAL_DAV_CON'] = false;
							}

							$checkedConnections[$connectionId] = (bool)$connection;
						}
					}
				}
				else
				{
					$section['IS_EXCHANGE'] = false;
					$section['CAL_DAV_CON'] = false;
				}

				$result[] = $section;
			}

			if ($cacheEnabled)
			{
				$cache->StartDataCache(CCalendar::CacheTime(), $cacheId, $cachePath);
				$cache->EndDataCache([
					"arResult" => $result,
					"arSectionIds" => $sectionIdList,
					"permissions" => self::$Permissions,
				]);
			}
		}

		if (($checkPermissions || $params['getPermissions']) && $userId >= 0 && !empty($sectionIdList))
		{
			$result = self::GetSectionPermission($result, $params['getPermissions']);
		}

		return $result;
	}

	private static function getListOrm($params)
	{
		$sectionFields = self::getSectionFields();
		$filterFields = $params['arFilter'] ?? [];
		$orderFields = $params['arOrder'] ?? [];
		$selectFields = $params['arSelect'] ?? ['*'];

		$query = SectionTable::query();
		$queryFilter = null;

		if (!empty($filterFields) && is_array($filterFields))
		{
			$queryFilter = \Bitrix\Main\ORM\Query\Query::filter();
			foreach ($filterFields as $key => $value)
			{
				if (is_string($value) && !$value)
				{
					continue;
				}

				switch ($key)
				{
					case 'ID':
					case 'XML_ID':
					case 'OWNER_ID':
					case 'EXTERNAL_TYPE':
						if (is_array($value))
						{
							$queryFilter->whereIn($key, $value);
						}
						else
						{
							$queryFilter->where($key, $value);
						}
						break;
					case '>ID':
						if ((int)$value)
						{
							$queryFilter->where('ID', '>', (int)$value);
						}
						break;
					case 'ACTIVE':
						if ($value === 'Y')
						{
							$queryFilter->where('ACTIVE', $value);
						}
						break;
					case 'CAL_TYPE':
						if (is_array($value))
						{
							$params['joinTypeInfo'] = true;

							$queryFilter
								->where('TYPE.ACTIVE', 'Y')
								->whereIn('CAL_TYPE', $value);
						}
						else
						{
							$queryFilter->where('CAL_TYPE', $value);
						}
						break;
					default:
						if (in_array($key, $sectionFields, true))
						{
							$queryFilter->where($key, $value);
						}
						break;
				}

			}
		}

		if (!empty($filterFields['ADDITIONAL_IDS']) && is_array($filterFields['ADDITIONAL_IDS']))
		{
			if ($queryFilter)
			{
				$query->where(
					\Bitrix\Main\ORM\Query\Query::filter()
						->logic('or')
						->whereIn('ID', $filterFields['ADDITIONAL_IDS'])
						->where($queryFilter)
				);
			}
			else
			{
				$query->whereIn('ID', $filterFields['ADDITIONAL_IDS']);
			}
		}
		else if ($queryFilter)
		{
			$query->where($queryFilter);
		}

		if ($params['joinTypeInfo'])
		{
			$query->registerRuntimeField(
				'TYPE',
				new \Bitrix\Main\Entity\ReferenceField(
					'TYPE',
					\Bitrix\Calendar\Internals\TypeTable::getEntity(),
					\Bitrix\Main\ORM\Query\Join::on('ref.XML_ID', 'this.CAL_TYPE'),
					['join_type' => \Bitrix\Main\ORM\Query\Join::TYPE_INNER]

				)
			);

			$selectFields['TYPE_NAME'] = 'TYPE.NAME';
			$selectFields['TYPE_DESC'] = 'TYPE.DESCRIPTION';
		}

		$query->setSelect($selectFields);

		$orderList = [];
		foreach ($orderFields as $key => $order)
		{
			if (in_array($key, $sectionFields, true))
			{
				$orderList[$key] = (mb_strtoupper($order) === 'DESC') ? 'DESC' : 'ASC';
			}
		}

		if (!empty($orderList))
		{
			$query->setOrder($orderList);
		}

		if (isset($params['limit']) && (int)$params['limit'] > 0)
		{
			$query->setLimit((int)$params['limit']);
		}

		$sectionQuery = $query->exec();

		[$sectionIdList, $result] = self::prepareSectionQueryData($sectionQuery);

		if ($params['getPermissions'])
		{
			$result = self::getSectionAccess($sectionIdList, $result);
		}

		return $result;
	}

	private static function prepareSectionQueryData($query)
	{
		$sectionIdList = [];
		$result = [];

		while ($section = $query->fetch())
		{
			$section['COLOR'] = CCalendar::Color($section['COLOR']);
			$section['NAME'] = Emoji::decode($section['NAME']);

			if (!empty($section['DATE_CREATE']))
			{
				$section['DATE_CREATE'] = (string)$section['DATE_CREATE'];
			}

			if (!empty($section['TIMESTAMP_X']))
			{
				$section['TIMESTAMP_X'] = (string)$section['TIMESTAMP_X'];
			}

			$sectionId = (int)$section['ID'];
			$sectionIdList[] = $sectionId;
			$result[$sectionId] = $section;
		}

		return [$sectionIdList, $result];
	}

	private static function getSectionAccess($sectionIdList, $sections)
	{
		if (empty($sectionIdList))
		{
			return [];
		}

		$accessQuery = \Bitrix\Calendar\Internals\AccessTable::query()
			->setSelect([
				'ACCESS_CODE',
				'TASK_ID',
				'SECT_ID'
			])
			->whereIn('SECT_ID', $sectionIdList)
			->exec()
		;

		while ($access = $accessQuery->fetch())
		{
			if (!isset($sections[$access['SECT_ID']]['ACCESS']))
			{
				$sections[$access['SECT_ID']]['ACCESS'] = [];
			}

			$sections[$access['SECT_ID']]['ACCESS'][$access['ACCESS_CODE']] = (int)$access['TASK_ID'];
		}

		return $sections;
	}

	private static function getListOld($params)
	{
		global $DB;

		$filter = $params['arFilter'];
		$sort = $params['arOrder'] ?? ['SORT' => 'asc'];

		$arFields = self::GetFields();
		$arSqlSearch = [];
		if(is_array($filter))
		{
			$filterKeys = array_keys($filter);
			foreach ($filterKeys as $filterKey)
			{
				$n = mb_strtoupper($filterKey);
				$val = $filter[$filterKey] ?? '';
				if (($val === '') || $val === "NOT_REF")
				{
					continue;
				}

				if ($n === 'CAL_TYPE' && ($val === 'company_calendar' || $val === 'calendar_company'))
				{
					$arSqlSearch[] = 'CS.CAL_TYPE = \'' . $val . '\' AND CS.CAL_TYPE IS NOT NULL';
				}
				else if (
					$n === 'ID'
					|| $n === 'XML_ID'
					|| $n === 'OWNER_ID'
					|| $n === 'EXTERNAL_TYPE'
					// || $n === 'CAL_DAV_CON'
				)
				{
					if (is_array($val))
					{
						$val = array_map(array($DB, "ForSQL"), $val);
						$arSqlSearch[] = 'CS.'.$n.' IN (\''.implode('\',\'', $val).'\')';
					}
					else
					{
						$arSqlSearch[] = GetFilterQuery("CS.".$n, $val, 'N');
					}
				}
				else if($n === '>ID' && (int)$val > 0)
				{
					$arSqlSearch[] = "CS.ID > ". (int)$val;
				}
				elseif($n === 'ACTIVE' && $val === "Y")
				{
					$arSqlSearch[] = "CS.ACTIVE = 'Y'";
				}
				elseif ($n === 'CAL_TYPE' && is_array($val))
				{
					$params['joinTypeInfo'] = true;
					$strType = "";
					foreach($val as $type)
					{
						$strType .= ",'" . $DB->ForSql($type) . "'";
					}
					$arSqlSearch[] = "CS.CAL_TYPE in (".trim($strType, ", ").")";
					$arSqlSearch[] = "CT.ACTIVE='Y'";
				}
				elseif(isset($arFields[$n]))
				{
					$arSqlSearch[] = GetFilterQuery(
						$arFields[$n]["FIELD_NAME"],
						$val,
						(isset($arFields[$n]["PROCENT"]) && $arFields[$n]["PROCENT"] === "N") ? "N" : "Y"
					);
				}
			}
		}

		$strOrderBy = '';
		foreach($sort as $by => $order)
		{
			if(isset($arFields[mb_strtoupper($by)]))
			{
				$byName = $arFields[mb_strtoupper($by)]["~FIELD_NAME"]
					?? $arFields[mb_strtoupper($by)]["FIELD_NAME"]
				;
				$strOrderBy .= $byName.' '.(mb_strtolower($order) === 'desc'?'desc':'asc').',';
			}
		}

		if($strOrderBy)
		{
			$strOrderBy = "ORDER BY ".rtrim($strOrderBy, ",");
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);

		if (!empty($filter['ADDITIONAL_IDS'] && is_array($filter['ADDITIONAL_IDS'])))
		{
			$strTypes = "";
			foreach($filter['ADDITIONAL_IDS'] as $adid)
			{
				$strTypes .= ",". (int)$adid;
			}
			$strSqlSearch = '('.$strSqlSearch.') OR ID in('.trim($strTypes, ', ').')';
		}

		$strLimit = '';
		if (isset($params['limit']) && (int)$params['limit'] > 0)
		{
			$strLimit = 'LIMIT '. (int)$params['limit'];
		}

		$select = 'CS.*';
		$from = 'b_calendar_section CS';

		// Fetch types info into selection
		if ($params['joinTypeInfo'])
		{
			$select .= ", CT.NAME AS TYPE_NAME, CT.DESCRIPTION AS TYPE_DESC";
			$from .= "\n INNER JOIN b_calendar_type CT ON (CS.CAL_TYPE=CT.XML_ID)";
		}

		if ($params['getPermissions'])
		{
			$select .= ", CAP.ACCESS_CODE, CAP.TASK_ID";
			$from .= "\n LEFT JOIN b_calendar_access CAP ON (CS.ID=".$DB->ToNumber('CAP.SECT_ID').")";
		}

		$strSql = "
				SELECT
					$select
				FROM
					$from
				WHERE
					$strSqlSearch
				$strOrderBy
				$strLimit";

		$result = [];
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		while ($sect = $res->Fetch())
		{
			$result[] = $sect;
		}

		return $result;
	}

	public static function GetSectionPermission(array $array, $getPermissions = null)
	{
		$res = [];
		$accessCodes = [];

		foreach ($array as $section)
		{
			$sectId = $section['ID'];
			$userId = CCalendar::GetUserId();

			$accessController = new SectionAccessController($userId);
			$sectionModel = SectionModel::createFromArray($section);

			$request = [
				ActionDictionary::ACTION_SECTION_EVENT_VIEW_TIME => [],
				ActionDictionary::ACTION_SECTION_EVENT_VIEW_TITLE => [],
				ActionDictionary::ACTION_SECTION_EVENT_VIEW_FULL => [],
				ActionDictionary::ACTION_SECTION_ADD => [],
				ActionDictionary::ACTION_SECTION_EDIT => [],
				ActionDictionary::ACTION_SECTION_ACCESS => [],
			];

			$result = $accessController->batchCheck($request, $sectionModel);

			if ($result[ActionDictionary::ACTION_SECTION_EVENT_VIEW_TIME])
			{
				$section['PERM'] = [
					'view_time' => $result[ActionDictionary::ACTION_SECTION_EVENT_VIEW_TIME],
					'view_title' => $result[ActionDictionary::ACTION_SECTION_EVENT_VIEW_TITLE],
					'view_full' => $result[ActionDictionary::ACTION_SECTION_EVENT_VIEW_FULL],
					'add' => $result[ActionDictionary::ACTION_SECTION_ADD],
					'edit' => $result[ActionDictionary::ACTION_SECTION_EDIT],
					'edit_section' => $result[ActionDictionary::ACTION_SECTION_EDIT],
					'access' => $result[ActionDictionary::ACTION_SECTION_ACCESS],
				];

				if ($getPermissions || $section['PERM']['access'] || $section['CAL_TYPE'] === 'location')
				{
					$section['ACCESS'] = [];
					if (
						isset(self::$Permissions[$sectId])
						&& is_array(self::$Permissions[$sectId])
						&& !empty(self::$Permissions[$sectId])
					)
					{
						// Add codes to get they full names for interface
						$currentAccessCodes = array_keys(self::$Permissions[$sectId]);
						foreach ($currentAccessCodes as $code)
						{
							if (!in_array($code, $accessCodes, true))
							{
								$accessCodes[] = $code;
							}
						}

						$section['ACCESS'] = self::$Permissions[$sectId];
					}
				}

				CCalendar::PushAccessNames($accessCodes);

				$res[] = $section;
			}
		}

		return $res;
	}

	public static function GetById(int $id = 0, bool $checkPermissions = true, bool $bRerequest = false)
	{
		if ($id > 0)
		{
			if (!isset(self::$sections[$id]) || $bRerequest)
			{
				$section = self::GetList([
					'arFilter' => ['ID' => $id],
					'checkPermissions' => $checkPermissions,
				]);

				if($section && is_array($section) && is_array($section[0]))
				{
					self::$sections[$id] = $section[0];
					return $section[0];
				}
			}
			else
			{
				return self::$sections[$id];
			}
		}

		return false;
	}

	//
	public static function GetSuperposedList($params = [])
	{
		global $DB;
		$checkPermissions = ($params['checkPermissions'] ?? null) !== false;
		$userId = isset($params['userId']) ? (int)$params['userId'] : CCalendar::GetCurUserId();

		$arResult = [];
		$arSectionIds = [];
		$sqlSearch = "";

		$select = '';
		$from = '';

		$helper = Application::getConnection()->getSqlHelper();

		if ($checkPermissions)
		{
			$select .= ", CAP.ACCESS_CODE, CAP.TASK_ID";
			$from .= "\n LEFT JOIN b_calendar_access CAP ON ({$helper->castToChar('CS.ID')}=CAP.SECT_ID)";
		}

		// Common types
		$strTypes = "";
		if (isset($params['TYPES']) && is_array($params['TYPES']))
		{
			foreach($params['TYPES'] as $type)
			{
				$strTypes .= ",'" . $DB->ForSql($type) . "'";
			}

			$strTypes = trim($strTypes, ", ");
			if ($strTypes != "")
			{
				$sqlSearch .= "(CS.CAL_TYPE in (" . $strTypes . "))";
			}
		}

		// Group's calendars
		$strGroups = "0";
		if (!empty($params['GROUPS']) && is_array($params['GROUPS']))
		{
			foreach($params['GROUPS'] as $ownerId)
			{
				if ((int)$ownerId > 0)
				{
					$strGroups .= "," . (int)$ownerId;
				}
			}

			if ($strGroups != "0")
			{
				if ($sqlSearch != "")
				{
					$sqlSearch .= " OR ";
				}
				$sqlSearch .= "(CS.OWNER_ID in (".$strGroups.") AND CS.CAL_TYPE='group')";
			}
		}

		if ($sqlSearch != "")
		{
			$strSql = "
				SELECT
					CS.*,
					CT.NAME AS TYPE_NAME, CT.DESCRIPTION AS TYPE_DESC".$select."
				FROM
					b_calendar_section CS
					LEFT JOIN b_calendar_type CT ON (CS.CAL_TYPE=CT.XML_ID)".$from."
				WHERE
					(
						CT.ACTIVE='Y'
					AND
						CS.ACTIVE='Y'
					AND
					(
						$sqlSearch
					))";

			$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			while($arRes = $res->Fetch())
			{
				if ($checkPermissions)
				{
					self::HandlePermission($arRes);
					unset($arRes['ACCESS_CODE'], $arRes['TASK_ID']);
				}

				if (!in_array($arRes['ID'], $arSectionIds))
				{
					$arSectionIds[] = $arRes['ID'];
					$arResult[] = $arRes;
				}
			}
		}

		// User's calendars
		$strUsers = "0";

		if (isset($params['USERS']) && is_array($params['USERS']) && count($params['USERS']) > 0)
		{
			foreach($params['USERS'] as $ownerId)
			{
				if ((int)$ownerId > 0)
				{
					$strUsers .= ",". (int)$ownerId;
				}
			}

			if ($strUsers != "0")
			{
				$strSql = "
				SELECT
					CS.*,
					U.LOGIN AS USER_LOGIN, U.NAME AS USER_NAME, U.LAST_NAME AS USER_LAST_NAME, U.SECOND_NAME AS USER_SECOND_NAME".$select."
				FROM
					b_calendar_section CS
					LEFT JOIN b_user U ON (CS.OWNER_ID=U.ID)".$from."
				WHERE
					(
						CS.ACTIVE='Y'
					AND
						CS.OWNER_ID in (".$strUsers.")
					AND
						CS.CAL_TYPE='user'
					)";

				$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}

			while($arRes = $res->Fetch())
			{
				if ($checkPermissions)
				{
					self::HandlePermission($arRes);
					unset($arRes['ACCESS_CODE'], $arRes['TASK_ID']);
				}

				if (!in_array($arRes['ID'], $arSectionIds))
				{
					$arSectionIds[] = $arRes['ID'];
					$arResult[] = $arRes;
				}
			}
		}

		if ($checkPermissions && !empty($arSectionIds))
		{
			$res = [];
			$sectIds = [];
			foreach($arResult as $sect)
			{
				$sectId = $sect['ID'];
				$ownerId = $sect['OWNER_ID'];

				$accessController = new SectionAccessController($userId);
				$sectionModel =
					SectionModel::createFromId((int)$sectId)
						->setType($sect['CAL_TYPE'])
						->setOwnerId((int)$ownerId)
				;
				$request = [
					ActionDictionary::ACTION_SECTION_EVENT_VIEW_TIME => [],
					ActionDictionary::ACTION_SECTION_EVENT_VIEW_TITLE => [],
					ActionDictionary::ACTION_SECTION_EVENT_VIEW_FULL => [],
					ActionDictionary::ACTION_SECTION_ADD => [],
					ActionDictionary::ACTION_SECTION_EDIT => [],
					ActionDictionary::ACTION_SECTION_ACCESS => [],
				];

				$result = $accessController->batchCheck($request, $sectionModel);

				if ($result[ActionDictionary::ACTION_SECTION_EVENT_VIEW_TIME] && !in_array($sectId, $sectIds))
				{
					$sect['PERM'] = [
						'view_time' => $result[ActionDictionary::ACTION_SECTION_EVENT_VIEW_TIME],
						'view_title' => $result[ActionDictionary::ACTION_SECTION_EVENT_VIEW_TITLE],
						'view_full' => $result[ActionDictionary::ACTION_SECTION_EVENT_VIEW_FULL],
						'add' => $result[ActionDictionary::ACTION_SECTION_ADD],
						'edit' => $result[ActionDictionary::ACTION_SECTION_EDIT],
						'edit_section' => $result[ActionDictionary::ACTION_SECTION_EDIT],
						'access' => $result[ActionDictionary::ACTION_SECTION_ACCESS],
					];

					if ($sect['CAL_TYPE'] === 'user')
					{
						if (isset($sect['USER_NAME'], $sect['USER_LAST_NAME']))
						{
							$sect['OWNER_NAME'] = CCalendar::GetUserName(array(
								"NAME" => $sect['USER_NAME'],
								"LAST_NAME" => $sect['USER_LAST_NAME'],
								"LOGIN" => $sect['USER_LOGIN'],
								"ID" => $ownerId,
								"SECOND_NAME" => $sect['USER_SECOND_NAME'])
							);
							unset(
								$sect['USER_LOGIN'],
								$sect['USER_LAST_NAME'],
								$sect['USER_SECOND_NAME'],
								$sect['USER_NAME']
							);
						}
						else
						{
							$sect['OWNER_NAME'] = CCalendar::GetUserName($ownerId);
						}
					}
					elseif ($sect['CAL_TYPE'] === 'group' && isset($params['arGroups']))
					{
						$sect['OWNER_NAME'] = $params['arGroups'][$ownerId]['NAME'];
					}

					$res[] = $sect;
					$sectIds[] = $sectId;
				}
			}
			$arResult = $res;
		}

		foreach ($arResult as &$section)
		{
			if (!empty($section['NAME']))
			{
				$section['NAME'] = Emoji::decode($section['NAME']);
			}
		}

		return $arResult;
	}

	public static function Edit($params)
	{
		global $DB;
		$sectionFields = $params['arFields'];
		$userId = (isset($params['userId']) ? (int)$params['userId'] : CCalendar::GetCurUserId());

		$isNewSection = !isset($sectionFields['ID']) || $sectionFields['ID'] <= 0;
		if (isset($sectionFields['COLOR']) || $isNewSection)
		{
			$sectionFields['COLOR'] = CCalendar::Color($sectionFields['COLOR'] ?? null);
		}

		$sectionFields['TIMESTAMP_X'] = CCalendar::Date(time());

		if (isset($sectionFields['EXPORT']) && is_array($sectionFields['EXPORT']))
		{
			$sectionFields['EXPORT'] = [
				'ALLOW' => (bool)$sectionFields['EXPORT']['ALLOW'],
				'SET' => (in_array($sectionFields['EXPORT']['set'] ?? null, array('all', '3_9', '6_12')))
					? $sectionFields['EXPORT']['set']
					: 'all',
			];

			$sectionFields['EXPORT'] = serialize($sectionFields['EXPORT']);
		}

		if (!empty($sectionFields['NAME']))
		{
			$sectionFields['NAME'] = Emoji::encode($sectionFields['NAME']);
		}

		if ($isNewSection) // Add
		{
			if (!isset($sectionFields['DATE_CREATE']))
			{
				$sectionFields['DATE_CREATE'] = CCalendar::Date(time());
			}

			if ((!isset($sectionFields['CREATED_BY']) || !$sectionFields['CREATED_BY']))
			{
				$sectionFields['CREATED_BY'] = CCalendar::GetCurUserId();
			}

			if (!isset($sectionFields['EXTERNAL_TYPE']))
			{
				$sectionFields['EXTERNAL_TYPE'] = 'local';
			}

			unset($sectionFields['ID']);
			$id = $DB->Add("b_calendar_section", $sectionFields, ['DESCRIPTION']);
		}
		else // Update
		{
			$id = (int)$sectionFields['ID'];

			$originalSection = SectionTable::getById($id);
			if (
				$originalSection !== false
				&& is_array($originalSection)
				&& $originalSection['EXTERNAL_TYPE'] !== self::EXTERNAL_TYPE_LOCAL
			)
			{
				$sectionFields['EXTERNAL_TYPE'] = $originalSection['EXTERNAL_TYPE'];
			}

			unset($sectionFields['ID']);
			$strUpdate = $DB->PrepareUpdate("b_calendar_section", $sectionFields);
			$strSql = "UPDATE b_calendar_section SET ". $strUpdate . " WHERE ID = " . $id;

			$DB->QueryBind($strSql, array('DESCRIPTION' => $sectionFields['DESCRIPTION'] ?? null));
		}

		//SaveAccess
		if ($id > 0 && isset($sectionFields['ACCESS']) && is_array($sectionFields['ACCESS']))
		{
			$sectionModel =
				SectionModel::createFromId($id)
					->setType($sectionFields['CAL_TYPE'])
					->setOwnerId((int)($sectionFields['OWNER_ID'] ?? null))
			;
			if ((new SectionAccessController($userId))->check(ActionDictionary::ACTION_SECTION_ACCESS, $sectionModel))
			{
				if (empty($sectionFields['ACCESS']))
				{
					self::SavePermissions(
						$id,
						self::GetDefaultAccess($sectionFields['CAL_TYPE'], $sectionFields['OWNER_ID'] ?? null)
					);
				}
				else
				{
					self::SavePermissions($id, $sectionFields['ACCESS']);
				}
			}
			elseif($isNewSection)
			{
				self::SavePermissions(
					$id,
					self::GetDefaultAccess($sectionFields['CAL_TYPE'], $sectionFields['OWNER_ID'])
				);
			}
		}

		if ($isNewSection && $id > 0 && !isset($sectionFields['ACCESS']))
		{
			self::SavePermissions(
				$id,
				self::GetDefaultAccess($sectionFields['CAL_TYPE'], $sectionFields['OWNER_ID'])
			);
		}

		if ($isNewSection && $id && !isset($sectionFields['XML_ID']))
		{
			$xmlId = md5($sectionFields['CAL_TYPE'] . '_' . $id . '_' . Random::getString(8));

			SectionTable::update($id, [
				'XML_ID' => $xmlId
			]);
		}

		CCalendar::ClearCache(['section_list', 'event_list']);

		if ($id > 0 && isset(self::$Permissions[$id]))
		{
			unset(self::$Permissions[$id]);
			self::$arOp = [];
		}

		if ($isNewSection)
		{
			self::onCreateSync($id, [
				'params'        => $params,
				'sectionFields' => $sectionFields,
				'userId'        => $userId,
			]);
		}
		else
		{
			self::onUpdateSync($id, [
				'params'          => $params,
				'sectionFields'   => $sectionFields,
				'userId'          => $userId,
			]);
		}

		if ($isNewSection)
		{
			foreach(EventManager::getInstance()->findEventHandlers("calendar", "OnAfterCalendarSectionAdd") as $event)
			{
				ExecuteModuleEventEx($event, [$id, $sectionFields]);
			}
		}
		else
		{
			foreach(EventManager::getInstance()->findEventHandlers("calendar", "OnAfterCalendarSectionUpdate") as $event)
			{
				ExecuteModuleEventEx($event, array($id, $sectionFields));
			}

		}

		$pullUserId = (int)($sectionFields['CREATED_BY'] ?? $userId);
		if (
			$pullUserId
			&& self::$sendPush
		)
		{
			Util::addPullEvent(
				'edit_section',
				$pullUserId,
				[
					'fields' => $sectionFields,
					'newSection' => $isNewSection,
				]
			);
		}

		return $id;
	}

	public static function Delete($id, $checkPermissions = true, $params = [])
	{
		global $DB;
		$id = (int)$id;

		$sectionFields = self::GetById($id);
		$canEdit = $sectionFields['PERM']['edit'] ?? false;
		if ($checkPermissions !== false && !$canEdit)
		{
			return CCalendar::ThrowError('EC_ACCESS_DENIED');
		}

		$meetingIds = [];
		if (Util::isSectionStructureConverted())
		{
			$strSql = "select CE.ID, CE.PARENT_ID, CE.CREATED_BY
				from b_calendar_event CE
				where CE.SECTION_ID=".$id."
				and (CE.IS_MEETING='1' and CE.IS_MEETING is not null)
				and (CE.DELETED='N' and CE.DELETED is not null)";
		}
		else
		{
			// Here we don't use GetList to speed up delete process
			// mantis: 82918
			$strSql = "SELECT CE.ID, CE.PARENT_ID, CE.DELETED, CE.CREATED_BY, CES.SECT_ID, CES.EVENT_ID
				FROM b_calendar_event CE
				LEFT JOIN b_calendar_event_sect CES ON (CE.ID=CES.EVENT_ID)
				WHERE CES.SECT_ID=".$id."
				AND (CE.IS_MEETING='1' and CE.IS_MEETING is not null)
				AND (CE.DELETED='N' and CE.DELETED is not null)";
		}

		$res = $DB->Query($strSql , false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while($ev = $res->Fetch())
		{
			if ((int)$ev['ID'] === (int)$ev['PARENT_ID'])
			{
				$meetingIds[] = (int)$ev['PARENT_ID'];
				CCalendarLiveFeed::OnDeleteCalendarEventEntry($ev['PARENT_ID']);
			}

			$pullUserId = (int)$ev['CREATED_BY'] > 0 ? (int)$ev['CREATED_BY'] : CCalendar::GetCurUserId();
			if (
				$pullUserId
				&& self::$sendPush
			)
			{
				Bitrix\Calendar\Util::addPullEvent(
					'delete_event',
					$pullUserId,
					[
						'fields' => $ev,
					]
				);
			}
		}

		if (!empty($meetingIds))
		{
			$DB->Query("DELETE from b_calendar_event WHERE PARENT_ID in (".implode(',', $meetingIds).")", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		//delete section in services
		self::onDeleteSync($id, [
			'originalFrom' => $params['originalFrom'] ?? '',
		]);

		// Del link from table
		if (!Util::isSectionStructureConverted())
		{
			$DB->Query("DELETE FROM b_calendar_event_sect WHERE SECT_ID=".$id, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}

		// Del from
		$DB->Query("DELETE FROM b_calendar_section WHERE ID=".$id, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		CCalendarEvent::DeleteEmpty($id);
		self::CleanAccessTable($id);
		CCalendar::ClearCache(array('section_list', 'event_list'));

		foreach(EventManager::getInstance()->findEventHandlers("calendar", "OnAfterCalendarSectionDelete") as $event)
		{
			ExecuteModuleEventEx($event, array($id));
		}

		$pullUserId = (int)$sectionFields['CREATED_BY'] > 0 ? (int)$sectionFields['CREATED_BY'] : CCalendar::GetCurUserId();
		if (
			$pullUserId
			&& self::$sendPush
		)
		{
			Util::addPullEvent(
				'delete_section',
				$pullUserId,
				[
					'fields' => $sectionFields,
				]
			);
		}


		return true;
	}

	public static function CreateDefault($params = [])
	{
		if ($params['type'] === 'user' || $params['type'] === 'group')
		{
			$name = CCalendar::GetOwnerName($params['type'], $params['ownerId']);
		}
		else
		{
			$name = $params['type'] === 'location' ? Loc::getMessage('EC_DEF_SECT_LOCATION_CAL') : Loc::getMessage('EC_DEF_SECT_GROUP_CAL');
		}

		$userId = $params['type'] === 'user' ? $params['ownerId'] : CCalendar::GetCurUserId();

		if ($userId > 0)
		{
			$arFields = [
				'CAL_TYPE' => $params['type'],
				'NAME' => $name,
				'DESCRIPTION' => Loc::getMessage('EC_DEF_SECT_DESC'),
				'COLOR' => CCalendar::Color(),
				'OWNER_ID' => $params['ownerId'],
				'IS_EXCHANGE' => 0,
				'ACCESS' => CCalendarSect::GetDefaultAccess($params['type'], $params['ownerId']),
				'PERM' => [
					'view_time' => true,
					'view_title' => true,
					'view_full' => true,
					'add' => true,
					'edit' => true,
					'edit_section' => true,
					'access' => true,
				],
				'EXTERNAL_TYPE' => self::EXTERNAL_TYPE_LOCAL,
			];

			if($params['type'] === 'location')
			{
				$arFields['NECESSITY'] = 'N';
				$arFields['CAPACITY'] = 0;

				$builder = new \Bitrix\Calendar\Core\Builders\Rooms\RoomBuilderFromArray($arFields);
				$room = $builder->build();

				Rooms\Manager::createInstanceWithRoom($room)
					->createRoom()
					->saveAccess()
					->clearCache()
					->eventHandler('OnAfterCalendarRoomCreate')
					->addPullEvent('create_room')
				;

				$arFields['ID'] = $room->getId();
			}
			else
			{
				$arFields['ID'] = self::Edit([
					 'arFields' => $arFields,
					 'userId' => $userId,
				]);
			}

			if ($arFields['ID'] > 0)
			{
				return $arFields;
			}
		}
		return false;
	}

	public static function SavePermissions($sectId, $taskPerm)
	{
		global $DB;
		$DB->Query("DELETE FROM b_calendar_access WHERE SECT_ID='".(int)$sectId."'", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if (is_array($taskPerm))
		{
			foreach($taskPerm as $accessCode => $taskId)
			{
				if (strpos($accessCode, "SG") === 0)
				{
					$accessCode = self::prepareGroupCode($accessCode);
				}

				$insert = $DB->PrepareInsert(
					"b_calendar_access",
					[
						"ACCESS_CODE" => $accessCode,
						"TASK_ID" => (int)$taskId,
						"SECT_ID" => (int)$sectId,
					]
				);
				$strSql = "INSERT INTO b_calendar_access(".$insert[0].") VALUES(".$insert[1].")";
				$DB->Query($strSql , false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}
	}

	private static function prepareGroupCode($code)
	{
		$parsedCode = explode('_', $code);

		if (count($parsedCode) === 1)
		{
			$code .= '_K';
		}

		return $code;
	}

	public static function GetArrayPermissions($arSections = [])
	{
		global $DB;
		$s = "'0'";
		foreach($arSections as $id)
		{
			if ($id > 0)
			{
				$s .= ",'". (int)$id ."'";
			}
		}

		$helper = Application::getConnection()->getSqlHelper();
		$strSql = 'SELECT SC.ID, CAP.ACCESS_CODE, CAP.TASK_ID, SC.CAL_TYPE, SC.OWNER_ID, SC.CREATED_BY
			FROM b_calendar_section SC
			LEFT JOIN b_calendar_access CAP ON CAP.SECT_ID = '.$helper->castToChar('SC.ID').'
			WHERE SC.ID in ('.$s.')'
		;
		
		$res = $DB->Query($strSql , false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while($arRes = $res->Fetch())
		{
			if ($arRes['ID'] > 0)
			{
				self::HandlePermission($arRes);
			}
		}

		return self::$Permissions;
	}

	public static function SetClearOperationCache($val = true)
	{
		self::$bClearOperationCache = $val;
	}

	public static function CanDo($operation, $sectId = 0, $userId = null)
	{
		$res = null;
		global $USER;
		if ((!$USER || !is_object($USER)) || $USER->CanDoOperation('edit_php'))
		{
			return true;
		}

		if (!is_numeric($userId))
		{
			$userId = CCalendar::GetCurUserId();
		}

		if (
			CCalendar::IsBitrix24()
			&& Loader::includeModule('bitrix24')
			&& CBitrix24::isPortalAdmin($userId)
		)
		{
			return true;
		}

		if (
			CCalendar::IsSocNet()
			&& CCalendar::IsSocnetAdmin()
			&&(
				CCalendar::GetType() === 'group'
				|| CCalendar::GetType() === 'user'
				|| CCalendar::IsBitrix24()
			)
		)
		{
			return true;
		}

		if ((int)$sectId && (int)$userId && !self::$bClearOperationCache)
		{
			$sectionPermKey = $userId . '|' . $sectId;
			if (isset(self::$userSectionPermissions[$sectionPermKey]))
			{
				$res = in_array($operation, self::$userSectionPermissions[$sectionPermKey], true);
			}
		}

		if ($res === null)
		{
			$res = in_array($operation, self::GetOperations($sectId, $userId), true);
		}

		self::$bClearOperationCache = false;
		return $res;
	}

	public static function GetOperations($sectId, $userId = null)
	{
		if (!$userId)
		{
			$userId = CCalendar::GetCurUserId();
		}

		if ((int)$sectId && (int)$userId && !self::$bClearOperationCache)
		{
			$sectionPermKey = $userId . '|' . $sectId;
			if (isset(self::$userSectionPermissions[$sectionPermKey]))
			{
				return self::$userSectionPermissions[$sectionPermKey];
			}
		}

		$codes = Util::getUserAccessCodes($userId);

		$key = $sectId.'|'.implode(',', $codes);
		if (self::$bClearOperationCache || !is_array(self::$arOp[$key] ?? null))
		{
			if (!isset(self::$Permissions[$sectId]))
			{
				self::GetArrayPermissions([$sectId]);
			}
			$perms = self::$Permissions[$sectId];

			self::$arOp[$key] = [];
			if (is_array($perms))
			{
				foreach ($perms as $code => $taskId)
				{
					if (in_array($code, $codes, true))
					{
						self::$arOp[$key] = array_merge(self::$arOp[$key], CTask::GetOperations($taskId, true));
					}
				}
			}
		}

		if ((int)$sectId && (int)$userId)
		{
			$sectionPermKey = $userId . '|' . $sectId;
			self::$userSectionPermissions[$sectionPermKey] = self::$arOp[$key];
		}

		self::$bClearOperationCache = false;

		return self::$arOp[$key];
	}

	public static function GetCalDAVConnectionId($section = 0)
	{
		global $DB;

		$arIds = is_array($section) ? $section : array($section);
		$arIds = array_unique($arIds);
		$strIds = [];
		$result = [];
		foreach($arIds as $id)
		{
			if ((int)$id > 0)
			{
				$strIds[] = (int)$id;
				$result[(int)$id] = 0;
			}
		}
		$strIds = implode(',', $strIds);

		$strSql = "SELECT ID, CAL_DAV_CON FROM b_calendar_section WHERE ID in (".$strIds.")";
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		while ($arRes = $res->Fetch())
		{
			$result[$arRes['ID']] = ($arRes['CAL_DAV_CON'] > 0) ? (int)$arRes['CAL_DAV_CON'] : 0;
		}

		if (!is_array($section))
		{
			return $result[$section];
		}

		return $result;
	}

	public static function GetExportLink($sectionId, $type = '', $ownerId = null)
	{
		$userId = CCalendar::getCurUserId();
		$ownerId = (int)$ownerId;
		$path = Util::getPathToCalendar($ownerId, $type);

		return '&type='.mb_strtolower($type)
				.'&owner='.$ownerId
				.'&ncc=1&user='.$userId
				.'&'.'sec_id='.(int)$sectionId
				.'&sign='.self::getSign($userId, $sectionId)
				.'&bx_hit_hash='.self::getAuthHash($userId, $path);
	}

	/**
	 * @param int $sectionId
	 * @param array $fields
	 */
	public static function CleanFieldsValueById(int $sectionId, array $fields): void
	{
		if (!$fields)
		{
			return;
		}

		global $DB;
		$dbFields = [];

		foreach ($fields as $field)
		{
			$dbFields[$field] = false;
		}

		$DB->Query("UPDATE b_calendar_section SET "
			. $DB->PrepareUpdate('b_calendar_section', $dbFields)
			. " WHERE ID = " . $sectionId);
	}

	/**
	 * @param int $id
	 * @param array $params
	 *
	 * @return Result|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private static function onCreateSync(int $id, array $params): ?Result
	{
		if (!Loader::includeModule('dav'))
		{
			return null;
		}

		$originalFrom = $params['params']['originalFrom'] ?? null;
		if ($originalFrom === ($params['sectionFields']['EXTERNAL_TYPE'] ?? null))
		{
			return null;
		}

		if (
			!empty($params['sectionFields']['EXTERNAL_TYPE'])
			&& (
				$params['sectionFields']['EXTERNAL_TYPE'] === Bitrix\Calendar\Sync\Caldav\Helper::CALDAV_TYPE
				|| $params['sectionFields']['EXTERNAL_TYPE'] === Bitrix\Calendar\Sync\Caldav\Helper::EXCHANGE_TYPE
			)
		)
		{
			return null;
		}

		if ($params['params']['arFields']['CAL_TYPE'] !== 'user')
		{
			return null;
		}

		/** @var \Bitrix\Calendar\Core\Section\Section $section */
		$section = (new Bitrix\Calendar\Core\Mappers\Section())->getById($id);
		if (!$section)
		{
			return null;
		}

		$factories = FactoriesCollection::createByUserId($params['userId']);
		if ($factories->count() === 0)
		{
			return null;
		}

		$syncManager = new Synchronization($factories);
		$context = new Context([]);
		if (!empty($originalFrom))
		{
			$context->add('sync', 'originalFrom', $originalFrom);
		}

		$result = $syncManager->createSection($section, $context);

		// TODO: temporary. Need to move into separated method
		if ($result->isSuccess())
		{
			/** @var FactoryInterface $factory */
			foreach ($factories as $factory)
			{
				if ($factory->canSubscribeSection())
				{
					$outgoingManager = new \Bitrix\Calendar\Sync\Managers\OutgoingManager($factory->getConnection());
					/** @var Result $vendorResult */
					if (
						($vendorResult = $result->getData()[$factory->getCode()])
						&& $sectionConnection = $vendorResult->getData()['sectionConnection']
					)
					{
						$outgoingManager->subscribeSection($sectionConnection);
					}
				}
			}
		}


		return $result;
	}

	private static function onUpdateSync(int $id, array $params)
	{
		if (!Loader::includeModule('dav'))
		{
			return null;
		}

		if (($params['params']['arFields']['CAL_TYPE'] ?? null) !== 'user')
		{
			return null;
		}

		if (empty($params['params']['arFields']['NAME']))
		{
			return new Result();
		}

		/** @var \Bitrix\Calendar\Core\Section\Section $section */
		$section = (new Bitrix\Calendar\Core\Mappers\Section())->getById($id);
		if (!$section)
		{
			return null;
		}

		$factories = FactoriesCollection::createBySection($section);
		if ($factories->count() === 0)
		{
			return null;
		}
		$syncManager = new Synchronization($factories);
		$context = new Context([]);
		if (!empty($params['params']['originalFrom']))
		{
			$context->add('sync', 'originalFrom', $params['params']['originalFrom']);
		}

		return $syncManager->updateSection($section, $context);
	}

	private static function onDeleteSync(int $id, array $params)
	{
		if (!Loader::includeModule('dav'))
		{
			return null;
		}

		$section = new Bitrix\Calendar\Core\Section\Section();
		$section->setId($id);

		$factories = FactoriesCollection::createBySection($section);

		if ($factories->count() === 0)
		{
			self::cleanLinkTables($id);

			return null;
		}
		$syncManager = new Synchronization($factories);
		$context = new Context([]);
		if (!empty($params['originalFrom']))
		{
			$context->add('sync', 'originalFrom', $params['originalFrom']);
		}

		return $syncManager->deleteSection($section, $context);
	}

	public static function cleanLinkTables($sectId)
	{
		global $DB;

		$DB->Query("DELETE FROM b_calendar_event_connection
			WHERE EVENT_ID IN (SELECT EV.ID FROM b_calendar_event EV
	        WHERE EV.SECTION_ID = " . (int)$sectId . ");"
		);

		$DB->Query("DELETE FROM b_calendar_section_connection 
            WHERE SECTION_ID = " . (int)$sectId . ";"
		);
	}

	function GetSPExportLink()
	{
		$userId = CCalendar::GetCurUserId();
		return '&user_id='.$userId.'&sign='.self::GetSign($userId, 'superposed_calendars');
	}

	public static function GetOutlookLink($Params)
	{
		if (Bitrix\Main\Loader::includeModule('intranet'))
		{
			return CIntranetUtils::GetStsSyncURL($Params);
		}

		return null;
	}

	private static function GetUniqCalendarId()
	{
		$uniq = COption::GetOptionString("calendar", "~export_uniq_id", "");
		if($uniq == '')
		{
			$uniq = md5(uniqid(rand(), true));
			COption::SetOptionString("calendar", "~export_uniq_id", $uniq);
		}
		return $uniq;
	}

	public static function GetSign($userId, $sectId)
	{
		return md5($userId."||".$sectId."||".self::GetUniqCalendarId());
	}

	public static function CheckSign($sign, $userId, $sectId)
	{
		return (md5($userId."||".$sectId."||".self::GetUniqCalendarId()) == $sign);
	}

	// * * * * EXPORT TO ICAL  * * * *
	public static function ReturnICal($Params)
	{
		$sectId = $Params['sectId'];
		$userId = (int)$Params['userId'];
		$sign = $Params['sign'];
		$type = mb_strtolower($Params['type']);
		$ownerId = (int)$Params['ownerId'];
		$bCache = false;

		$GLOBALS['APPLICATION']->RestartBuffer();

		if (!self::CheckSign($sign, $userId, $sectId))
		{
			return CCalendar::ThrowError(Loc::getMessage('EC_ACCESS_DENIED'));
		}

		$arSections = self::GetList(
			array(
				'arFilter' => array('ID' => $sectId),
				'checkPermissions' => false,
			));

		if ($arSections && $arSections[0] && $arSections[0]['EXPORT'] && $arSections[0]['EXPORT']['ALLOW'])
		{
			$arSection = $arSections[0];
			$arEvents = CCalendarEvent::GetList(
				array(
					'arFilter' => array(
						'SECTION' => $arSection['ID'],
					),
					'getUserfields' => false,
					'parseRecursion' => false,
					'fetchAttendees' => false,
					'fetchMeetings' => true,
					'userId' => $userId,
				)
			);
			$iCalEvents = self::FormatICal($arSection, $arEvents);
		}
		else
		{
			return CCalendar::ThrowError(Loc::getMessage('EC_ACCESS_DENIED'));
		}

		self::ShowICalHeaders();
		echo $iCalEvents;
		exit();
	}

	private static function ShowICalHeaders()
	{
		header("Content-Type: text/calendar; charset=UTF-8");
		header("Accept-Ranges: bytes");
		header("Connection: Keep-Alive");
		header("Keep-Alive: timeout=15, max=100");
	}

	private static function FormatICal($section, $events)
	{
		global $APPLICATION;

		$res = 'BEGIN:VCALENDAR'."\n".
			'PRODID:-//Bitrix//Bitrix Calendar//EN'."\n".
			'VERSION:2.0'."\n".
			'CALSCALE:GREGORIAN'."\n".
			'METHOD:PUBLISH'."\n".
			'X-WR-CALNAME:'.self::_ICalPaste($section['NAME'])."\n".
			'X-WR-CALDESC:'.self::_ICalPaste($section['DESCRIPTION'])."\n";

		$localTime = new DateTime();
		$localOffset = $localTime->getOffset();

		foreach ($events as $event)
		{
			$fromTs = CCalendar::Timestamp($event['DATE_FROM']);
			$toTs = CCalendar::Timestamp($event['DATE_TO']);
			if ($event['DT_SKIP_TIME'] === "Y")
			{
				$dtStart = date("Ymd", $fromTs);
				$dtEnd = date("Ymd", $toTs + CCalendar::GetDayLen());
			}
			else
			{
				$fromTsUTC = $fromTs - $event['TZ_OFFSET_FROM'];
				$toTsUTC = $toTs - $event['TZ_OFFSET_TO'];
				$dtStart = date("Ymd\THis\Z", $fromTsUTC);
				$dtEnd = date("Ymd\THis\Z", $toTsUTC);
			}

			$dtStamp = str_replace('T000000Z', '', date("Ymd\THisZ", CCalendar::Timestamp($event['TIMESTAMP_X']) - $localOffset));
			$uid = md5(uniqid(rand(), true).$event['ID']).'@bitrix';
			$period = '';

			$rrule = CCalendarEvent::ParseRRULE($event['RRULE']);

			if($rrule && isset($rrule['FREQ']) && $rrule['FREQ'] !== 'NONE')
			{
				$period = 'RRULE:FREQ='.$rrule['FREQ'].';';
				$period .= 'INTERVAL='.$rrule['INTERVAL'].';';
				if ($rrule['FREQ'] === 'WEEKLY')
				{
					$period .= 'BYDAY='.implode(',', $rrule['BYDAY']).';';
				}

				if (isset($rrule['COUNT']) && (int)$rrule['COUNT'] > 0)
				{
					$period .= 'COUNT='. (int)$rrule['COUNT'] .';';
				}
				else
				{
					$until = date("Ymd", $event['DATE_TO_TS_UTC']);
					if($until != '20380101')
						$period .= 'UNTIL='.$until.';';
				}
				$period .= 'WKST=MO';
				$period .= "\n";
			}

			$res .= 'BEGIN:VEVENT'."\n";

			if ($event['DT_SKIP_TIME'] === "Y")
			{
				$res .= 'DTSTART;VALUE=DATE:'.$dtStart."\n".
					'DTEND;VALUE=DATE:'.$dtEnd."\n";
			}
			else
			{
				$res .= 'DTSTART;VALUE=DATE-TIME:'.$dtStart."\n".
					'DTEND;VALUE=DATE-TIME:'.$dtEnd."\n";
			}

			$res .= 'DTSTAMP:'.$dtStamp."\n".
				'UID:'.$uid."\n".
				'SUMMARY:'.self::_ICalPaste($event['NAME'])."\n".
				'DESCRIPTION:'.self::_ICalPaste($event['DESCRIPTION'])."\n".$period."\n".
				'LOCATION:'.self::_ICalPaste(CCalendar::GetTextLocation($event['LOCATION']))."\n".
				'SEQUENCE:0'."\n".
				'STATUS:CONFIRMED'."\n".
				'TRANSP:TRANSPARENT'."\n".
				'END:VEVENT'."\n";
		}

		$res .= 'END:VCALENDAR';
		if (!defined('BX_UTF') || BX_UTF !== true)
			$res = $APPLICATION->ConvertCharset($res, LANG_CHARSET, 'UTF-8');

		return $res;
	}

	private static function _ICalPaste($str)
	{
		$str = preg_replace ("/\r/i", '', $str);
		$str = preg_replace ("/\n/i", '\\n', $str);
		return $str;
	}

	public static function GetModificationLabel($calendarId) // GetCalendarModificationLabel
	{
		global $DB;
		// We didn't have cashing for task list,
		// so just change modification label every 3 minutes
		if ($calendarId[0] === CCalendar::TASK_SECTION_ID)
		{
			return CCalendar::Date(round(time() / 180) * 180);
		}

		$sectionId = (int)$calendarId[0];
		if ($sectionId > 0)
		{
			$strSql = "
				SELECT ".$DB->DateToCharFunction("CS.TIMESTAMP_X")." as TIMESTAMP_X
				FROM b_calendar_section CS
				WHERE ID=".$sectionId;
			$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if($sect = $res->Fetch())
				return $sect['TIMESTAMP_X'];
		}
		return "";
	}

	public static function UpdateModificationLabel($arId = [])
	{
		global $DB;
		if (!is_array($arId) && $arId)
		{
			$arId = [$arId];
		}

		$arId = array_unique($arId);
		$strIds = [];
		foreach($arId as $id)
		{
			if ((int)$id > 0)
			{
				$strIds[] = (int)$id;
			}
		}
		$strIds = implode(',', $strIds);

		if ($strIds)
		{
			$strSql =
			"UPDATE b_calendar_section SET ".
				$DB->PrepareUpdate("b_calendar_section", array('TIMESTAMP_X' => FormatDate(CCalendar::DFormat(true), time()))).
			" WHERE ID in (".$strIds.")";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
	}

	public static function GetDefaultAccess($type, $ownerId)
	{
		if (CCalendar::IsIntranetEnabled())
		{
			$access = array('G2' => CCalendar::GetAccessTasksByName('calendar_section', 'calendar_view_time'));
		}
		else
		{
			$access = array('G2' => CCalendar::GetAccessTasksByName('calendar_section', 'calendar_view'));
		}

		if ($type === 'group' && $ownerId > 0)
		{
			$access['SG'.$ownerId.'_A'] = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_access');
			$access['SG'.$ownerId.'_E'] = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_edit');
			$access['SG'.$ownerId.'_K'] = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_edit');
		}
		else if ($type !== 'user')
		{
			$access['G2'] = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_edit');
		}

		// Creator of the section
		if ($type !== 'user')
		{
			$access['U'.CCalendar::GetUserId()] = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_access');
		}

		$accessCodes = [];
		foreach($access as $code => $o)
		{
			$accessCodes[] = $code;
		}

		CCalendar::PushAccessNames($accessCodes);
		return $access;
	}

	public static function getAuthHash(int $userId, string $path)
	{
		global $USER;
		if ((!isset(self::$authHashiCal) || empty(self::$authHashiCal)) && $USER && is_object($USER))
		{
			self::$authHashiCal = $USER::GetHitAuthHash($path, $userId);
			if (empty(self::$authHashiCal))
			{
				self::$authHashiCal = $USER::AddHitAuthHash($path, $userId);
			}
		}
		return self::$authHashiCal;
	}

	public static function CheckAuthHash()
	{
		global $USER;
		if ($_REQUEST['bx_hit_hash'] ?? null)
		{
			return $USER->LoginHitByHash($_REQUEST['bx_hit_hash']);
		}

		return false;
	}

	public static function GetLastUsedSection($type, $ownerId, $userId)
	{
		$userSettings = UserSettings::get($userId);
		return $userSettings['lastUsedSection'];
	}

	public static function GetSectionForOwner($type, $ownerId, $autoCreate = true)
	{
		$sectionId = false;
		$autoCreated = false;

		$res = self::GetList([
			'arFilter' => [
				'CAL_TYPE' => $type,
				'OWNER_ID' => $ownerId,
				'DELETED' => 'N',
				'ACTIVE' => 'Y',
				'GAPI_CALENDAR_ID' => null,
			],
			'checkPermissions' => false,
			'getPermissions' => false,
			'limit' => 1,
		]);

		$section = $res[0] ?? false;

		if ($section)
		{
			$ownerId = $section['OWNER_ID'];
			$sectionId = $section['ID'];
		}

		if (!$section && $autoCreate)
		{
			$section = self::CreateDefault([
				'type' => $type,
				'ownerId' => $ownerId,
			]);

			$autoCreated = true;
			$sectionId = $section['ID'];
		}

		return array('sectionId' => $sectionId, 'autoCreated' => $autoCreated, 'section' => $section);
	}

	public static function HandlePermission($section = [])
	{
		if ($section && $section['ID'])
		{
			$sectionId = $section['ID'];
			if (!isset(self::$Permissions[$sectionId]) || !is_array(self::$Permissions[$sectionId]))
			{
				self::$Permissions[$sectionId] = [];
			}

			if (isset($section['ACCESS_CODE']) && $section['ACCESS_CODE'] && $section['ACCESS_CODE'] !== '0' && (int)$section['TASK_ID'] > 0)
			{
				self::$Permissions[$sectionId][$section['ACCESS_CODE']] = (int)$section['TASK_ID'];
			}

			if (isset($section['ACCESS']) && $section['ACCESS'])
			{
				self::$Permissions[$sectionId] = $section['ACCESS'];
			}

			if ($section['CAL_TYPE'] !== 'group' && (int)$section['OWNER_ID'] > 0) // Owner for user or other calendar types
			{
				self::$Permissions[$sectionId]['U'.$section['OWNER_ID']] = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_access');
			}

			if ($section['CAL_TYPE'] === 'group' && (int)$section['OWNER_ID'] > 0) // Owner for group
			{
				self::$Permissions[$sectionId]['SG'.$section['OWNER_ID'].'_A'] = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_access');
			}
		}
	}


	public static function CleanAccessTable(string $sectionId)
	{
		if (empty($sectionId))
		{
			return;
		}

		global $DB;

		$DB->Query("DELETE FROM b_calendar_access WHERE SECT_ID = '" . $sectionId  . "'", false,
			"FILE: ".__FILE__."<br> LINE: ".__LINE__);
	}

	/**
	 * @param string|null $davXmlId
	 * @param string|null $externalType
	 * @return bool
	 */
	public static function CheckGoogleVirtualSection($davXmlId = '', $externalType = ''): bool
	{
		return $davXmlId !== '' && (preg_match('/@virtual\/events\//i', (string)$davXmlId)
			|| preg_match('/@group\.v\.calendar\.google/i', (string)$davXmlId)
			|| $externalType === Dictionary::ACCESS_ROLE_TO_EXTERNAL_TYPE['reader']
			|| $externalType === Dictionary::ACCESS_ROLE_TO_EXTERNAL_TYPE['freeBusyOrder']
		);
	}

	public static function GetCount()
	{
		global $DB;
		$count = 0;
		$res = $DB->Query('select count(*) as c  from b_calendar_section', false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if($res = $res->Fetch())
		{
			$count = $res['c'];
		}

		return $count;
	}

	public static function GetSectionByEventId($id)
	{
		$resDb = EventTable::getList([
			'select' => ['SECTION_ID', 'OWNER_ID', 'CAL_TYPE'],
			'filter' => ['ID' => $id],
		]);

		return $resDb->fetch();
	}

	public static function containsLocalSection($sections, $type): bool
	{
		if ($type !== 'user')
		{
			return true;
		}

		if ($sections && is_array($sections))
		{
			foreach ($sections as $section)
			{
				if (
					$section['EXTERNAL_TYPE'] === self::EXTERNAL_TYPE_LOCAL
					&& $section['CAL_TYPE'] === 'user'
					&& (int)$section['OWNER_ID'] === CCalendar::GetOwnerId()
				)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param int $connectionId
	 * @param array $type
	 *
	 * @return array
	 * @throws ArgumentException
	 * @throws CDavArgumentNullException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getAllSectionsForVendor(int $connectionId, array $type)
	{
		if (!Loader::includeModule('dav'))
		{
			return [
				'status' => 'error',
				'message' => Loc::getMessage('EC_SYNCAJAX_DAV_REQUIRED'),
			];
		}
		/** @var Bitrix\Calendar\Core\Mappers\Factory $eventMapper */
		$mapperFactory = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		if ($connection = $mapperFactory->getConnection()->getById($connectionId))
		{
			$userId = \CCalendar::GetUserId();

			if ($connection->getOwner()->getId() !== $userId)
			{
				return [];
			}

			return SectionTable::query()
				->setSelect(['*', 'CONNECTION_ID' => 'LINK.CONNECTION_ID'])
				->where('CAL_TYPE', 'user')
				->where('OWNER_ID', $userId)
				->whereIn('EXTERNAL_TYPE', $type)
				->where('CONNECTION_ID', $connectionId)
				->registerRuntimeField(
					new \Bitrix\Main\Entity\ReferenceField(
						'LINK',
						\Bitrix\Calendar\Internals\SectionConnectionTable::class,
						['=this.ID' => 'ref.SECTION_ID'],
						['join_type' => 'INNER']
					),
				)->exec()->fetchAll()
			;
		}

		return [];
	}

	public static function prepareSectionListResponse(string $type, string $ownerId): array
	{
		$userId = CCalendar::GetCurUserId();
		$followedSectionList = UserSettings::getFollowedSectionIdList($userId);

		$isNotInternalUser =
			Loader::includeModule('extranet')
			&& !\CExtranet::IsIntranetUser(SITE_ID, $userId)
		;
		if ($isNotInternalUser)
		{
			// Check permissions for group
			if ($type === 'group')
			{
				$perm = CCalendar::GetPermissions([
					'type' => $type,
					'ownerId' => $ownerId,
					'userId' => $userId,
					'setProperties' => false
				]);
				// For all members of the group it will be true so we want to skip everybody else
				if (!$perm['edit'])
				{
					return [];
				}
			}
			else // user's and company calendars are not available for external users
			{
				return [];
			}
		}

		$sectionList = CCalendar::getSectionList([
			'CAL_TYPE' => $type,
			'OWNER_ID' => $ownerId,
			'ACTIVE' => 'Y',
			'ADDITIONAL_IDS' => $followedSectionList,
			'checkPermissions' => true,
			'getPermissions' => true,
			'getImages' => true
		]);

		$sectionList = array_merge($sectionList, CCalendar::getSectionListAvailableForUser($userId));
		$sections = [];
		$sectionIdList = [];

		foreach ($sectionList as $section)
		{
			if (!in_array((int)$section['ID'], $sectionIdList))
			{
				// if ($isNotInternalUser)


				if (in_array($section['ID'], $followedSectionList))
				{
					$section['SUPERPOSED'] = true;
				}

				if (!empty($section['NAME']))
				{
					$section['NAME'] = Emoji::decode($section['NAME']);
				}
				$sections[] = $section;
				$sectionIdList[] = (int) $section['ID'];
			}
		}

		return $sections;
	}
}
?>