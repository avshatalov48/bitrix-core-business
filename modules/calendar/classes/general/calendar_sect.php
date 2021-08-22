<?
use Bitrix\Main\Loader;

class CCalendarSect
{
	private static
		$sections,
		$Permissions = [],
		$arOp = [],
		$bClearOperationCache = false,
		$authHashiCal = null, // for login by hash
		$Fields = [];

	private static function GetFields()
	{
		global $DB;
		if (!count(self::$Fields))
			self::$Fields = array(
			"ID" => Array("FIELD_NAME" => "CS.ID", "FIELD_TYPE" => "int"),
			"NAME" => Array("FIELD_NAME" => "CS.NAME", "FIELD_TYPE" => "string"),
			"XML_ID" => Array("FIELD_NAME" => "CS.XML_ID", "FIELD_TYPE" => "string"),
			"EXTERNAL_ID" => Array("FIELD_NAME" => "CS.EXTERNAL_ID", "FIELD_TYPE" => "string"),
			"ACTIVE" => Array("FIELD_NAME" => "CS.ACTIVE", "FIELD_TYPE" => "string"),
			"COLOR" => Array("FIELD_NAME" => "CS.COLOR", "FIELD_TYPE" => "string"),
			"SORT" => Array("FIELD_NAME" => "CS.SORT", "FIELD_TYPE" => "int"),
			"CAL_TYPE" => Array("FIELD_NAME" => "CS.CAL_TYPE", "FIELD_TYPE" => "string", "PROCENT" => "N"),
			"OWNER_ID" => Array("FIELD_NAME" => "CS.OWNER_ID", "FIELD_TYPE" => "int"),
			"CREATED_BY" => Array("FIELD_NAME" => "CS.CREATED_BY", "FIELD_TYPE" => "int"),
			"PARENT_ID" => Array("FIELD_NAME" => "CS.PARENT_ID", "FIELD_TYPE" => "int"),
			"TIMESTAMP_X" => Array("~FIELD_NAME" => "CS.TIMESTAMP_X", "FIELD_NAME" => $DB->DateToCharFunction("CS.TIMESTAMP_X").' as TIMESTAMP_X', "FIELD_TYPE" => "date"),
			"DATE_CREATE" => Array("~FIELD_NAME" => "CS.DATE_CREATE", "FIELD_NAME" => $DB->DateToCharFunction("CS.DATE_CREATE").' as DATE_CREATE', "FIELD_TYPE" => "date"),
			"DAV_EXCH_CAL" => Array("FIELD_NAME" => "CS.DAV_EXCH_CAL", "FIELD_TYPE" => "string"), // Exchange calendar
			"DAV_EXCH_MOD" => Array("FIELD_NAME" => "CS.DAV_EXCH_MOD", "FIELD_TYPE" => "string"), // Exchange calendar modification label
			"CAL_DAV_CON" => Array("FIELD_NAME" => "CS.CAL_DAV_CON", "FIELD_TYPE" => "string"), // CalDAV connection
			"CAL_DAV_CAL" => Array("FIELD_NAME" => "CS.CAL_DAV_CAL", "FIELD_TYPE" => "string"), // CalDAV calendar
			"CAL_DAV_MOD" => Array("FIELD_NAME" => "CS.CAL_DAV_MOD", "FIELD_TYPE" => "string"), // CalDAV calendar modification label
			"IS_EXCHANGE" => Array("FIELD_NAME" => "CS.IS_EXCHANGE", "FIELD_TYPE" => "string"),
			"SYNC_TOKEN" => Array("FIELD_NAME" => "CS.SYNC_TOKEN", "FIELD_TYPE" => "string"),
		);
		return self::$Fields;
	}

	public static function GetList($params = [])
	{
		global $DB;
		$result = false;
		$filter = $params['arFilter'];
		$sort = isset($params['arOrder']) ? $params['arOrder'] : Array('SORT' => 'asc');
		$params['joinTypeInfo'] = !!$params['joinTypeInfo'];
		$checkPermissions = $params['checkPermissions'] !== false;
		$params['checkPermissions'] = $checkPermissions;
		$getPermissions = $params['getPermissions'] !== false;
		$params['getPermissions'] = $getPermissions;
		$userId = $params['userId'] ? (int)$params['userId'] : CCalendar::GetCurUserId();
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
				$arSectionIds = $res["arSectionIds"];
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

		if (!$cacheEnabled || !isset($arSectionIds))
		{
			$arFields = self::GetFields();
			$arSqlSearch = [];
			if(is_array($filter))
			{
				$filter_keys = array_keys($filter);
				for($i = 0, $l = count($filter_keys); $i<$l; $i++)
				{
					$n = mb_strtoupper($filter_keys[$i]);
					$val = $filter[$filter_keys[$i]];
					if(is_string($val)  && $val == '' || strval($val)=="NOT_REF")
						continue;
					if ($n == 'ID' || $n == 'XML_ID' || $n == 'OWNER_ID')
					{
						if(is_array($val))
						{
							$val = array_map(array($DB, "ForSQL"), $val);
							$arSqlSearch[] = 'CS.'.$n.' IN (\''.implode('\',\'', $val).'\')';
						}
						else
						{
							$arSqlSearch[] = GetFilterQuery("CS.".$n, $val, 'N');
						}
					}
					elseif($n == '>ID' && intval($val) > 0)
					{
						$arSqlSearch[] = "CS.ID > ".intval($val);
					}
					elseif($n == 'ACTIVE' && $val == "Y")
					{
						$arSqlSearch[] = "CS.ACTIVE='Y'";
					}
					elseif ($n == 'CAL_TYPE' && is_array($val))
					{
						$params['joinTypeInfo'] = true;
						$strType = "";
						foreach($val as $type)
							$strType .= ",'".$DB->ForSql($type)."'";
						$arSqlSearch[] = "CS.CAL_TYPE in (".trim($strType, ", ").")";
						$arSqlSearch[] = "CT.ACTIVE='Y'";
					}
					elseif(isset($arFields[$n]))
					{
						$arSqlSearch[] = GetFilterQuery($arFields[$n]["FIELD_NAME"], $val, (isset($arFields[$n]["PROCENT"]) &&
						$arFields[$n]["PROCENT"] == "N") ? "N" : "Y");
					}
				}
			}

			$strOrderBy = '';
			foreach($sort as $by => $order)
			{
				if(isset($arFields[mb_strtoupper($by)]))
				{
					$byName = isset($arFields[mb_strtoupper($by)]["~FIELD_NAME"]) ? $arFields[mb_strtoupper($by)]["~FIELD_NAME"] : $arFields[mb_strtoupper($by)]["FIELD_NAME"];
					$strOrderBy .= $byName.' '.(mb_strtolower($order) == 'desc'?'desc'.($DB->type == "ORACLE"?" NULLS LAST":""):'asc'.($DB->type == "ORACLE"?" NULLS FIRST":"")).',';
				}
			}

			if($strOrderBy <> '')
				$strOrderBy = "ORDER BY ".rtrim($strOrderBy, ",");

			$strSqlSearch = GetFilterSqlSearch($arSqlSearch);

			if (isset($filter['ADDITIONAL_IDS']) && is_array($filter['ADDITIONAL_IDS']) && count($filter['ADDITIONAL_IDS']) > 0)
			{
				$strTypes = "";
				foreach($filter['ADDITIONAL_IDS'] as $adid)
					$strTypes .= ",".intval($adid);
				$strSqlSearch = '('.$strSqlSearch.') OR ID in('.trim($strTypes, ', ').')';
			}

			$strLimit = '';
			if (isset($params['limit']) && intval($params['limit']) > 0)
			{
				$strLimit = 'LIMIT '.intval($params['limit']);
			}

			$select = 'CS.*';
			$from = 'b_calendar_section CS';

			// Fetch types info into selection
			if ($params['joinTypeInfo'])
			{
				$select .= ", CT.NAME AS TYPE_NAME, CT.DESCRIPTION AS TYPE_DESC";
				$from .= "\n INNER JOIN b_calendar_type CT ON (CS.CAL_TYPE=CT.XML_ID)";
			}

			if ($getPermissions)
			{
				$select .= ", CAP.ACCESS_CODE, CAP.TASK_ID";
				$from .= "\n LEFT JOIN b_calendar_access CAP ON (CS.ID=CAP.SECT_ID)";
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

			$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$result = [];
			$arSectionIds = [];

			$isExchangeEnabled = CCalendar::IsExchangeEnabled();
			$isCalDAVEnabled = CCalendar::IsCalDAVEnabled();

			while($section = $res->Fetch())
			{
				$sectId = $section['ID'];
				$sectionType = $section['CAL_TYPE'];

				// Outlook js
				if (!in_array($sectId, $arSectionIds)
					&& CCalendar::IsIntranetEnabled()
					&& $sectionType !== CCalendarLocation::TYPE
				)
				{
					$section['OUTLOOK_JS'] = CCalendarSect::GetOutlookLink(array(
							'ID' => intval($sectId),
							'XML_ID' => $section['XML_ID'],
							'TYPE' => $sectionType,
							'NAME' => $section['NAME'],
							'PREFIX' => CCalendar::GetOwnerName($sectionType, $section['OWNER_ID']),
							'LINK_URL' => CCalendar::GetOuterUrl()
					));
				}

				if ($checkPermissions)
				{
					self::HandlePermission($section);
				}
				if (in_array($sectId, $arSectionIds))
				{
					continue;
				}
				unset($section['ACCESS_CODE'], $section['TASK_ID']);
				$section['COLOR'] = CCalendar::Color($section['COLOR'], true);
				$arSectionIds[] = $sectId;
				$section['EXPORT'] = array('ALLOW' => true, 'LINK' => self::GetExportLink($section['ID'], $sectionType, $section['OWNER_ID']));

				if ($sectionType == 'user')
				{
					$section['IS_EXCHANGE'] = $section["DAV_EXCH_CAL"] <> '' && $isExchangeEnabled;
					if ($section["CAL_DAV_CON"] && $isCalDAVEnabled)
					{
						$section["CAL_DAV_CON"] = intval($section["CAL_DAV_CON"]);
						$resCon = CDavConnection::GetList(array("ID" => "ASC"), array("ID" => $section["CAL_DAV_CON"]));

						if ($con = $resCon->Fetch())
							$section['CAL_DAV_CON'] = $con["ID"];
						else
							$section['CAL_DAV_CON'] = false;
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
				$cache->EndDataCache(array(
					"arResult" => $result,
					"arSectionIds" => $arSectionIds,
					"permissions" => self::$Permissions
				));
			}
		}

		if (($checkPermissions || $getPermissions) && count($arSectionIds) > 0 && $userId >= 0)
		{
			$res = [];
			$arAccessCodes = [];

			$settings = CCalendar::GetSettings(array('request' => false));
			foreach($result as $section)
			{
				$sectId = $section['ID'];
				$isOwner = $section['CAL_TYPE'] === 'user' && (int)$section['OWNER_ID'] === $userId;

				if(!$userId)
				{
					$userId = CCalendar::GetUserId();
				}

				$isManager = Loader::includeModule('intranet') && $section['CAL_TYPE'] == 'user' && $settings['dep_manager_sub'] && Bitrix\Calendar\Util::isManagerForUser($userId, $section['OWNER_ID']);

				if(
					$isOwner
					|| $isManager
					|| self::CanDo('calendar_view_time', $sectId, $userId))
				{
					$canView = CCalendarType::CanDo('calendar_type_view', $section['CAL_TYPE']);

					$section['PERM'] = [
						'view_time' => false,
						'view_title' => false,
						'view_full' => false,
						'add' => false,
						'edit' => false,
						'edit_section' => false,
						'access' => false
					];

					if ($canView)
					{
						$section['PERM'] = [
								'view_time' => $isManager
										|| $isOwner
										|| self::CanDo('calendar_view_time', $sectId, $userId),
								'view_title' => $isManager
										|| $isOwner
										|| self::CanDo('calendar_view_title', $sectId, $userId),
								'view_full' => $isManager
										|| $isOwner
										|| self::CanDo('calendar_view_full', $sectId, $userId)
						];
					}

					$canEdit = CCalendarType::CanDo('calendar_type_edit', $section['CAL_TYPE']);

					if ($canView && $canEdit)
					{
						$section['PERM']['add'] = $isOwner
								|| self::CanDo('calendar_add', $sectId, $userId);
						$section['PERM']['edit'] = $isOwner
								|| self::CanDo('calendar_edit', $sectId, $userId);
						$section['PERM']['edit_section'] = $isOwner
								|| self::CanDo('calendar_edit_section', $sectId, $userId);
					}

					$hasFullAccess = CCalendarType::CanDo('calendar_type_edit_access', $section['CAL_TYPE']);

					if ($hasFullAccess)
					{
						$section['PERM']['access'] = $isOwner || self::CanDo('calendar_edit_access', $sectId, $userId);
					}

					if($getPermissions || $isOwner || self::CanDo('calendar_edit_access', $sectId, $userId))
					{
						$section['ACCESS'] = [];
						if(is_array(self::$Permissions[$sectId]) && count(self::$Permissions[$sectId]) > 0)
						{
							// Add codes to get they full names for interface
							$arAccessCodes = array_merge($arAccessCodes, array_keys(self::$Permissions[$sectId]));
							$section['ACCESS'] = self::$Permissions[$sectId];
						}
					}

					$res[] = $section;
				}
			}
			CCalendar::PushAccessNames($arAccessCodes);
			$result = $res;
		}

		return $result;
	}

	public static function GetById(int $id = 0, bool $checkPermissions = true, bool $bRerequest = false)
	{
		$id = (int)$id;
		if ($id > 0)
		{
			if (!isset(self::$sections[$id]) || $bRerequest)
			{
				$section = self::GetList(array('arFilter' => array('ID' => $id),
					'checkPermissions' => $checkPermissions
				));
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
		$checkPermissions = $params['checkPermissions'] !== false;
		$userId = isset($params['userId']) ? intval($params['userId']) : CCalendar::GetCurUserId();

		$arResult = [];
		$arSectionIds = [];
		$sqlSearch = "";

		$select = '';
		$from = '';
		if ($checkPermissions)
		{
			$select .= ", CAP.ACCESS_CODE, CAP.TASK_ID";
			if($DB->type == "MYSQL")
			{
				$from .= "\n LEFT JOIN b_calendar_access CAP ON (CS.ID=CAP.SECT_ID)";
			}
			elseif($DB->type == "MSSQL")
			{
				$from .= "\n LEFT JOIN b_calendar_access CAP ON (convert(varchar,CS.ID)=CAP.SECT_ID)";
			}
			elseif($DB->type == "ORACLE")
			{
				$from .= "\n LEFT JOIN b_calendar_access CAP ON (TO_CHAR(CS.ID)=CAP.SECT_ID)";
			}
		}

		// Common types
		$strTypes = "";
		if (isset($params['TYPES']) && is_array($params['TYPES']))
		{
			foreach($params['TYPES'] as $type)
				$strTypes .= ",'".$DB->ForSql($type)."'";

			$strTypes = trim($strTypes, ", ");
			if ($strTypes != "")
				$sqlSearch .= "(CS.CAL_TYPE in (".$strTypes."))";
		}

		// Group's calendars
		$strGroups = "0";
		if (is_array($params['GROUPS']) && count($params['GROUPS']) > 0)
		{
			foreach($params['GROUPS'] as $ownerId)
				if (intval($ownerId) > 0)
					$strGroups .= ",".intval($ownerId);

			if ($strGroups != "0")
			{
				if ($sqlSearch != "")
					$sqlSearch .= " OR ";
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

		if (is_array($params['USERS']) && count($params['USERS']) > 0)
		{
			foreach($params['USERS'] as $ownerId)
			{
				if (intval($ownerId) > 0)
				{
					$strUsers .= ",".intval($ownerId);
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

		if ($checkPermissions && count($arSectionIds) > 0)
		{
			$res = [];
			$sectIds = [];
			foreach($arResult as $sect)
			{
				$sectId = $sect['ID'];
				$ownerId = $sect['OWNER_ID'];

				if (self::CanDo('calendar_view_time', $sectId) && !in_array($sectId, $sectIds))
				{
					$sect['PERM'] = array(
						'view_time' => self::CanDo('calendar_view_time', $sectId),
						'view_title' => self::CanDo('calendar_view_title', $sectId),
						'view_full' => self::CanDo('calendar_view_full', $sectId),
						'add' => self::CanDo('calendar_add', $sectId),
						'edit' => self::CanDo('calendar_edit', $sectId),
						'edit_section' => self::CanDo('calendar_edit_section', $sectId),
						'access' => self::CanDo('calendar_edit_access', $sectId)
					);

					if ($sect['CAL_TYPE'] == 'user')
					{
						if (isset($sect['USER_NAME'], $sect['USER_LAST_NAME']))
						{
							$sect['OWNER_NAME'] = CCalendar::GetUserName(array("NAME" => $sect['USER_NAME'], "LAST_NAME" => $sect['USER_LAST_NAME'], "LOGIN" => $sect['USER_LOGIN'], "ID" => $ownerId, "SECOND_NAME" => $sect['USER_SECOND_NAME']));
							unset($sect['USER_LOGIN']);
							unset($sect['USER_LAST_NAME']);
							unset($sect['USER_SECOND_NAME']);
							unset($sect['USER_NAME']);
						}
						else
						{
							$sect['OWNER_NAME'] = CCalendar::GetUserName($ownerId);
						}
					}
					elseif ($sect['CAL_TYPE'] == 'group' && isset($params['arGroups']))
					{
						$sect['OWNER_NAME'] = $params['arGroups'][$ownerId]['NAME'];
					}

					$res[] = $sect;
					$sectIds[] = $sectId;
				}
			}
			$arResult = $res;
		}

		return $arResult;
	}

	public static function CheckFields($arFields)
	{
		return true;
	}

	public static function Edit($params)
	{
		global $DB;
		$sectionFields = $params['arFields'];
		if(!self::CheckFields($sectionFields))
		{
			return false;
		}

		$userId = intval(isset($params['userId']) ? $params['userId'] : CCalendar::GetCurUserId());
		//if (!CCalendarSect::CanDo('calendar_edit_section', $ID))
		//	return CCalendar::ThrowError('EC_ACCESS_DENIED');

		$isNewSection = !isset($sectionFields['ID']) || $sectionFields['ID'] <= 0;
		if (isset($sectionFields['COLOR']) || $isNewSection)
			$sectionFields['COLOR'] = CCalendar::Color($sectionFields['COLOR']);

		$sectionFields['TIMESTAMP_X'] = CCalendar::Date(time());

		if (is_array($sectionFields['EXPORT']))
		{
			$sectionFields['EXPORT'] = [
				'ALLOW' => !!$sectionFields['EXPORT']['ALLOW'],
				'SET' => (in_array($sectionFields['EXPORT']['set'], array('all', '3_9', '6_12'))) ? $sectionFields['EXPORT']['set'] : 'all'
			];

			$sectionFields['EXPORT'] = serialize($sectionFields['EXPORT']);
		}

		if ($isNewSection) // Add
		{
			if (!isset($sectionFields['DATE_CREATE']))
				$sectionFields['DATE_CREATE'] = CCalendar::Date(time());

			if ((!isset($sectionFields['CREATED_BY']) || !$sectionFields['CREATED_BY']))
				$sectionFields['CREATED_BY'] = CCalendar::GetCurUserId();

			unset($sectionFields['ID']);
			$id = $DB->Add("b_calendar_section", $sectionFields, array('DESCRIPTION'));
		}
		else // Update
		{
			$id = $sectionFields['ID'];
			unset($sectionFields['ID']);
			$strUpdate = $DB->PrepareUpdate("b_calendar_section", $sectionFields);
			$strSql =
				"UPDATE b_calendar_section SET ".
					$strUpdate.
				" WHERE ID=".intval($id);

			$DB->QueryBind($strSql, array('DESCRIPTION' => $sectionFields['DESCRIPTION']));
		}

		//SaveAccess
		if ($id > 0 && is_array($sectionFields['ACCESS']))
		{
			if (($sectionFields['CAL_TYPE'] == 'user' && $sectionFields['OWNER_ID'] == $userId) || self::CanDo('calendar_edit_access', $id))
			{
				if (empty($sectionFields['ACCESS']))
					self::SavePermissions($id, CCalendarSect::GetDefaultAccess($sectionFields['CAL_TYPE'], $sectionFields['OWNER_ID']));
				else
					self::SavePermissions($id, $sectionFields['ACCESS']);
			}
			elseif($isNewSection)
			{
				self::SavePermissions($id, CCalendarSect::GetDefaultAccess($sectionFields['CAL_TYPE'], $sectionFields['OWNER_ID']));
			}
		}

		if ($isNewSection && $id > 0 && !isset($sectionFields['ACCESS']))
		{
			self::SavePermissions($id, CCalendarSect::GetDefaultAccess($sectionFields['CAL_TYPE'], $sectionFields['OWNER_ID']));
		}

		CCalendar::ClearCache(array('section_list', 'event_list'));

		if ($id > 0 && isset(self::$Permissions[$id]))
		{
			unset(self::$Permissions[$id]);
			self::$arOp = [];
		}

		if ($isNewSection)
		{
			foreach(\Bitrix\Main\EventManager::getInstance()->findEventHandlers("calendar", "OnAfterCalendarSectionAdd") as $event)
			{
				ExecuteModuleEventEx($event, array($id, $sectionFields));
			}
		}
		else
		{
			foreach(\Bitrix\Main\EventManager::getInstance()->findEventHandlers("calendar", "OnAfterCalendarSectionUpdate") as $event)
			{
				ExecuteModuleEventEx($event, array($id, $sectionFields));
			}
		}

		$pullUserId = (int)$sectionFields['CREATED_BY'] > 0 ? (int)$sectionFields['CREATED_BY'] : $userId;
		\Bitrix\Calendar\Util::addPullEvent(
			'edit_section',
			$pullUserId,
			[
				'fields' => $sectionFields,
				'newSection' => $isNewSection
			]
		);

		return $id;
	}

	public static function Delete($id, $checkPermissions = true)
	{
		global $DB;
		if ($checkPermissions !== false && !CCalendarSect::CanDo('calendar_edit_section', $id))
			return CCalendar::ThrowError('EC_ACCESS_DENIED');

		$id = intval($id);

		$sectionFields = CCalendarSect::GetById($id);
		$meetingIds = [];
		if (\Bitrix\Calendar\Util::isSectionStructureConverted())
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
				$meetingIds[] = intval($ev['PARENT_ID']);
				CCalendarLiveFeed::OnDeleteCalendarEventEntry($ev['PARENT_ID']);
			}

			$pullUserId = (int)$ev['CREATED_BY'] > 0 ? (int)$ev['CREATED_BY'] : \CCalendar::GetCurUserId();
			if ($pullUserId)
			{
				Bitrix\Calendar\Util::addPullEvent(
					'delete_event',
					$pullUserId,
					[
						'fields' => $ev
					]
				);
			}
		}

		if (count($meetingIds) > 0)
		{
			$DB->Query("DELETE from b_calendar_event WHERE PARENT_ID in (".implode(',', $meetingIds).")", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		// Del link from table
		if (!\Bitrix\Calendar\Util::isSectionStructureConverted())
		{
			$DB->Query("DELETE FROM b_calendar_event_sect WHERE SECT_ID=".$id, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}

		// Del from
		$DB->Query("DELETE FROM b_calendar_section WHERE ID=".$id, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		CCalendarEvent::DeleteEmpty();
		self::CleanAccessTable();

		CCalendar::ClearCache(array('section_list', 'event_list'));

		foreach(\Bitrix\Main\EventManager::getInstance()->findEventHandlers("calendar", "OnAfterCalendarSectionDelete") as $event)
		{
			ExecuteModuleEventEx($event, array($id));
		}

		\Bitrix\Calendar\Util::addPullEvent(
			'delete_section',
			$sectionFields['CREATED_BY'],
			[
				'fields' => $sectionFields
			]
		);

		return true;
	}

	public static function CreateDefault($params = [])
	{
		if ($params['type'] == 'user' || $params['type'] == 'group')
			$name = CCalendar::GetOwnerName($params['type'], $params['ownerId']);
		else
			$name = GetMessage('EC_DEF_SECT_GROUP_CAL');

		$userId = $params['type'] == 'user' ? $params['ownerId'] : CCalendar::GetCurUserId();

		if ($userId > 0)
		{
			$arFields = [
				'CAL_TYPE' => $params['type'],
				'NAME' => $name,
				'DESCRIPTION' => GetMessage('EC_DEF_SECT_DESC'),
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
					'access' => true
				]
			];

			$arFields['ID'] = self::Edit([
				'arFields' => $arFields,
				'userId' => $userId
			]);

			if ($arFields['ID'] > 0)
				return $arFields;
		}
		return false;
	}

	public static function SavePermissions($sectId, $arTaskPerm)
	{
		global $DB;
		$DB->Query("DELETE FROM b_calendar_access WHERE SECT_ID='".intval($sectId)."'", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if (is_array($arTaskPerm))
		{
			foreach($arTaskPerm as $accessCode => $taskId)
			{
				$arInsert = $DB->PrepareInsert("b_calendar_access", array("ACCESS_CODE" => $accessCode, "TASK_ID" => intval($taskId), "SECT_ID" => intval($sectId)));
				$strSql = "INSERT INTO b_calendar_access(".$arInsert[0].") VALUES(".$arInsert[1].")";
				$DB->Query($strSql , false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}
	}

	public static function GetArrayPermissions($arSections = [])
	{
		global $DB;
		$s = "'0'";
		foreach($arSections as $id)
			if ($id > 0)
				$s .= ",'".intval($id)."'";

		if ($DB->type == "MYSQL")
		{
			$strSql = 'SELECT SC.ID, CAP.ACCESS_CODE, CAP.TASK_ID, SC.CAL_TYPE, SC.OWNER_ID, SC.CREATED_BY
				FROM b_calendar_section SC
				LEFT JOIN b_calendar_access CAP ON (SC.ID=CAP.SECT_ID)
				WHERE SC.ID in ('.$s.')';
		}
		elseif($DB->type == "MSSQL")
		{
			$strSql = 'SELECT SC.ID, CAP.ACCESS_CODE, CAP.TASK_ID, SC.CAL_TYPE, SC.OWNER_ID, SC.CREATED_BY
				FROM b_calendar_section SC
				LEFT JOIN b_calendar_access CAP ON (convert(varchar,SC.ID)=CAP.SECT_ID)
				WHERE SC.ID in ('.$s.')';
		}
		elseif($DB->type == "ORACLE")
		{
			$strSql = 'SELECT SC.ID, CAP.ACCESS_CODE, CAP.TASK_ID, SC.CAL_TYPE, SC.OWNER_ID, SC.CREATED_BY
				FROM b_calendar_section SC
				LEFT JOIN b_calendar_access CAP ON (TO_CHAR(SC.ID)=CAP.SECT_ID)
				WHERE SC.ID in ('.$s.')';
		}


		$res = $DB->Query($strSql , false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while($arRes = $res->Fetch())
		{
			if ($arRes['ID'] > 0)
				self::HandlePermission($arRes);
		}
		return self::$Permissions;
	}

	public static function SetClearOperationCache($val = true)
	{
		self::$bClearOperationCache = $val;
	}

	public static function CanDo($operation, $sectId = 0, $userId = false)
	{
		global $USER;

		if (!$userId)
		{
			$userId = CCalendar::GetCurUserId();
		}

		if (!isset($USER) || !is_object($USER) || !$sectId || !($USER instanceof \CUser))
		{
			return false;
		}

		if ($userId === CCalendar::GetCurUserId()
			&& $USER->CanDoOperation('edit_php'))
		{
			return true;
		}

		if ((CCalendar::GetType() == 'group' || CCalendar::GetType() == 'user' || CCalendar::IsBitrix24())
			&& CCalendar::IsSocNet() && CCalendar::IsSocnetAdmin())
		{
			return true;
		}

		$res = in_array($operation, self::GetOperations($sectId, $userId));

		self::$bClearOperationCache = false;
		return $res;
	}

	public static function GetOperations($sectId, $userId = false)
	{
		global $USER;
		if (!$userId)
			$userId = CCalendar::GetCurUserId();

		$arCodes = [];
		$rCodes = CAccess::GetUserCodes($userId);
		while($code = $rCodes->Fetch())
			$arCodes[] = $code['ACCESS_CODE'];

		if (!in_array('G2', $arCodes))
			$arCodes[] = 'G2';

		if (!in_array('AU', $arCodes) && $USER && $USER->GetId() == $userId)
			$arCodes[] = 'AU';

		$key = $sectId.'|'.implode(',', $arCodes);
		if (self::$bClearOperationCache || !is_array(self::$arOp[$key]))
		{
			if (!isset(self::$Permissions[$sectId]))
				self::GetArrayPermissions(array($sectId));
			$perms = self::$Permissions[$sectId];

			self::$arOp[$key] = [];
			if (is_array($perms))
			{
				foreach ($perms as $code => $taskId)
				{
					if (in_array($code, $arCodes))
					{
						self::$arOp[$key] = array_merge(self::$arOp[$key], CTask::GetOperations($taskId, true));
					}
				}
			}
		}
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
			if (intval($id) > 0)
			{
				$strIds[] = intval($id);
				$result[intval($id)] = 0;
			}
		$strIds = implode(',', $strIds);

		$strSql = "SELECT ID, CAL_DAV_CON FROM b_calendar_section WHERE ID in (".$strIds.")";
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		while ($arRes = $res->Fetch())
			$result[$arRes['ID']] = ($arRes['CAL_DAV_CON'] > 0) ? intval($arRes['CAL_DAV_CON']) : 0;

		if (!is_array($section))
			return $result[$section];

		return $result;
	}

	public static function GetExportLink($sectionId, $type = '', $ownerId = false)
	{
		$userId = CCalendar::GetCurUserId();
		$params = '';
		if ($type !== false)
			$params .= '&type='.mb_strtolower($type);
		if ($ownerId !== false)
			$params .=  '&owner='.intval($ownerId);
		return $params.'&ncc=1&user='.intval($userId).'&'.'sec_id='.intval($sectionId).'&sign='.self::GetSign($userId, $sectionId)
			.'&bx_hit_hash='.self::GetAuthHash();
	}

	function GetSPExportLink()
	{
		$userId = CCalendar::GetCurUserId();
		return '&user_id='.$userId.'&sign='.self::GetSign($userId, 'superposed_calendars');
	}

	public static function GetOutlookLink($Params)
	{
		if (Bitrix\Main\Loader::includeModule('intranet'))
			return CIntranetUtils::GetStsSyncURL($Params);
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
		$userId = intval($Params['userId']);
		$sign = $Params['sign'];
		$type = mb_strtolower($Params['type']);
		$ownerId = intval($Params['ownerId']);
		$bCache = false;

		$GLOBALS['APPLICATION']->RestartBuffer();

		if (!self::CheckSign($sign, $userId, $sectId))
			return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));

		$arSections = self::GetList(
			array(
				'arFilter' => array('ID' => $sectId),
				'checkPermissions' => false
			));

		if ($arSections && $arSections[0] && $arSections[0]['EXPORT'] && $arSections[0]['EXPORT']['ALLOW'])
		{
			$arSection = $arSections[0];
			$arEvents = CCalendarEvent::GetList(
				array(
					'arFilter' => array(
						'SECTION' => $arSection['ID']
					),
					'getUserfields' => false,
					'parseRecursion' => false,
					'fetchAttendees' => false,
					'fetchMeetings' => true,
					'userId' => $userId
				)
			);
			$iCalEvents = self::FormatICal($arSection, $arEvents);
		}
		else
		{
			return CCalendar::ThrowError(GetMessage('EC_ACCESS_DENIED'));
		}

		self::ShowICalHeaders();
		echo $iCalEvents;
		exit();
	}

	function ExtendExportEventsArray($arEvents, $arCalEx)
	{
		for($i = 0, $l = count($arEvents); $i < $l; $i++)
		{
			$calId = $arEvents[$i]['IBLOCK_SECTION_ID'];
			if (!isset($arCalEx[$calId]))
				continue;
			$arEvents[$i]['NAME'] = $arEvents[$i]['NAME'].' ['.$arCalEx[$calId]['SP_PARAMS']['NAME'].' :: '.$arCalEx[$calId]['NAME'].']';
		}
		return $arEvents;
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

			if($rrule && isset($rrule['FREQ']) && $rrule['FREQ'] != 'NONE')
			{
				$period = 'RRULE:FREQ='.$rrule['FREQ'].';';
				$period .= 'INTERVAL='.$rrule['INTERVAL'].';';
				if ($rrule['FREQ'] == 'WEEKLY')
				{
					$period .= 'BYDAY='.implode(',', $rrule['BYDAY']).';';
				}

				if (isset($rrule['COUNT']) && intval($rrule['COUNT']) > 0)
				{
					$period .= 'COUNT='.intval($rrule['COUNT']).';';
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
		if ($calendarId[0] === \CCalendar::TASK_SECTION_ID)
		{
			return \CCalendar::Date(round(time() / 180) * 180);
		}

		$sectionId = intval($calendarId[0]);
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
			$arId = array($arId);

		$arId = array_unique($arId);
		$strIds = [];
		foreach($arId as $id)
		{
			if (intval($id) > 0)
			{
				$strIds[] = intval($id);
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
			$access = array('G2' => CCalendar::GetAccessTasksByName('calendar_section', 'calendar_view_time'));
		else
			$access = array('G2' => CCalendar::GetAccessTasksByName('calendar_section', 'calendar_view'));

		if ($type == 'user')
		{
		}
		elseif ($type == 'group' && $ownerId > 0)
		{
			$access['SG'.$ownerId.'_A'] = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_access');
			$access['SG'.$ownerId.'_E'] = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_edit');
			$access['SG'.$ownerId.'_K'] = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_edit');
		}
		else
		{
			$access['G2'] = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_edit');
		}

		// Creator of the section
		if ($type !== 'user')
		{
			$access['U'.CCalendar::GetUserId()] = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_access');
		}

		$arAccessCodes = [];
		foreach($access as $code => $o)
			$arAccessCodes[] = $code;

		CCalendar::PushAccessNames($arAccessCodes);
		return $access;
	}

	public static function GetAuthHash()
	{
		global $USER, $APPLICATION;
		if ((!isset(self::$authHashiCal) || empty(self::$authHashiCal)) && $USER && is_object($USER))
		{
			self::$authHashiCal = $USER->AddHitAuthHash($APPLICATION->GetCurPage());
		}
		return self::$authHashiCal;
	}

	public static function CheckAuthHash()
	{
		global $USER;
		if ($_REQUEST['bx_hit_hash'] <> '') // $_REQUEST['bx_hit_hash']
			return $USER->LoginHitByHash();

		return false;
	}

	public static function GetLastUsedSection($type, $ownerId, $userId)
	{
		$userSettings = \Bitrix\Calendar\UserSettings::get($userId);
		return $userSettings['lastUsedSection'];
	}

	public static function GetSectionForOwner($type, $ownerId, $autoCreate = true)
	{
		$sectionId = false;
		$autoCreated = false;
		$section = false;

		$res = self::GetList(
			array('arFilter' =>
				array(
					'CAL_TYPE' => $type,
					'OWNER_ID' => $ownerId,
					'DELETED' => 'N',
					'ACTIVE' => 'Y'
				),
				'checkPermissions' => false
			));

		foreach($res as $sect)
		{
			$sectId = $sect['ID'];
			$ownerId = $sect['OWNER_ID'];

			if (self::CheckGoogleVirtualSection($sect['GAPI_CALENDAR_ID']))
				continue;

			$section = $sect;
			$sectionId = $sect['ID'];
			break;
		}

		if (!$section && $autoCreate)
		{
			$section = self::CreateDefault(array(
				'type' => $type,
				'ownerId' => $ownerId
			));
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
			if (!is_array(self::$Permissions[$sectionId]))
				self::$Permissions[$sectionId] = [];

			if($section['ACCESS_CODE'] != '' && $section['ACCESS_CODE'] != '0' && $section['TASK_ID'] > 0)
				self::$Permissions[$sectionId][$section['ACCESS_CODE']] = $section['TASK_ID'];


			if($section['CAL_TYPE'] != 'group' && $section['OWNER_ID'] > 0) // Owner for user or other calendar types
				self::$Permissions[$sectionId]['U'.$section['OWNER_ID']] = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_access');

			if($section['CAL_TYPE'] == 'group' && $section['OWNER_ID'] > 0) // Owner for group
				self::$Permissions[$sectionId]['SG'.$section['OWNER_ID'].'_A'] = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_access');
		}
	}


	public static function CleanAccessTable()
	{
		global $DB;

		$res = $DB->Query(
			"SELECT DISTINCT CA.SECT_ID from b_calendar_access CA
			LEFT JOIN b_calendar_section CS ON (CA.SECT_ID=CS.ID)
			WHERE concat('',CA.SECT_ID * 1)=CA.SECT_ID AND CS.ID is null",
			false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$items = [];
		while($r = $res->Fetch())
		{
			$items[] = "'".intval($r['SECT_ID'])."'";
		}

		// Clean from 'b_calendar_event'
		if(count($items))
		{
			$DB->Query("DELETE FROM b_calendar_access WHERE SECT_ID in (".implode(',', $items).")", false,
					"FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}
	}

	/**
	 * @param string|null $davXmlId
	 * @param string|null $externalType
	 * @return bool
	 */
	public static function CheckGoogleVirtualSection($davXmlId = '', $externalType = ''): bool
	{
		return $davXmlId !== '' && (preg_match('/@virtual\/events\//i', $davXmlId)
			|| preg_match('/@group\.v\.calendar\.google/i', $davXmlId)
			|| $externalType === \Bitrix\Calendar\Sync\Google\Dictionary::ACCESS_ROLE_TO_EXTERNAL_TYPE['reader']
			|| $externalType === \Bitrix\Calendar\Sync\Google\Dictionary::ACCESS_ROLE_TO_EXTERNAL_TYPE['freeBusyOrder']
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

	public static function GetSectionIdByEventId($id)
	{
		$resDb = \Bitrix\Calendar\Internals\EventTable::getList([
			'select' => ['SECTION_ID',],
			'filter' => ['ID' => $id],
		]);

		return $resDb->fetch();
	}
}
?>