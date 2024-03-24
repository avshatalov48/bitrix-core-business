<?

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\Model\TypeModel;
use Bitrix\Calendar\Access\TypeAccessController;
use Bitrix\Main\Loader;
use Bitrix\Calendar\Util;
use Bitrix\Main\UserTable;

class CCalendarType
{
	public const OPERATION_VIEW = 'calendar_type_view';
	public const OPERATION_ADD = 'calendar_type_add';
	public const OPERATION_EDIT = 'calendar_type_edit';
	public const OPERATION_EDIT_SECTION = 'calendar_type_edit_section';
	public const OPERATION_EDIT_ACCESS = 'calendar_type_edit_access';
	private static
		$Permissions = [],
		$arOp = [],
		$userOperationsCache = [];

	public static function GetList($params = [])
	{
		global $DB;
		$access = new CAccess();
		$access->UpdateCodes();
		$arFilter = $params['arFilter'] ?? null;
		$result = false;
		$cacheId = false;
		$cachePath = '';
		$arOrder = $params['arOrder'] ?? Array('XML_ID' => 'asc');
		$checkPermissions = ($params['checkPermissions'] ?? true) !== false;

		$bCache = CCalendar::CacheTime() > 0;

		if ($bCache)
		{
			$cache = new CPHPCache;
			$cacheId = serialize(array('type_list', $arFilter, $arOrder));
			$cachePath = CCalendar::CachePath().'type_list';

			if ($cache->InitCache(CCalendar::CacheTime(), $cacheId, $cachePath))
			{
				$res = $cache->GetVars();
				$result = $res["arResult"];
				$arTypeXmlIds = $res["arTypeXmlIds"];
			}
		}

		if (!$bCache || !isset($arTypeXmlIds))
		{
			$arFields = [
				"XML_ID" => ["FIELD_NAME" => "CT.XML_ID", "FIELD_TYPE" => "string"],
				"NAME" => ["FIELD_NAME" => "CT.NAME", "FIELD_TYPE" => "string"],
				"ACTIVE" => ["FIELD_NAME" => "CT.ACTIVE", "FIELD_TYPE" => "string"],
				"DESCRIPTION" => ["FIELD_NAME" => "CT.DESCRIPTION", "FIELD_TYPE" => "string"],
				"EXTERNAL_ID" => ["FIELD_NAME" => "CT.EXTERNAL_ID", "FIELD_TYPE" => "string"]
			];

			$arSqlSearch = [];
			if(is_array($arFilter))
			{
				$filter_keys = array_keys($arFilter);
				foreach ($filter_keys as $i => $value)
				{
					$n = mb_strtoupper($value);
					$val = $arFilter[$value];

					if (is_string($val) && !$val)
					{
						continue;
					}

					if ($n === 'XML_ID')
					{
						if (is_array($val))
						{
							$strXml = "";
							foreach($val as $xmlId)
							{
								$strXml .= ",'" . $DB->ForSql($xmlId) . "'";
							}
							$arSqlSearch[] = "CT.XML_ID in (".trim($strXml, ", ").")";
						}
						else
						{
							$arSqlSearch[] = GetFilterQuery("CT.XML_ID", $val, 'N');
						}
					}
					if ($n === 'EXTERNAL_ID')
					{
						$arSqlSearch[] = GetFilterQuery("CT.EXTERNAL_ID", $val, 'N');
					}
					elseif (isset($arFields[$n]))
					{
						$arSqlSearch[] = GetFilterQuery($arFields[$n]["FIELD_NAME"], $val);
					}
				}
			}

			$strOrderBy = '';
			foreach($arOrder as $by=>$order)
			{
				if (isset($arFields[mb_strtoupper($by)]))
				{
					$strOrderBy .= $arFields[mb_strtoupper($by)]["FIELD_NAME"] . ' '
						. (mb_strtolower($order) === 'desc' ? 'desc' : 'asc') . ',';
				}
			}

			if ($strOrderBy)
			{
				$strOrderBy = "ORDER BY " . rtrim($strOrderBy, ",");
			}

			$strSqlSearch = GetFilterSqlSearch($arSqlSearch);

			$strSql = "
				SELECT
					CT.*
				FROM
					b_calendar_type CT
				WHERE
					$strSqlSearch
				$strOrderBy";

			$res = $DB->Query($strSql, false, "Function: CCalendarType::GetList<br>Line: ".__LINE__);
			$result = [];
			$arTypeXmlIds = [];
			while($arRes = $res->Fetch())
			{
				$result[] = $arRes;
				$arTypeXmlIds[] = $arRes['XML_ID'];
			}

			if ($bCache && isset($cache))
			{
				$cache->StartDataCache(CCalendar::CacheTime(), $cacheId, $cachePath);
				$cache->EndDataCache(array(
					"arResult" => $result,
					"arTypeXmlIds" => $arTypeXmlIds
				));
			}
		}

		if ($checkPermissions && !empty($arTypeXmlIds))
		{
			$arPerm = self::GetArrayPermissions($arTypeXmlIds);
			$res = [];
			$arAccessCodes = [];
			$accessController = new TypeAccessController(CCalendar::GetCurUserId());

			if (is_array($result))
			{
				foreach($result as $type)
				{
					$typeXmlId = $type['XML_ID'];
					$typeModel = TypeModel::createFromXmlId($typeXmlId);
					$request = [
						ActionDictionary::ACTION_TYPE_VIEW => [],
						ActionDictionary::ACTION_TYPE_EDIT => [],
						ActionDictionary::ACTION_TYPE_ACCESS => [],
					];

					$result = $accessController->batchCheck($request, $typeModel);
					if ($result[ActionDictionary::ACTION_TYPE_VIEW])
					{
						$type['PERM'] = [
							'view' => true,
							'add' => $result[ActionDictionary::ACTION_TYPE_EDIT],
							'edit' => $result[ActionDictionary::ACTION_TYPE_EDIT],
							'edit_section' => $result[ActionDictionary::ACTION_TYPE_EDIT],
							'access' => $result[ActionDictionary::ACTION_TYPE_ACCESS],
						];

						if ($result[ActionDictionary::ACTION_TYPE_ACCESS])
						{
							$type['ACCESS'] = [];
							if (!empty($arPerm[$typeXmlId]))
							{
								// Add codes to get they full names for interface
								$currentAccessCodes = array_keys($arPerm[$typeXmlId]);
								foreach ($currentAccessCodes as $code)
								{
									if (!in_array($code, $arAccessCodes, true))
									{
										$arAccessCodes[] = $code;
									}
								}
								$type['ACCESS'] = $arPerm[$typeXmlId];
							}
						}
						$res[] = $type;
					}
				}
			}

			CCalendar::PushAccessNames($arAccessCodes);
			$result = $res;
		}

		return $result;
	}

	public static function Edit($params)
	{
		global $DB;
		$arFields = $params['arFields'];
		$XML_ID = preg_replace("/[^a-zA-Z0-9_]/i", "", $arFields['XML_ID']);
		$arFields['XML_ID'] = $XML_ID;
		if (!isset($arFields['XML_ID']) || $XML_ID == "")
		{
			return false;
		}

		//return $APPLICATION->ThrowException(GetMessage("EC_ACCESS_DENIED"));

		$access = $arFields['ACCESS'];
		unset($arFields['ACCESS']);

		if (count($arFields) > 1) // We have not only XML_ID
		{
			if ($params['NEW']) // Add
			{
				$strSql = "SELECT * FROM b_calendar_type WHERE XML_ID='".$DB->ForSql($XML_ID)."'";
				$res = $DB->Query($strSql, false, __LINE__);
				if (!($arRes = $res->Fetch()))
				{
					$arInsert = $DB->PrepareInsert("b_calendar_type", $arFields);
					$strSql ="INSERT INTO b_calendar_type(".$arInsert[0].") VALUES(".$arInsert[1].")";
					$DB->Query($strSql);
				}
				else
				{
					false;
				}
			}
			else // Update
			{
				unset($arFields['XML_ID']);
				if (!empty($arFields))
				{
					$strUpdate = $DB->PrepareUpdate("b_calendar_type", $arFields);
					$strSql =
						"UPDATE b_calendar_type SET ".
						$strUpdate.
						" WHERE XML_ID='".$DB->ForSql($XML_ID)."'";
					$DB->QueryBind($strSql, array('DESCRIPTION' => $arFields['DESCRIPTION']));
				}
			}
		}

		//SaveAccess
		$accessController = new TypeAccessController(CCalendar::GetUserId());
		$typeModel = TypeModel::createFromXmlId($arFields['XML_ID']);

		if (is_array($access) && $accessController->check(ActionDictionary::ACTION_TYPE_ACCESS, $typeModel))
		{
			self::SavePermissions($XML_ID, $access);
		}

		CCalendar::ClearCache('type_list');
		return $XML_ID;
	}

	public static function Delete($XML_ID)
	{
		global $DB;
		// Del types
		$DB->Query("DELETE FROM b_calendar_type WHERE XML_ID='".$DB->ForSql($XML_ID)."'", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		// Del access for types
		$DB->Query("DELETE FROM b_calendar_access WHERE SECT_ID='".$DB->ForSql($XML_ID)."'", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		// Del sections
		$DB->Query("DELETE FROM b_calendar_section WHERE CAL_TYPE='".$DB->ForSql($XML_ID)."'", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		// Del events
		$DB->Query("DELETE FROM b_calendar_event WHERE CAL_TYPE='".$DB->ForSql($XML_ID)."'", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		CCalendar::ClearCache(array('type_list', 'section_list', 'event_list'));

		return true;
	}

	public static function SavePermissions($type, $taskPerm)
	{
		global $DB;
		$DB->Query("DELETE FROM b_calendar_access WHERE SECT_ID='".$DB->ForSql($type)."'", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if (is_array($taskPerm))
		{
			foreach ($taskPerm as $accessCode => $taskId)
			{
				if (0 === strpos($accessCode, "SG"))
				{
					$accessCode = self::prepareGroupCode($accessCode);
				}

				$insert = $DB->PrepareInsert(
					"b_calendar_access",
					[
						"ACCESS_CODE" => $accessCode,
						"TASK_ID" => (int)$taskId,
						"SECT_ID" => $type
					]
				);

				$strSql = "INSERT INTO b_calendar_access(" . $insert[0] . ") VALUES(" . $insert[1] . ")";
				$DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
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


	public static function GetArrayPermissions($arTypes = [])
	{
		$sqlHelper = $connection = \Bitrix\Main\Application::getConnection()->getSqlHelper();
		$arTypes = array_map(static function($type) use ($sqlHelper) {
			return $sqlHelper->forSql($type);
		}, $arTypes);
		
		$query = \Bitrix\Calendar\Internals\AccessTable::query()
			->setSelect(['*'])
			->whereIn('SECT_ID', $arTypes)
			->exec()
		;

		while ($res = $query->fetch())
		{
			$xmlId = $res['SECT_ID'];
			if (!isset(self::$Permissions[$xmlId]) || !is_array(self::$Permissions[$xmlId]))
			{
				self::$Permissions[$xmlId] = [];
			}
			self::$Permissions[$xmlId][$res['ACCESS_CODE']] = $res['TASK_ID'];
		}
		foreach ($arTypes as $type)
		{
			if (!isset(self::$Permissions[$type]))
			{
				self::$Permissions[$type] = [];
			}
		}

		return self::$Permissions;
	}

	public static function CanDo($operation, $xmlId = 0, $userId = null)
	{
		global $USER;
		if ((!$USER || !is_object($USER)) || $USER->CanDoOperation('edit_php'))
		{
			return true;
		}

		if (!(int)$userId)
		{
			$userId = CCalendar::GetCurUserId();
		}

		if (
			CCalendar::IsBitrix24()
			&& Loader::includeModule('bitrix24')
			&& \CBitrix24::isPortalAdmin($userId)
		)
		{
			return true;
		}

		if (
			($xmlId === 'group' || $xmlId === 'user' || CCalendar::IsBitrix24())
			&& CCalendar::IsSocNet()
			&& CCalendar::IsSocnetAdmin()
		)
		{
			return true;
		}

		return in_array($operation, self::GetOperations($xmlId, $userId), true);
	}

	public static function GetOperations($xmlId, $userId = null)
	{
		global $USER;
		if (!$userId)
		{
			$userId = CCalendar::GetCurUserId();
		}

		$opCacheKey = $xmlId.'_'.$userId;

		if (isset(self::$userOperationsCache[$opCacheKey]) && is_array(self::$userOperationsCache[$opCacheKey]))
		{
			$result = self::$userOperationsCache[$opCacheKey];
		}
		else
		{
			$arCodes = [];
			if ($userId)
			{
				$arCodes = Util::getUserAccessCodes($userId);
			}

			if(!in_array('G2', $arCodes, true))
			{
				$arCodes[] = 'G2';
			}

			if($userId && !in_array('AU', $arCodes, true) && (int)$USER->GetId() === (int)$userId)
			{
				$arCodes[] = 'AU';
			}

			if($userId && !in_array('UA', $arCodes, true) && (int)$USER->GetId() === (int)$userId)
			{
				$arCodes[] = 'UA';
			}

			$key = $xmlId.'|'.implode(',', $arCodes);
			if(!isset(self::$arOp[$key]) || !is_array(self::$arOp[$key]))
			{
				if(!isset(self::$Permissions[$xmlId]))
				{
					self::GetArrayPermissions([$xmlId]);
				}
				$perms = self::$Permissions[$xmlId];

				self::$arOp[$key] = [];
				if(is_array($perms))
				{
					foreach($perms as $code => $taskId)
					{
						if (in_array($code, $arCodes, true))
						{
							self::$arOp[$key] = array_merge(self::$arOp[$key], CTask::GetOperations($taskId, true));
						}
					}
				}
			}
			$result = self::$userOperationsCache[$opCacheKey] = self::$arOp[$key];
		}

		return $result;
	}
}
?>