<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\UserToGroupTable;

class CAllSocNetLogCounter
{
	static $moduleManagerClass = ModuleManager::class;
	static $logClass = CSocNetLog::class;
	static $logCommentClass = CSocNetLogComments::class;
	static $allowedClass = CSocNetAllowed::class;
	static $optionClass = Option::class;
	static $logRightTableClass = Bitrix\Socialnetwork\LogRightTable::class;
	static $userTableClass = Bitrix\Main\UserTable::class;

	public static function GetSubSelect2($entityId, array $arParams = [])
	{
		return CSocNetLogCounter::GetSubSelect(
			[
				"LOG_ID" => $entityId,
				"TYPE" => (
					!empty($arParams["TYPE"])
						? $arParams["TYPE"]
						: CSocNetLogCounter::TYPE_LOG_ENTRY
				),
				"CODE" => (
					!empty($arParams["CODE"])
						? $arParams["CODE"]
						: false
				),
				"DECREMENT" => (bool) ($arParams["DECREMENT"] ?? false),
				"FOR_ALL_ACCESS" => (bool) ($arParams["FOR_ALL_ACCESS"] ?? false),
				"FOR_ALL_ACCESS_ONLY" => (bool) ($arParams["FOR_ALL_ACCESS_ONLY"] ?? false),
				'WORKGROUP_MODE' => (bool) ($arParams['WORKGROUP_MODE'] ?? false),
				"TAG_SET" => (
					!empty($arParams["TAG_SET"])
						? $arParams["TAG_SET"]
						: false
				),
				"MULTIPLE" => (
					!empty($arParams["MULTIPLE"])
					&& $arParams["MULTIPLE"] === "Y"
						? "Y"
						: "N"
				),
				"SET_TIMESTAMP" => (
					!empty($arParams["SET_TIMESTAMP"])
					&& $arParams["SET_TIMESTAMP"] === "Y"
						? "Y"
						: "N"
				),
				"SEND_TO_AUTHOR" => (
					!isset($arParams["SEND_TO_AUTHOR"])
					|| $arParams["SEND_TO_AUTHOR"] !== "Y"
						? "N"
						: "Y"
				),
				"USER_ID" => (
					isset($arParams["USER_ID"])
					&& is_array($arParams["USER_ID"])
						? $arParams["USER_ID"]
						: []
				),
			]
		);
	}

	public static function GetSubSelect(
		$counterEntityId,
		$counterEntityType = '',
		$logEntityId = false,
		$logEventId = '',
		$createdById = 0,
		$entitiesList = null,
		$adminList = false,
		$transport = false,
		$visible = "Y",
		$type = CSocNetLogCounter::TYPE_LOG_ENTRY,
		$params = array(),
		$decrement = false,
		$forAllAccess = false
	)
	{
		global $DB;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		if (
			is_array($counterEntityId)
			&& isset($counterEntityId["LOG_ID"])
		)
		{
			$arFields = $counterEntityId;

			$counterEntityId = (int)$arFields["LOG_ID"];
			$counterEntityType = (string)($arFields["ENTITY_TYPE"] ?? '');
			$logEventId = (string)($arFields["EVENT_ID"] ?? '');
			$createdById = (int)($arFields["CREATED_BY_ID"] ?? 0);
			$type = ($arFields["TYPE"] ?? CSocNetLogCounter::TYPE_LOG_ENTRY);
			$code = ($arFields["CODE"] ?? false);
			$params = ($arFields["PARAMS"] ?? []);
			$decrement = ($arFields["DECREMENT"] ?? false);
			$multiple = (isset($arFields['MULTIPLE']) && $arFields['MULTIPLE'] === 'Y');
			$setTimestamp = (isset($arFields['SET_TIMESTAMP']) && $arFields['SET_TIMESTAMP'] === 'Y');

			$forAllAccessOnly = false;
			if (isset($arFields["FOR_ALL_ACCESS_ONLY"]))
			{
				$forAllAccessOnly = ($arFields["FOR_ALL_ACCESS_ONLY"] ? "Y" : "N");
			}

			$forAllAccess = (
				$forAllAccessOnly === 'Y'
					? true
					: ($arFields["FOR_ALL_ACCESS"] ?? false)
			);
			$tagSet = ($arFields["TAG_SET"] ?? false);

			$sendToAuthor = (
				isset($arFields['SEND_TO_AUTHOR'])
				&& $arFields['SEND_TO_AUTHOR'] === 'Y'
			);
			$userIdListToIncrement = (
				isset($arFields["USER_ID"])
				&& is_array($arFields["USER_ID"])
					? $arFields["USER_ID"]
					: []
			);
			$workgroupMode = (bool)($arFields["WORKGROUP_MODE"] ?? false);
		}
		else
		{
			$sendToAuthor = false;
			$forAllAccessOnly = false;
			$multiple = false;
			$tagSet = false;
			$code = false;
			$setTimestamp = false;
			$userIdListToIncrement = [];
			$workgroupMode = false;
		}

		$intranetInstalled = static::$moduleManagerClass::isModuleInstalled('intranet');
		if ((int)$counterEntityId <= 0)
		{
			return '';
		}

		$arSocNetAllowedSubscribeEntityTypesDesc = static::$allowedClass::GetAllowedEntityTypesDesc();

		if (!$code)
		{
			$code = CUserCounter::LIVEFEED_CODE . ($multiple ? $type.$counterEntityId : "");

			if ($workgroupMode)
			{
				$code .= 'SG';
			}

			$code = "'" . $code . "'";
		}

		$params = (
			is_array($params)
				? $params
				: []
		);

		$params['CODE'] = (
			!empty($params['CODE'])
				? $params['CODE']
				: $code
		);

		if (
			$type === CSocNetLogCounter::TYPE_LOG_ENTRY
			&& ($arLog = static::$logClass::GetByID($counterEntityId))
		)
		{
			$logId = $counterEntityId;
			$counterEntityType = (string)$arLog["ENTITY_TYPE"];
			$logEventId = (string)$arLog["EVENT_ID"];
			$createdById = (int)$arLog["USER_ID"];
		}
		elseif (
			$type === CSocNetLogCounter::TYPE_LOG_COMMENT
			&& ($arLogComment = static::$logCommentClass::GetByID($counterEntityId))
		)
		{
			$counterEntityType = (string)$arLogComment["ENTITY_TYPE"];
			$logEventId = (string)$arLogComment["EVENT_ID"];
			$createdById = (int)$arLogComment["USER_ID"];
			$logId = $arLogComment["LOG_ID"]; // recalculate log_id
		}
		else
		{
			$logId = $counterEntityId;
		}

		if (
			$logEventId === ''
			|| !in_array($counterEntityType, static::$allowedClass::GetAllowedEntityTypes(), true)
		)
		{
			return '';
		}

		$logRightCodes = [];
		$res = static::$logRightTableClass::getList([
			'filter' => [
				'=LOG_ID' => $logId,
			],
			'select' => [ 'GROUP_CODE' ],
		]);
		while ($logRightFields = $res->fetch())
		{
			$logRightCodes[] = $logRightFields['GROUP_CODE'];
		}

		$useUASubSelect = false;

		$useFollow = (
			!$workgroupMode
			&& $type === CSocNetLogCounter::TYPE_LOG_COMMENT
			&& (
				!defined('DisableSonetLogFollow')
				|| DisableSonetLogFollow !== true
			)
		);

		$defaultFollowValue = static::$optionClass::get('socialnetwork', 'follow_default_type', 'Y');

		$followJoin = '';
		$followWhere = '';

		if ($useFollow)
		{
			if ($defaultFollowValue === 'Y')
			{
				$followWhere = "
					AND (
						NOT EXISTS (
							SELECT USER_ID 
							FROM b_sonet_log_follow 
							WHERE 
								USER_ID = U.ID 
								AND TYPE = 'N' 
								AND (CODE = 'L" . $logId . "' OR CODE = '**')
						)
						OR EXISTS (
							SELECT USER_ID 
							FROM b_sonet_log_follow 
							WHERE 
								USER_ID = U.ID 
								AND TYPE = 'Y' AND CODE = 'L" . $logId . "'
						)
					)
				";
			}
			else
			{
				$followJoin = '';

				[ $join, $condition ] = static::getFollowJoin($logId, 'U.ID', 'positive');
				$followJoin .= $join . ' AND ' . $condition . ' ';

				[ $join, $condition ] = static::getFollowJoin($logId, 'U.ID', 'negative');
				$followJoin .= $join . ' AND ' . $condition . ' ';

				$wherePositive = static::getFollowWhere('positive');
				$whereNegative = static::getFollowWhere('negative');

				$followWhere = "
					AND (LFW.USER_ID IS NOT NULL AND " . $wherePositive .")
					AND " . $whereNegative . "
				";
			}
		}

		$viewJoin = " 
			LEFT JOIN b_sonet_log_view LFV 
			ON 
				LFV.USER_ID = U.ID 
				AND LFV.EVENT_ID = '" . $DB->ForSql($logEventId) . "'";

		$viewWhere = "AND (LFV.USER_ID IS NULL OR LFV.TYPE = 'Y')";

		$logRightFilterValue = [];

		if (!empty($userIdListToIncrement))
		{
			$userWhere = " AND U.ID IN (".implode(",", $userIdListToIncrement).")";
		}
		elseif ($workgroupMode)
		{
			$workgroupIdList = [];
			foreach ($logRightCodes as $rightCode)
			{
				if (
					!preg_match('/^SG(\d+)/i', $rightCode, $matches)
					|| (int)$matches[1] <= 0
				)
				{
					continue;
				}
				$workgroupIdList[] = (int)$matches[1];
			}

			$workgroupIdList = array_unique($workgroupIdList);
			if (empty($workgroupIdList))
			{
				return '';
			}

			$userWhere = "
				AND SU2G.GROUP_ID IN (" . static::implodeArrayOfIntegers($workgroupIdList) . ")
				AND SU2G.ROLE IN (" . static::implodeArrayOfStrings(UserToGroupTable::getRolesMember()) . ")
			";
		}
		elseif (!$intranetInstalled)
		{
			if (static::$optionClass::get('socialnetwork', 'sonet_log_smart_filter', 'N') === 'Y')
			{
				$userWhere = "
					AND (
						0=1
						OR (
							(
								SLSF.USER_ID IS NULL
								OR SLSF.TYPE = 'Y'
							)
							" . (!$forAllAccess ? ' AND (UA.ACCESS_CODE = SLR.GROUP_CODE)' : '') . "
							AND (
								SLR.GROUP_CODE LIKE 'SG%'
								OR SLR.GROUP_CODE = " . $DB->Concat("'U'", 'U.ID') . "
							)
						)
						OR (
							SLSF.TYPE <> 'Y'
							AND (
								SLR.GROUP_CODE IN ('AU', 'G2')
								" . (!$forAllAccess ? ' OR (UA.ACCESS_CODE = SLR.GROUP_CODE)' : '') . "
							)
						)
					)
				";
			}
			else
			{
				$userWhere = "
					AND (
						0=1
						OR (
							(
								SLSF.USER_ID IS NULL
								OR SLSF.TYPE <> 'Y'
							)
							AND (
								SLR.GROUP_CODE IN ('AU', 'G2')
								" . ($forAllAccess ? '' : ' OR (UA.ACCESS_CODE = SLR.GROUP_CODE)') . "
							)
						)
						OR (
							SLSF.TYPE = 'Y'
							" . ($forAllAccess ? '' : ' AND (UA.ACCESS_CODE = SLR.GROUP_CODE)') . "
							AND (
								SLR.GROUP_CODE LIKE 'SG%'
								OR SLR.GROUP_CODE = " . $DB->Concat("'U'", 'U.ID') . "
							)
						)
					)
				";
			}
		}
		else
		{
			$userLogRightsIntersectCondition = '';
			if (!$forAllAccess && $forAllAccessOnly !== 'Y')
			{
				foreach ($logRightCodes as $rightCode)
				{
					if (in_array($rightCode, [ 'AU', 'G2' ], true))
					{
						continue;
					}

					$logRightFilterValue[] = $rightCode;
				}

				$userLogRightsIntersectCondition = (
					!empty($logRightFilterValue)
						? ' OR UA.ACCESS_CODE IN (' . static::implodeArrayOfStrings($logRightFilterValue) . ') '
						: ' OR UA.ACCESS_CODE = SLR.GROUP_CODE '
					);
			}

			if (
				$useFollow
				&& $defaultFollowValue !== 'Y'
				&& !$forAllAccess
				&& $forAllAccessOnly !== 'Y'
				&& !empty($logRightFilterValue)
			)
			{
				$useUASubSelect = true;

				[ $join, $condition ] = static::getFollowJoin($logId, 'UA.USER_ID', 'positive');
				$where = static::getFollowWhere('positive');

				$userWhere = "
					AND U.ID IN (
						SELECT DISTINCT UA.USER_ID
						FROM b_user_access UA
						" . $join . "
						WHERE
							UA.ACCESS_CODE IN (" . static::implodeArrayOfStrings($logRightFilterValue) . ")
							AND " . $where . "
							AND " . $condition . "
					)
				";

				[ $join, $condition ] = static::getFollowJoin($logId, 'U.ID', 'negative');
				$where = static::getFollowWhere('negative');

				$followJoin = $join . ' AND ' . $condition . ' ';
				$followWhere = " AND " . $where . " ";
			}
			else
			{
				$userWhere = "
					AND (
						0=1
						" . (
							$forAllAccessOnly !== 'N' || $forAllAccess
								? "OR (SLR.GROUP_CODE IN ('AU', 'G2'))"
								: ''
						) . "
						" . $userLogRightsIntersectCondition . "
					)
				";
			}
		}

		$userWhere = (
			(
				$type === CSocNetLogCounter::TYPE_LOG_COMMENT
				|| (
					array_key_exists("USE_CB_FILTER", $arSocNetAllowedSubscribeEntityTypesDesc[$counterEntityType])
					&& $arSocNetAllowedSubscribeEntityTypesDesc[$counterEntityType]["USE_CB_FILTER"] === "Y"
				)
			)
			&& $createdById > 0
			&& !$sendToAuthor
				? " AND U.ID <> " . $createdById
				: ""
			) .
			$userWhere;

		$strSQL = '';

		if ($workgroupMode)
		{
			$strSQL = "
				SELECT DISTINCT
					U.ID as ID
					," . ($decrement ? "-1" : "1") . " as CNT
					, '**' as SITE_ID
					," . $DB->Concat($params['CODE'], 'SU2G.GROUP_ID') . " as CODE,
					0 as SENT
					" . ($tagSet ? ", '" . $DB->ForSQL($tagSet) . "' as TAG" : "") . "
					" . ($setTimestamp ? ', ' . CDatabase::currentTimeFunction() . ' as TIMESTAMP_X' : '') . "
				FROM
					b_user U
				INNER JOIN b_sonet_user2group SU2G ON SU2G.USER_ID = U.ID" .
				$viewJoin . "
				WHERE
					U.ACTIVE = 'Y'
					AND U.LAST_ACTIVITY_DATE IS NOT NULL
					AND U.LAST_ACTIVITY_DATE > " . $helper->addDaysToDateTime(-14) . "
					AND CASE WHEN U.EXTERNAL_AUTH_ID IN ('".implode("','", static::$userTableClass::getExternalUserTypes())."') THEN 'N' ELSE 'Y' END = 'Y'
					" .
					$userWhere . " " .
					$viewWhere . "
			";
		}
		else
		{
			$strSQL = "
				SELECT DISTINCT
					U.ID as ID
					," . ($decrement ? "-1" : "1")." as CNT
					, " .$DB->IsNull("SLS.SITE_ID", "'**'")." as SITE_ID
					," . $params['CODE']." as CODE,
					0 as SENT
					" . ($tagSet ? ", '".$DB->ForSQL($tagSet)."' as TAG" : "") . "
					" . ($setTimestamp ? ', ' . CDatabase::currentTimeFunction() . ' as TIMESTAMP_X' : '') . "
				FROM
					b_user U
				INNER JOIN b_sonet_log_right SLR ON SLR.LOG_ID = ".$logId."
					" . (
						!$forAllAccess && !$useUASubSelect
							? 'INNER JOIN b_user_access UA 
								ON UA.USER_ID = U.ID' .
								(!empty($logRightFilterValue) ? ' AND (UA.ACCESS_CODE = SLR.GROUP_CODE)' : '')
							: ''
					)."
					LEFT JOIN b_sonet_log_site SLS ON SLS.LOG_ID = SLR.LOG_ID
					" . ($followJoin !== '' ? $followJoin : "") . "
					" . $viewJoin . "
					".(!$intranetInstalled ? "LEFT JOIN b_sonet_log_smartfilter SLSF ON SLSF.USER_ID = U.ID " : "")."

				WHERE
					U.ACTIVE = 'Y'
					AND U.LAST_ACTIVITY_DATE IS NOT NULL
					AND U.LAST_ACTIVITY_DATE > " . $helper->addDaysToDateTime(-14) . "
					AND CASE WHEN U.EXTERNAL_AUTH_ID IN ('".implode("','", static::$userTableClass::getExternalUserTypes())."') THEN 'N' ELSE 'Y' END = 'Y'
					" .
					$userWhere."
					".
					$followWhere .
					$viewWhere . "
			";
		}

		return $strSQL;
	}

	private static function isComment(string $type): bool
	{
		return $type === CSocNetLogCounter::TYPE_LOG_COMMENT;
	}

	protected static function getFollowJoin(int $logId = 0, string $userIdReference = 'U.ID', string $mode = ''): array
	{
		if (!in_array($mode, [ 'positive', 'negative' ], true))
		{
			return [ '', '' ];
		}

		if ($mode === 'positive')
		{
			$join = " 
				INNER JOIN b_sonet_log_follow LFW 
				ON LFW.USER_ID = " . $userIdReference . " 
			";

			$condition = " (LFW.CODE = 'L" . $logId . "' OR LFW.CODE = '**') ";
		}
		else
		{
			$join = " 
				LEFT JOIN b_sonet_log_follow LFW2 
				ON LFW2.USER_ID = " . $userIdReference . " 
			";

			$condition = " (LFW2.CODE = 'L" . $logId . "' AND LFW2.TYPE = 'N') ";
		}

		return [ $join, $condition ];
	}

	protected static function getFollowWhere(string $mode = ''): string
	{
		if (!in_array($mode, [ 'positive', 'negative' ], true))
		{
			return '';
		}

		if ($mode === 'positive')
		{
			$result = " LFW.TYPE = 'Y' ";
		}
		else
		{
			$result = " LFW2.USER_ID IS NULL ";
		}

		return $result;
	}

	protected static function implodeArrayOfStrings(array $list = []): string
	{
		global $DB;

		return implode(', ', array_map(static function($item) use ($DB) {
			return "'" . $DB->forSql($item) . "'";
		}, $list));
	}

	protected static function implodeArrayOfIntegers(array $list = []): string
	{
		return implode(', ', array_map(static function($item) {
			return (int)$item;
		}, $list));
	}

	/** @deprecated */
	public static function GetValueByUserID($user_id, $site_id = SITE_ID)
	{
		global $DB;
		$user_id = (int)$user_id;

		if ($user_id <= 0)
			return false;

		$strSQL = "
			SELECT SUM(CNT) CNT
			FROM b_sonet_log_counter
			WHERE USER_ID = ".$user_id."
			AND (SITE_ID = '".$site_id."' OR SITE_ID = '**')
			AND CODE = '**'
		";

		$dbRes = $DB->Query($strSQL);
		if ($arRes = $dbRes->Fetch())
		{
			return $arRes["CNT"];
		}

		return 0;
	}

	/** @deprecated */
	public static function GetCodeValuesByUserID($user_id, $site_id = SITE_ID)
	{
		global $DB;
		$result = array();
		$user_id = (int)$user_id;

		if($user_id > 0)
		{
			$strSQL = "
				SELECT CODE, SUM(CNT) CNT
				FROM b_sonet_log_counter
				WHERE USER_ID = ".$user_id."
				AND (SITE_ID = '".$site_id."' OR SITE_ID = '**')
				GROUP BY CODE
			";

			$dbRes = $DB->Query($strSQL);
			while ($arRes = $dbRes->Fetch())
				$result[$arRes["CODE"]] = $arRes["CNT"];
		}

		return $result;
	}

	/** @deprecated */
	public static function GetLastDateByUserAndCode($user_id, $site_id = SITE_ID, $code = "**")
	{
		global $DB;
		$result = 0;
		$user_id = (int)$user_id;

		if($user_id > 0)
		{
			$strSQL = "
				SELECT ".$DB->DateToCharFunction("LAST_DATE", "FULL")." LAST_DATE
				FROM b_sonet_log_counter
				WHERE USER_ID = ".$user_id."
				AND (SITE_ID = '".$DB->ForSql($site_id)."' OR SITE_ID = '**')
				AND CODE = '".$DB->ForSql($code)."'
			";

			$dbRes = $DB->Query($strSQL);
			if ($arRes = $dbRes->Fetch())
			{
				$result = MakeTimeStamp($arRes["LAST_DATE"]);
			}
		}

		return $result;
	}

	/** @deprecated */
	public static function GetList($arFilter = Array(), $arSelectFields = [])
	{
		global $DB;

		if (count($arSelectFields) <= 0)
		{
			$arSelectFields = array("LAST_DATE", "PAGE_SIZE", "PAGE_LAST_DATE_1");
		}

		// FIELDS -->
		$arFields = array(
			"USER_ID" => Array("FIELD" => "SLC.USER_ID", "TYPE" => "int"),
			"SITE_ID" => Array("FIELD" => "SLC.SITE_ID", "TYPE" => "string"),
			"CODE" => Array("FIELD" => "SLC.CODE", "TYPE" => "string"),
			"LAST_DATE" => Array("FIELD" => "SLC.LAST_DATE", "TYPE" => "datetime"),
			"PAGE_SIZE" => array("FIELD" => "SLC.PAGE_SIZE", "TYPE" => "int"),
			"PAGE_LAST_DATE_1" => Array("FIELD" => "SLC.PAGE_LAST_DATE_1", "TYPE" => "datetime"),
		);
		// <-- FIELDS

		$arSqls = CSocNetGroup::PrepareSql($arFields, array(), $arFilter, false, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_sonet_log_counter SLC ".
			"	".$arSqls["FROM"]." ";

		if ($arSqls["WHERE"] <> '')
		{
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		}

		return $DB->Query($strSql);
	}
}
