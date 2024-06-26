<?php

IncludeModuleLangFile(__FILE__);

class CAllRatings
{
	const REACTION_DEFAULT = 'like';
	const REACTION_KISS = 'kiss';
	const REACTION_LAUGH = 'laugh';
	const REACTION_ANGRY = 'angry';
	const REACTION_WONDER = 'wonder';
	const REACTION_CRY = 'cry';

	// get specified rating record
	public static function GetByID($ID)
	{
		global $DB;

		$ID = (int)$ID;

		if($ID<=0)
			return false;

		return $DB->Query("
			SELECT
				R.*,
				".$DB->DateToCharFunction("R.CREATED")." as CREATED,
				".$DB->DateToCharFunction("R.LAST_MODIFIED")." as LAST_MODIFIED,
				".$DB->DateToCharFunction("R.LAST_CALCULATED")." as	LAST_CALCULATED
			FROM
				b_rating R
			WHERE
				ID=".$ID
		);
	}

	public static function GetArrayByID($ID)
	{
		global $DB;

		$ID = (int)$ID;
		$strID = "b".$ID;
		if(CACHED_b_rating===false)
		{
			$res = $DB->Query("
				SELECT
					R.*,
					".$DB->DateToCharFunction("R.CREATED")." as CREATED,
					".$DB->DateToCharFunction("R.LAST_MODIFIED")." as LAST_MODIFIED,
					".$DB->DateToCharFunction("R.LAST_CALCULATED")." as	LAST_CALCULATED
				FROM
					b_rating R
				WHERE
					ID=".$ID
			);
			$arResult = $res->Fetch();
		}
		else
		{
			global $stackCacheManager;
			$stackCacheManager->SetLength("b_rating", 100);
			$stackCacheManager->SetTTL("b_rating", CACHED_b_rating);
			if($stackCacheManager->Exist("b_rating", $strID))
				$arResult = $stackCacheManager->Get("b_rating", $strID);
			else
			{
				$res = $DB->Query("
					SELECT
						R.*,
						".$DB->DateToCharFunction("R.CREATED")." as CREATED,
						".$DB->DateToCharFunction("R.LAST_MODIFIED")." as LAST_MODIFIED,
						".$DB->DateToCharFunction("R.LAST_CALCULATED")." as	LAST_CALCULATED
					FROM
						b_rating R
					WHERE
						ID=".$ID
				);
				$arResult = $res->Fetch();
				if($arResult)
					$stackCacheManager->Set("b_rating", $strID, $arResult);
			}
		}

		return $arResult;
	}

	// get rating record list
	public static function GetList($arSort=array(), $arFilter=Array())
	{
		global $DB;

		$arSqlSearch = Array();

		if (is_array($arFilter))
		{
			foreach ($arFilter as $key => $val)
			{
				if ((string)$val == '' || $val === "NOT_REF")
					continue;
				switch(mb_strtoupper($key))
				{
					case "ID":
						$arSqlSearch[] = GetFilterQuery("R.ID", $val, "N");
						break;
					case "ACTIVE":
						if(in_array($val, Array('Y', 'N')))
						{
							$arSqlSearch[] = "R.ACTIVE = '".$val."'";
						}
						break;
					case "AUTHORITY":
						if(in_array($val, Array('Y', 'N')))
						{
							$arSqlSearch[] = "R.AUTHORITY = '".$val."'";
						}
						break;
					case "POSITION":
						if(in_array($val, Array('Y', 'N')))
						{
							$arSqlSearch[] = "R.POSITION = '".$val."'";
						}
						break;
					case "CALCULATED":
						if(in_array($val, Array('Y', 'N', 'C')))
						{
							$arSqlSearch[] = "R.CALCULATED = '".$val."'";
						}
						break;
					case "NAME":
						$arSqlSearch[] = GetFilterQuery("R.NAME", $val);
						break;
					case "ENTITY_ID":
						$arSqlSearch[] = GetFilterQuery("R.ENTITY_ID", $val);
						break;
				}
			}
		}

		$sOrder = "";
		foreach($arSort as $key=>$val)
		{
			$ord = (mb_strtoupper($val) <> "ASC"? "DESC":"ASC");
			switch(mb_strtoupper($key))
			{
				case "ID":
					$sOrder .= ", R.ID ".$ord;
					break;
				case "NAME":
					$sOrder .= ", R.NAME ".$ord;
					break;
				case "CREATED":
					$sOrder .= ", R.CREATED ".$ord;
					break;
				case "LAST_MODIFIED":
					$sOrder .= ", R.LAST_MODIFIED ".$ord;
					break;
				case "LAST_CALCULATED":
					$sOrder .= ", R.LAST_CALCULATED ".$ord;
					break;
				case "ACTIVE":
					$sOrder .= ", R.ACTIVE ".$ord;
					break;
				case "AUTHORITY":
					$sOrder .= ", R.AUTHORITY ".$ord;
					break;
				case "POSITION":
					$sOrder .= ", R.POSITION ".$ord;
					break;
				case "STATUS":
					$sOrder .= ", R.CALCULATED ".$ord;
					break;
				case "CALCULATED":
					$sOrder .= ", R.CALCULATED ".$ord;
					break;
				case "CALCULATION_METHOD":
					$sOrder .= ", R.CALCULATION_METHOD ".$ord;
					break;
				case "ENTITY_ID":
					$sOrder .= ", R.ENTITY_ID ".$ord;
					break;
			}
		}

		if ($sOrder == '')
			$sOrder = "R.ID DESC";

		$strSqlOrder = " ORDER BY ".TrimEx($sOrder,",");

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				R.ID, R.NAME, R.ACTIVE, R.CALCULATED, R.AUTHORITY, R.POSITION, R.ENTITY_ID, R.CALCULATION_METHOD,
				".$DB->DateToCharFunction("R.CREATED")." CREATED,
				".$DB->DateToCharFunction("R.LAST_MODIFIED")." LAST_MODIFIED,
				".$DB->DateToCharFunction("R.LAST_CALCULATED")." LAST_CALCULATED
			FROM
				b_rating R
			WHERE
			".$strSqlSearch."
			".$strSqlOrder;
		return $DB->Query($strSql);
	}

	public static function GetRatingValueInfo($ratingId)
	{
		global $DB;

		$ratingId = (int)$ratingId;

		$strSql = "
			SELECT
				MAX(CURRENT_VALUE) as MAX,
				MIN(CURRENT_VALUE) as MIN,
				AVG(CURRENT_VALUE) as AVG,
				COUNT(*) as CNT
			FROM b_rating_results
			WHERE RATING_ID = ".$ratingId;
		return $DB->Query($strSql);
	}

	//Addition rating
	public static function Add($arFields)
	{
		global $DB, $stackCacheManager;

		// check only general field
		if(!CRatings::__CheckFields($arFields))
			return false;

		$arFields_i = Array(
			"ACTIVE"				=> $arFields["ACTIVE"] === 'Y' ? 'Y' : 'N',
			"POSITION"				=> $arFields["POSITION"] === 'Y' ? 'Y' : 'N',
			"AUTHORITY"				=> $arFields["AUTHORITY"] === 'Y' ? 'Y' : 'N',
			"NAME"					=> $arFields["NAME"],
			"ENTITY_ID"		 		=> $arFields["ENTITY_ID"],
			"CALCULATION_METHOD"	=> $arFields["CALCULATION_METHOD"],
			"~CREATED"				=> $DB->GetNowFunction(),
			"~LAST_MODIFIED"		=> $DB->GetNowFunction(),
		);
		$ID = $DB->Add("b_rating", $arFields_i);

		// queries modules and give them to inspect the field settings
		foreach(GetModuleEvents("main", "OnAfterAddRating", true) as $arEvent)
			$arFields = ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		CRatings::__AddComponents($ID, $arFields);

		$arFields_u = Array(
			"CONFIGS" => "'".$DB->ForSQL(serialize($arFields["CONFIGS"]))."'",
		);

		$DB->Update("b_rating", $arFields_u, "WHERE ID = ".$ID);

		if ($arFields['AUTHORITY'] === 'Y')
			CRatings::SetAuthorityRating($ID);

		CAgent::AddAgent("CRatings::Calculate($ID);", "main", "N", 3600, "", "Y", "");

		$stackCacheManager->Clear("b_rating");

		return $ID;
	}

	//Update rating
	public static function Update($ID, $arFields)
	{
		global $DB, $stackCacheManager;

		$ID = (int)$ID;

		// check only general field
		if(!CRatings::__CheckFields($arFields))
			return false;

		$arFields_u = Array(
			"ACTIVE"				=> $arFields['ACTIVE'] === 'Y' ? 'Y' : 'N',
			"NAME"					=> $arFields["NAME"],
			"ENTITY_ID"		 		=> $arFields["ENTITY_ID"],
			"CALCULATION_METHOD"	=> $arFields["CALCULATION_METHOD"],
			"~LAST_MODIFIED"		=> $DB->GetNowFunction(),
		);
		$strUpdate = $DB->PrepareUpdate("b_rating", $arFields_u);
		if(!$DB->Query("UPDATE b_rating SET ".$strUpdate." WHERE ID=".$ID))
			return false;

		if (!isset($arFields["CONFIGS"]))
		{
			$stackCacheManager->Clear("b_rating");
			return true;
		}
		// queries modules and give them to inspect the field settings
		foreach(GetModuleEvents("main", "OnAfterUpdateRating", true) as $arEvent)
			$arFields = ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		CRatings::__UpdateComponents($ID, $arFields);

		$arFields_u = Array(
			"POSITION" => "'".($arFields['POSITION'] === 'Y' ? 'Y' : 'N')."'",
			"AUTHORITY" => "'".($arFields['AUTHORITY'] === 'Y' ? 'Y' : 'N')."'",
			"CONFIGS"  => "'".$DB->ForSQL(serialize($arFields["CONFIGS"]))."'",
		);
		$DB->Update("b_rating", $arFields_u, "WHERE ID = ".$ID);

		if ($arFields['AUTHORITY'] === 'Y')
			CRatings::SetAuthorityRating($ID);

		if ($arFields['NEW_CALC'] === 'Y')
			$DB->Query("UPDATE b_rating_results SET PREVIOUS_VALUE = 0 WHERE RATING_ID=".$ID." and ENTITY_TYPE_ID='".$DB->ForSql($arFields["ENTITY_ID"])."'");

		$strSql = "SELECT COMPLEX_NAME FROM b_rating_component WHERE RATING_ID = $ID and ACTIVE = 'N'";
		$res = $DB->Query($strSql);
		$arrRatingComponentId = array();
		while($arRes = $res->Fetch())
			$arrRatingComponentId[] = $arRes['COMPLEX_NAME'];

		if (!empty($arrRatingComponentId))
			$DB->Query("DELETE FROM b_rating_component_results WHERE RATING_ID = $ID AND COMPLEX_NAME IN ('".implode("','", $arrRatingComponentId)."')");

		CRatings::Calculate($ID, true);

		CAgent::RemoveAgent("CRatings::Calculate($ID);", "main");
		$AID = CAgent::AddAgent("CRatings::Calculate($ID);", "main", "N", 3600, "", "Y", "");

		$stackCacheManager->Clear("b_rating");

		return true;
	}

	// delete rating
	public static function Delete($ID)
	{
		global $DB, $stackCacheManager;

		$ID = (int)$ID;

		foreach(GetModuleEvents("main", "OnBeforeDeleteRating", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID));

		$DB->Query("DELETE FROM b_rating WHERE ID=$ID");
		$DB->Query("DELETE FROM b_rating_user WHERE RATING_ID=$ID");
		$DB->Query("DELETE FROM b_rating_component WHERE RATING_ID=$ID");
		$DB->Query("DELETE FROM b_rating_component_results WHERE RATING_ID=$ID");
		$DB->Query("DELETE FROM b_rating_results WHERE RATING_ID=$ID");

		CAgent::RemoveAgent("CRatings::Calculate($ID);", "main");

		$stackCacheManager->Clear("b_rating");

		return true;
	}

	// start calculation rating-component
	public static function Calculate($ID, $bForceRecalc = false)
	{
		global $DB;

		$ID = (int)$ID;

		$strSql = "SELECT
				RC.*,
				".$DB->DateToCharFunction("RC.LAST_MODIFIED")."	LAST_MODIFIED,
				".$DB->DateToCharFunction("RC.LAST_CALCULATED")." LAST_CALCULATED,
				".$DB->DateToCharFunction("RC.NEXT_CALCULATION")." NEXT_CALCULATION
			FROM
				b_rating_component RC
			WHERE
				RATING_ID = $ID
				and ACTIVE = 'Y' ".($bForceRecalc ? '' : 'AND NEXT_CALCULATION <= '.$DB->GetNowFunction());
		$res = $DB->Query($strSql);
		while($arRes = $res->Fetch())
		{
			if(CModule::IncludeModule(mb_strtolower($arRes['MODULE_ID'])))
			{
				$arRes['CONFIG'] = unserialize($arRes['CONFIG'], ['allowed_classes' => false]);
				// If the type is automatic calculation of parameters * global vote weight
				$sRatingWeightType = COption::GetOptionString("main", "rating_weight_type", "auto");
				if ($sRatingWeightType === 'auto')
				{
					$voteWeight = COption::GetOptionString("main", "rating_vote_weight", 1);
					$arRes['CONFIG']['COEFFICIENT'] = ($arRes['CONFIG']['COEFFICIENT'] ?? 1) * $voteWeight;
				}
				if ($arRes['EXCEPTION_METHOD'] <> '')
				{
					if (method_exists($arRes['CLASS'], $arRes['EXCEPTION_METHOD']))
					{
						$exceptionText = call_user_func(array($arRes['CLASS'], $arRes['EXCEPTION_METHOD']));
						if ($exceptionText === false)
						{
							if (method_exists($arRes['CLASS'],  $arRes['CALC_METHOD']))
							{
								$result = call_user_func(array($arRes['CLASS'], $arRes['CALC_METHOD']), $arRes);
							}
						}
					}
				}
				else
				{
					if (method_exists($arRes['CLASS'],  $arRes['CALC_METHOD']))
					{
						$result = call_user_func(array($arRes['CLASS'], $arRes['CALC_METHOD']), $arRes);
					}
				}
			}
		}

		CRatings::BuildRating($ID);

		return "CRatings::Calculate($ID);";
	}

	// queries modules and get all the available objects
	public static function GetRatingObjects()
	{
		$arObjects = array();

		foreach(GetModuleEvents("main", "OnGetRatingsObjects", true) as $arEvent)
		{
			$arConfig = ExecuteModuleEventEx($arEvent);
			foreach ($arConfig as $OBJ_TYPE)
				if (!in_array($OBJ_TYPE, $arObjects))
					$arObjects[] = $OBJ_TYPE;
		}
		return $arObjects;
	}

	// queries modules and get all the available entity types
	public static function GetRatingEntityTypes($objectType = null)
	{
		$arEntityTypes = array();

		foreach(GetModuleEvents("main", "OnGetRatingsConfigs", true) as $arEvent)
		{
			$arConfig = ExecuteModuleEventEx($arEvent);
			if (is_null($objectType))
			{
				foreach ($arConfig as $OBJ_TYPE => $OBJ_VALUE)
					foreach ($OBJ_VALUE['VOTE'] as $VOTE_VALUE)
					{
						$EntityTypeId = $VOTE_VALUE['MODULE_ID'].'_'.$VOTE_VALUE['ID'];
						if (!in_array($arEntityTypes[$OBJ_TYPE], $EntityTypeId))
							$arEntityTypes[$OBJ_TYPE][] = $EntityTypeId;
					}
			}
			else
			{
				foreach ($arConfig[$objectType]['VOTE'] as $VOTE_VALUE)
				{
					$EntityTypeId = $VOTE_VALUE['MODULE_ID'].'_'.$VOTE_VALUE['ID'];
					$arEntityTypes[$EntityTypeId] = $EntityTypeId;
				}
			}
		}

		return $arEntityTypes;
	}

	// queries modules and assemble an array of settings
	public static function GetRatingConfigs($objectType = null, $withRatingType = true)
	{
		$arConfigs = array();

		foreach(GetModuleEvents("main", "OnGetRatingsConfigs", true) as $arEvent)
		{
			$arConfig = ExecuteModuleEventEx($arEvent);
			if (is_null($objectType))
			{
				foreach ($arConfig["COMPONENT"] as $OBJ_TYPE => $TYPE_VALUE)
				{
					foreach ($TYPE_VALUE as $RAT_TYPE => $RAT_VALUE)
					{
						foreach ($RAT_VALUE as $VALUE)
						{
							if ($withRatingType)
								$arConfigs[$OBJ_TYPE][$arConfig['MODULE_ID']][$RAT_TYPE][$arConfig['MODULE_ID']."_".$RAT_TYPE."_".$VALUE['ID']] = $VALUE;
							else
								$arConfigs[$OBJ_TYPE][$arConfig['MODULE_ID']][$arConfig['MODULE_ID']."_".$RAT_TYPE."_".$VALUE['ID']] = $VALUE;
						}
					}
				}
			}
			else
			{
				foreach ($arConfig["COMPONENT"][$objectType] as $RAT_TYPE => $RAT_VALUE)
				{
					$arConfigs[$arConfig['MODULE_ID']]['MODULE_ID'] = $arConfig['MODULE_ID'];
					$arConfigs[$arConfig['MODULE_ID']]['MODULE_NAME'] = $arConfig['MODULE_NAME'];
					foreach ($RAT_VALUE as $VALUE)
						if ($withRatingType)
							$arConfigs[$arConfig['MODULE_ID']][$RAT_TYPE][$arConfig['MODULE_ID']."_".$RAT_TYPE."_".$VALUE['ID']] = $VALUE;
						else
							$arConfigs[$arConfig['MODULE_ID']][$arConfig['MODULE_ID']."_".$RAT_TYPE."_".$VALUE['ID']] = $VALUE;
				}
			}
		}

		return $arConfigs;
	}

	public static function GetRatingVoteResult($entityTypeId, $entityId, $user_id = 0)
	{
		global $USER;

		$arResult = array();
		$user_id = (int)$user_id;

		if ($user_id == 0)
			$user_id = $USER->GetID();

		if (is_array($entityId))
		{
			foreach ($entityId as $currentEntityId)
			{
				$arResult[$currentEntityId] = self::GetRatingVoteResultCache($entityTypeId, $currentEntityId, $user_id);
			}
		}
		else
		{
			$arResult = self::GetRatingVoteResultCache($entityTypeId, $entityId, $user_id);
		}

		return $arResult;
	}

	public static function GetRatingVoteResultCache($entityTypeId, $entityId, $user_id = 0)
	{
		global $DB, $CACHE_MANAGER;

		$arResult = array();
		$entityId = (int)$entityId;
		$user_id = (int)$user_id;

		if ($entityTypeId == '' || $entityId <= 0)
			return $arResult;

		if ($user_id == 0)
			$user_id = $GLOBALS["USER"]->GetID();

		$bucket_size = (int)CACHED_b_rating_bucket_size;
		if($bucket_size <= 0)
			$bucket_size = 100;

		$bucket = (int)($entityId / $bucket_size);
		if($CACHE_MANAGER->Read(CACHED_b_rating_vote, $cache_id="b_rvg_".$entityTypeId.$bucket, "b_rating_voting"))
		{
			$arResult = $CACHE_MANAGER->Get($cache_id);
		}
		else
		{
			$total = array();

			$sql_str = "SELECT
							RVG.ID,
							RVG.ENTITY_ID,
							RVG.TOTAL_VALUE,
							RVG.TOTAL_VOTES,
							RVG.TOTAL_POSITIVE_VOTES,
							RVG.TOTAL_NEGATIVE_VOTES
						FROM
							b_rating_voting RVG
						WHERE
							RVG.ENTITY_TYPE_ID = '".$DB->ForSql($entityTypeId)."'
						and RVG.ENTITY_ID between ".($bucket*$bucket_size)." AND ".(($bucket+1)*$bucket_size-1)."
						and RVG.ACTIVE = 'Y'";
			$res = $DB->Query($sql_str);
			while($row = $res->Fetch())
			{
				$arResult[$row['ENTITY_ID']] = array(
					'USER_VOTE' => 0,
					'USER_REACTION' => false,
					'USER_HAS_VOTED' => 'N',
					'USER_VOTE_LIST' => array(),
					'USER_REACTION_LIST' => array(),
					'TOTAL_VALUE' => $row['TOTAL_VALUE'],
					'TOTAL_VOTES' => (int)$row['TOTAL_VOTES'],
					'TOTAL_POSITIVE_VOTES' => (int)$row['TOTAL_POSITIVE_VOTES'],
					'TOTAL_NEGATIVE_VOTES' => (int)$row['TOTAL_NEGATIVE_VOTES'],
					'REACTIONS_LIST' => array(
						self::REACTION_DEFAULT => (int)$row['TOTAL_POSITIVE_VOTES']
					)
				);

				if (!isset($total[$row['ENTITY_ID']]))
				{
					$total[$row['ENTITY_ID']] = (int)$row['TOTAL_POSITIVE_VOTES'];
				}
			}

			$count = array();
			$foundDefault = array();
			$entityIdList = array();

			$sql_str = "SELECT
							RVGR.ENTITY_ID,
							RVGR.REACTION,
							RVGR.TOTAL_VOTES
						FROM
							b_rating_voting_reaction RVGR
						WHERE
							RVGR.ENTITY_TYPE_ID = '".$DB->ForSql($entityTypeId)."'
						and RVGR.ENTITY_ID between ".($bucket*$bucket_size)." AND ".(($bucket+1)*$bucket_size-1);
			$res = $DB->Query($sql_str);
			while($row = $res->Fetch())
			{
				if (!in_array($row['ENTITY_ID'], $entityIdList))
				{
					$entityIdList[] = $row['ENTITY_ID'];
				}

				$arResult[$row['ENTITY_ID']]['REACTIONS_LIST'][$row['REACTION']] = (int)$row['TOTAL_VOTES'];

				if (!isset($count[$row['ENTITY_ID']]))
				{
					$count[$row['ENTITY_ID']] = 0;
				}
				$count[$row['ENTITY_ID']] += ((int)$row['TOTAL_VOTES'] >= 0 ? (int)$row['TOTAL_VOTES'] : 0);

				if (
					!isset($foundDefault[$row['ENTITY_ID']])
					&& $row['REACTION'] == self::REACTION_DEFAULT
				)
				{
					$foundDefault[$row['ENTITY_ID']] = true;
				}
			}

			foreach($entityIdList as $eId)
			{
				if (
					!isset($foundDefault[$eId])
					&& isset($count[$eId])
					&& isset($total[$eId])
					&& $count[$eId] >= $total[$eId]
				)
				{
					$arResult[$eId]['REACTIONS_LIST'][self::REACTION_DEFAULT] = 0;
				}
			}

			$sql = "SELECT RVG.ENTITY_ID, RVG.USER_ID, RVG.VALUE, RVG.REACTION
					FROM b_rating_vote RVG
					WHERE RVG.ENTITY_TYPE_ID = '".$DB->ForSql($entityTypeId)."'
					and RVG.ENTITY_ID between ".($bucket*$bucket_size)." AND ".(($bucket+1)*$bucket_size-1);
			$res = $DB->Query($sql);
			while($row = $res->Fetch())
			{
				$arResult[$row['ENTITY_ID']]['USER_VOTE_LIST'][$row['USER_ID']] = $row['VALUE'];
				$arResult[$row['ENTITY_ID']]['USER_REACTION_LIST'][$row['USER_ID']] = (!empty($row['REACTION']) ? $row['REACTION'] : self::REACTION_DEFAULT);
			}

			$CACHE_MANAGER->Set($cache_id, $arResult);
		}

		if (isset($arResult[$entityId]['USER_VOTE_LIST'][$user_id]))
		{
			$arResult[$entityId]['USER_VOTE'] = $arResult[$entityId]['USER_VOTE_LIST'][$user_id];
			$arResult[$entityId]['USER_REACTION'] = (
				!empty($arResult[$entityId]['USER_REACTION_LIST'][$user_id])
					? $arResult[$entityId]['USER_REACTION_LIST'][$user_id]
					: self::REACTION_DEFAULT
			);
			$arResult[$entityId]['USER_HAS_VOTED'] = 'Y';
		}

		return $arResult[$entityId] ?? array();
	}

	public static function GetRatingResult($ID, $entityId)
	{
		global $DB;

		$ID = (int)$ID;

		static $cacheRatingResult = array();
		if(!array_key_exists($ID, $cacheRatingResult))
			$cacheRatingResult[$ID] = array();

		$arResult = array();
		$arToSelect = array();
		if(is_array($entityId))
		{
			foreach($entityId as $value)
			{
				$value = (int)$value;
				if($value > 0)
				{
					if(array_key_exists($value, $cacheRatingResult[$ID]))
						$arResult[$value] = $cacheRatingResult[$ID][$value];
					else
					{
						$arResult[$value] = $cacheRatingResult[$ID][$value] = array();
						$arToSelect[$value] = $value;
					}
				}
			}
		}
		else
		{
			$value = (int)$entityId;
			if($value > 0)
			{
				if(isset($cacheRatingResult[$ID][$value]))
					$arResult[$value] = $cacheRatingResult[$ID][$value];
				else
				{
					$arResult[$value] = $cacheRatingResult[$ID][$value] = array();
					$arToSelect[$value] = $value;
				}
			}
		}

		if(!empty($arToSelect))
		{
			$strSql  = "
				SELECT ENTITY_TYPE_ID, ENTITY_ID, PREVIOUS_VALUE, CURRENT_VALUE, PREVIOUS_POSITION, CURRENT_POSITION
				FROM b_rating_results
				WHERE RATING_ID = '".$ID."'  AND ENTITY_ID IN (".implode(',', $arToSelect).")
			";
			$res = $DB->Query($strSql);
			while($arRes = $res->Fetch())
			{

				$arRes['PROGRESS_VALUE'] = $arRes['CURRENT_VALUE'] - $arRes['PREVIOUS_VALUE'];
				$arRes['PROGRESS_VALUE'] = round($arRes['PROGRESS_VALUE'], 2);
				$arRes['PROGRESS_VALUE'] = $arRes['PROGRESS_VALUE'] > 0? "+".$arRes['PROGRESS_VALUE']: $arRes['PROGRESS_VALUE'];
				$arRes['ROUND_CURRENT_VALUE'] = round($arRes['CURRENT_VALUE']) == 0? 0: round($arRes['CURRENT_VALUE']);
				$arRes['ROUND_PREVIOUS_VALUE'] = round($arRes['PREVIOUS_VALUE']) == 0? 0: round($arRes['CURRENT_VALUE']);
				$arRes['CURRENT_POSITION'] = $arRes['CURRENT_POSITION'] > 0? $arRes['CURRENT_POSITION'] : GetMessage('RATING_NO_POSITION');
				if ($arRes['PREVIOUS_POSITION']>0)
				{
					$arRes['PROGRESS_POSITION'] = $arRes['PREVIOUS_POSITION'] - $arRes['CURRENT_POSITION'];
					$arRes['PROGRESS_POSITION'] = $arRes['PROGRESS_POSITION'] > 0? "+".$arRes['PROGRESS_POSITION']: $arRes['PROGRESS_POSITION'];
				}
				else
				{
					$arRes['PREVIOUS_POSITION'] = 0;
					$arRes['PROGRESS_POSITION'] = 0;
				}

				$arResult[$arRes["ENTITY_ID"]] = $cacheRatingResult[$ID][$arRes["ENTITY_ID"]] = $arRes;
			}
		}
		if(!is_array($entityId) && !empty($arResult))
			$arResult = array_pop($arResult);

		return $arResult;
	}

	public static function AddRatingVote($arParam)
	{
		global $DB, $CACHE_MANAGER;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		if ($arParam['ENTITY_TYPE_ID'] === 'USER' && isset(\Bitrix\Main\Application::getInstance()->getSession()['RATING_VOTE_COUNT']))
		{
			if (\Bitrix\Main\Application::getInstance()->getSession()['RATING_VOTE_COUNT'] >= \Bitrix\Main\Application::getInstance()->getSession()['RATING_USER_VOTE_COUNT'])
			{
				return false;
			}

			\Bitrix\Main\Application::getInstance()->getSession()['RATING_VOTE_COUNT']++;
		}

		$arParam['ENTITY_TYPE_ID'] = mb_substr($arParam['ENTITY_TYPE_ID'], 0, 50);
		$arParam['REACTION'] = ($arParam['REACTION'] <> '' ? $arParam['REACTION'] : self::REACTION_DEFAULT);

		CRatings::CancelRatingVote($arParam);

		$votePlus = $arParam['VALUE'] >= 0 ? true : false;

		$ratingId = CRatings::GetAuthorityRating();

		$arRatingUserProp = CRatings::GetRatingUserProp($ratingId, $arParam['USER_ID']);
		$voteUserWeight = $arRatingUserProp['VOTE_WEIGHT'];

		$sRatingWeightType = COption::GetOptionString("main", "rating_weight_type", "auto");
		if ($sRatingWeightType === 'auto')
		{
			if ($arParam['ENTITY_TYPE_ID'] === 'USER')
			{
				$sRatingAuthrorityWeight = COption::GetOptionString("main", "rating_authority_weight_formula", 'Y');
				if ($sRatingAuthrorityWeight === 'Y')
				{
					$communitySize = COption::GetOptionString("main", "rating_community_size", 1);
					$communityAuthority = COption::GetOptionString("main", "rating_community_authority", 1);
					$voteWeight = COption::GetOptionString("main", "rating_vote_weight", 1);
					$arParam['VALUE'] = $arParam['VALUE']*($communitySize*($voteUserWeight/$voteWeight)/$communityAuthority);
				}
			}
			else
			{
				$arParam['VALUE'] = $arParam['VALUE']*$voteUserWeight;
			}
		}
		else
		{
			$arParam['VALUE'] = $arParam['VALUE']*$voteUserWeight;
		}
		$arFields = array(
			'ACTIVE' => "'Y'",
			'TOTAL_VOTES' => "TOTAL_VOTES+1",
			'TOTAL_VALUE' => "TOTAL_VALUE".($votePlus ? '+' : '').(float)$arParam['VALUE'],
			'LAST_CALCULATED' => $DB->GetNowFunction(),
		);
		$arFields[($votePlus ? 'TOTAL_POSITIVE_VOTES' : 'TOTAL_NEGATIVE_VOTES')] = ($votePlus ? 'TOTAL_POSITIVE_VOTES+1' : 'TOTAL_NEGATIVE_VOTES+1');

		// GetOwnerDocument
		$arParam['OWNER_ID'] = 0;
		foreach(GetModuleEvents("main", "OnGetRatingContentOwner", true) as $arEvent)
		{
			$result = ExecuteModuleEventEx($arEvent, array($arParam));
			if ($result !== false)
				$arParam['OWNER_ID'] = (int)$result;
		}

		$rowAffected = $DB->Update("b_rating_voting", $arFields, "WHERE ENTITY_TYPE_ID='".$DB->ForSql($arParam['ENTITY_TYPE_ID'])."' AND ENTITY_ID='".(int)$arParam['ENTITY_ID']."'");
		if ($rowAffected > 0)
		{
			$rsRV = $DB->Query("SELECT ID, TOTAL_POSITIVE_VOTES FROM b_rating_voting WHERE ENTITY_TYPE_ID='".$DB->ForSql($arParam['ENTITY_TYPE_ID'])."' AND ENTITY_ID='".(int)$arParam['ENTITY_ID']."'");
			$arRV = $rsRV->Fetch();
			$arParam['RATING_VOTING_ID'] = $arRV['ID'];
			$arParam['TOTAL_POSITIVE_VOTES'] = $arRV['TOTAL_POSITIVE_VOTES'];
			$arParam['REACTIONS_LIST'] = array(
				self::REACTION_DEFAULT => (int)$arParam['TOTAL_POSITIVE_VOTES']
			);

			if ($votePlus)
			{
				$rsRVR = $DB->Query("SELECT TOTAL_VOTES FROM b_rating_voting_reaction WHERE ENTITY_TYPE_ID='".$DB->ForSql($arParam['ENTITY_TYPE_ID'])."' AND ENTITY_ID='".(int)$arParam['ENTITY_ID']."'");
				if (!($arRVR = $rsRVR->fetch())) // reactions not initialized
				{
					$merge = $helper->prepareMerge('b_rating_voting_reaction', ['ENTITY_TYPE_ID', 'ENTITY_ID', 'REACTION'], [
						'ENTITY_TYPE_ID' => $arParam['ENTITY_TYPE_ID'],
						'ENTITY_ID' => $arParam['ENTITY_ID'],
						'REACTION' => self::REACTION_DEFAULT,
						'TOTAL_VOTES' => $arRV['TOTAL_POSITIVE_VOTES'],
					], [
						'TOTAL_VOTES' => $arRV['TOTAL_POSITIVE_VOTES'],
					]);
					if ($merge[0])
					{
						$DB->query($merge[0]);
					}
				}

				$merge = $helper->prepareMerge('b_rating_voting_reaction', ['ENTITY_TYPE_ID', 'ENTITY_ID', 'REACTION'], [
					'ENTITY_TYPE_ID' => $arParam['ENTITY_TYPE_ID'],
					'ENTITY_ID' => $arParam['ENTITY_ID'],
					'REACTION' => $arParam['REACTION'],
					'TOTAL_VOTES' => 1,
				], [
					'TOTAL_VOTES' => new \Bitrix\Main\DB\SqlExpression('b_rating_voting_reaction.TOTAL_VOTES + 1'),
				]);
				if ($merge[0])
				{
					$DB->query($merge[0]);
				}
			}
		}
		else
		{
			$arFields = array(
				"ENTITY_TYPE_ID" => "'".$DB->ForSql($arParam["ENTITY_TYPE_ID"])."'",
				"ENTITY_ID" => (int)$arParam['ENTITY_ID'],
				"OWNER_ID" => (int)$arParam['OWNER_ID'],
				"ACTIVE" => "'Y'",
				"CREATED" => $DB->GetNowFunction(),
				"LAST_CALCULATED" => $DB->GetNowFunction(),
				"TOTAL_VOTES" => 1,
				"TOTAL_VALUE" => (float)$arParam['VALUE'],
				"TOTAL_POSITIVE_VOTES" => ($votePlus ? 1 : 0),
				"TOTAL_NEGATIVE_VOTES" => ($votePlus ? 0 : 1)
			);
			$arParam['RATING_VOTING_ID'] = $DB->Insert("b_rating_voting", $arFields);
			$arParam['TOTAL_POSITIVE_VOTES'] = ($votePlus ? 1 : 0);

			$arParam['REACTIONS_LIST'] = array(
				self::REACTION_DEFAULT => (int)$arParam['TOTAL_POSITIVE_VOTES']
			);

			if ($votePlus)
			{
				$merge = $helper->prepareMerge('b_rating_voting_reaction', ['ENTITY_TYPE_ID', 'ENTITY_ID', 'REACTION'], [
					'ENTITY_TYPE_ID' => $arParam['ENTITY_TYPE_ID'],
					'ENTITY_ID' => $arParam['ENTITY_ID'],
					'REACTION' => $arParam['REACTION'],
					'TOTAL_VOTES' => 1,
				], [
					'TOTAL_VOTES' => 1,
				]);
				if ($merge[0])
				{
					$DB->query($merge[0]);
				}
			}
		}

		$rsRVR = $DB->Query("SELECT REACTION, TOTAL_VOTES FROM b_rating_voting_reaction WHERE ENTITY_TYPE_ID='".$DB->ForSql($arParam['ENTITY_TYPE_ID'])."' AND ENTITY_ID='".(int)$arParam['ENTITY_ID']."'");
		while($arRVR = $rsRVR->fetch())
		{
			$arParam['REACTIONS_LIST'][$arRVR['REACTION']] = $arRVR['TOTAL_VOTES'];
		}

		$arFields = array(
			"RATING_VOTING_ID"	=> (int)$arParam['RATING_VOTING_ID'],
			"ENTITY_TYPE_ID"		=> "'".$DB->ForSql($arParam["ENTITY_TYPE_ID"])."'",
			"ENTITY_ID"				=> (int)$arParam['ENTITY_ID'],
			"VALUE"				=> (float)$arParam['VALUE'],
			"ACTIVE"				=> "'Y'",
			"CREATED"			=> $DB->GetNowFunction(),
			"USER_ID"			=> (int)$arParam['USER_ID'],
			"USER_IP"			=> "'".$DB->ForSql($arParam["USER_IP"])."'",
			"OWNER_ID"			=> (int)$arParam['OWNER_ID'],
			"REACTION"			=> "'".$DB->ForSql($arParam["REACTION"])."'"
		);
		$ID = $DB->Insert("b_rating_vote", $arFields);

		foreach(GetModuleEvents("main", "OnAddRatingVote", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array((int)$ID, $arParam));

		$userData = \CAllRatings::getUserData((int)$arParam['USER_ID'], (float)$arParam['VALUE']);
		if (CModule::IncludeModule('pull'))
		{
			CPullStack::AddShared(Array(
				'module_id' => 'main',
				'command' => 'rating_vote',
				'params' => Array(
					"TYPE" => "ADD",
					"USER_ID" => (int)$arParam['USER_ID'],
					"ENTITY_TYPE_ID" => $arParam["ENTITY_TYPE_ID"],
					"ENTITY_ID" => (int)$arParam['ENTITY_ID'],
					"TOTAL_POSITIVE_VOTES" => $arParam['TOTAL_POSITIVE_VOTES'],
					"RESULT" => $votePlus? 'PLUS': 'MINUS',
					"USER_DATA" => $userData,
					"REACTION" => $arParam['REACTION'],
					"REACTIONS_LIST" => $arParam['REACTIONS_LIST']
				)
			));
		}

		if (CACHED_b_rating_vote!==false)
		{
			$bucket_size = (int)CACHED_b_rating_bucket_size;
			if($bucket_size <= 0)
				$bucket_size = 100;
			$bucket = (int)((int)$arParam['ENTITY_ID'] / $bucket_size);
			$CACHE_MANAGER->Clean("b_rvg_".$DB->ForSql($arParam["ENTITY_TYPE_ID"]).$bucket, "b_rating_voting");
		}

		return $userData;
	}

	public static function ChangeRatingVote($arParam)
	{
		global $DB, $CACHE_MANAGER;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$arParam['ENTITY_TYPE_ID'] = mb_substr($arParam['ENTITY_TYPE_ID'], 0, 50);
		$arParam['REACTION'] = ($arParam['REACTION'] <> '' ? $arParam['REACTION'] : self::REACTION_DEFAULT);
		$userData = \CAllRatings::getUserData((int)$arParam['USER_ID'], (float)$arParam['VALUE']);

		$sqlStr = "
			SELECT
				RVG.ID,
				RV.ID AS VOTE_ID,
				RV.REACTION AS REACTION,
				RV.VALUE AS VOTE_VALUE,
				RVG.TOTAL_POSITIVE_VOTES
			FROM
				b_rating_voting RVG,
				b_rating_vote RV
			WHERE
				RVG.ENTITY_TYPE_ID = '".$DB->ForSql($arParam['ENTITY_TYPE_ID'])."'
			and RVG.ENTITY_ID = ".(int)$arParam['ENTITY_ID']."
			and RVG.ID = RV.RATING_VOTING_ID
			and RV.USER_ID = ".(int)$arParam['USER_ID'];

		$res = $DB->Query($sqlStr);
		if ($arVote = $res->Fetch())
		{
			// GetOwnerDocument
			$arParam['OWNER_ID'] = 0;
			foreach(GetModuleEvents("main", "OnGetRatingContentOwner", true) as $arEvent)
			{
				$result = ExecuteModuleEventEx($arEvent, array($arParam));
				if ($result !== false)
					$arParam['OWNER_ID'] = (int)$result;
			}

			$votePlus = $arVote['VOTE_VALUE'] >= 0 ? true : false;
			$arVote['REACTION_OLD'] = ($arVote['REACTION'] <> '' ? $arVote['REACTION'] : self::REACTION_DEFAULT);

			if (!$votePlus)
			{
				return false;
			}

			$rsRV = $DB->Query("SELECT ID, TOTAL_POSITIVE_VOTES FROM b_rating_voting WHERE ENTITY_TYPE_ID='".$DB->ForSql($arParam['ENTITY_TYPE_ID'])."' AND ENTITY_ID='".(int)$arParam['ENTITY_ID']."'");
			if ($arRV = $rsRV->Fetch())
			{
				$arParam['RATING_VOTING_ID'] = $arRV['ID'];
				$arParam['TOTAL_POSITIVE_VOTES'] = $arRV['TOTAL_POSITIVE_VOTES'];
				$arParam['REACTIONS_LIST'] = array(
					self::REACTION_DEFAULT => (int)$arParam['TOTAL_POSITIVE_VOTES']
				);
			}
			else
			{
				return false;
			}

			$rsRVR = $DB->Query("SELECT TOTAL_VOTES FROM b_rating_voting_reaction WHERE ENTITY_TYPE_ID='".$DB->ForSql($arParam['ENTITY_TYPE_ID'])."' AND ENTITY_ID='".(int)$arParam['ENTITY_ID']."'");
			if (!($arRVR = $rsRVR->fetch())) // reactions not initialized
			{
				$merge = $helper->prepareMerge('b_rating_voting_reaction', ['ENTITY_TYPE_ID', 'ENTITY_ID', 'REACTION'], [
					'ENTITY_TYPE_ID' => $arParam['ENTITY_TYPE_ID'],
					'ENTITY_ID' => $arParam['ENTITY_ID'],
					'REACTION' => self::REACTION_DEFAULT,
					'TOTAL_VOTES' => $arRV['TOTAL_POSITIVE_VOTES'],
				], [
					'TOTAL_VOTES' => $arRV['TOTAL_POSITIVE_VOTES'],
				]);
				if ($merge[0])
				{
					$DB->query($merge[0]);
				}
			}

			$merge = $helper->prepareMerge('b_rating_voting_reaction', ['ENTITY_TYPE_ID', 'ENTITY_ID', 'REACTION'], [
				'ENTITY_TYPE_ID' => $arParam['ENTITY_TYPE_ID'],
				'ENTITY_ID' => $arParam['ENTITY_ID'],
				'REACTION' => $arParam['REACTION'],
				'TOTAL_VOTES' => 1,
			], [
				'TOTAL_VOTES' => new \Bitrix\Main\DB\SqlExpression('b_rating_voting_reaction.TOTAL_VOTES + 1'),
			]);
			if ($merge[0])
			{
				$DB->query($merge[0]);
			}

			if (!empty($arVote['REACTION_OLD']))
			{
				$DB->Query("UPDATE b_rating_voting_reaction SET TOTAL_VOTES = TOTAL_VOTES - 1 WHERE ENTITY_TYPE_ID = '".$DB->ForSql($arParam['ENTITY_TYPE_ID'])."' AND ENTITY_ID = '".(int)$arParam['ENTITY_ID']."' AND REACTION = '".$DB->ForSql($arVote['REACTION_OLD'])."'");
			}

			$rsRVR = $DB->Query("SELECT REACTION, TOTAL_VOTES FROM b_rating_voting_reaction WHERE ENTITY_TYPE_ID='".$DB->ForSql($arParam['ENTITY_TYPE_ID'])."' AND ENTITY_ID='".(int)$arParam['ENTITY_ID']."'");
			while($arRVR = $rsRVR->fetch())
			{
				$arParam['REACTIONS_LIST'][$arRVR['REACTION']] = $arRVR['TOTAL_VOTES'];
			}

			$arFields = array(
				"CREATED" => $DB->GetNowFunction(),
				"USER_IP" => "'".$DB->ForSql($arParam["USER_IP"])."'",
				"REACTION" => "'".$DB->ForSql($arParam["REACTION"])."'"
			);

			$ID = $DB->Update("b_rating_vote", $arFields, "WHERE RATING_VOTING_ID=".(int)$arParam['RATING_VOTING_ID']." AND USER_ID=".(int)$arParam['USER_ID']);
			if (!$ID)
			{
				return false;
			}

			foreach(GetModuleEvents("main", "OnChangeRatingVote", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array((int)$ID, $arParam));
			}

			if (CModule::IncludeModule('pull'))
			{
				CPullStack::AddShared(Array(
					'module_id' => 'main',
					'command' => 'rating_vote',
					'params' => Array(
						"TYPE" => "CHANGE",
						"USER_ID" => (int)$arParam['USER_ID'],
						"ENTITY_TYPE_ID" => $arParam["ENTITY_TYPE_ID"],
						"ENTITY_ID" => (int)$arParam['ENTITY_ID'],
						"TOTAL_POSITIVE_VOTES" => $arParam['TOTAL_POSITIVE_VOTES'],
						"RESULT" => 'CHANGE',
						"USER_DATA" => $userData,
						"REACTION" => $arParam['REACTION'],
						"REACTION_OLD" => $arVote['REACTION'],
						"REACTIONS_LIST" => $arParam['REACTIONS_LIST']
					)
				));
			}

			if (CACHED_b_rating_vote!==false)
			{
				$bucket_size = (int)CACHED_b_rating_bucket_size;
				if($bucket_size <= 0)
				{
					$bucket_size = 100;
				}
				$bucket = (int)((int)$arParam['ENTITY_ID'] / $bucket_size);
				$CACHE_MANAGER->Clean("b_rvg_".$DB->ForSql($arParam["ENTITY_TYPE_ID"]).$bucket, "b_rating_voting");
			}
		}

		return $userData;
	}

	public static function CancelRatingVote($arParam)
	{
		global $DB, $CACHE_MANAGER;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$sqlStr = "
			SELECT
				RVG.ID,
				RV.ID AS VOTE_ID,
				RV.REACTION AS REACTION,
				RV.VALUE AS VOTE_VALUE,
				RVG.TOTAL_POSITIVE_VOTES
			FROM
				b_rating_voting RVG,
				b_rating_vote RV
			WHERE
				RVG.ENTITY_TYPE_ID = '".$DB->ForSql($arParam['ENTITY_TYPE_ID'])."'
			and RVG.ENTITY_ID = ".(int)$arParam['ENTITY_ID']."
			and RVG.ID = RV.RATING_VOTING_ID
			and RV.USER_ID = ".(int)$arParam['USER_ID'];

		$res = $DB->Query($sqlStr);
		if ($arVote = $res->Fetch())
		{
			$votePlus = $arVote['VOTE_VALUE'] >= 0 ? true : false;
			$arVote['REACTION'] = ($arVote['REACTION'] <> '' ? $arVote['REACTION'] : self::REACTION_DEFAULT);

			if ($votePlus)
			{
				$rsRVR = $DB->Query("SELECT TOTAL_VOTES FROM b_rating_voting_reaction WHERE ENTITY_TYPE_ID='".$DB->ForSql($arParam['ENTITY_TYPE_ID'])."' AND ENTITY_ID='".(int)$arParam['ENTITY_ID']."'");
				if (!($arRVR = $rsRVR->fetch())) // reactions not initialized
				{
					$merge = $helper->prepareMerge('b_rating_voting_reaction', ['ENTITY_TYPE_ID', 'ENTITY_ID', 'REACTION'], [
						'ENTITY_TYPE_ID' => $arParam['ENTITY_TYPE_ID'],
						'ENTITY_ID' => $arParam['ENTITY_ID'],
						'REACTION' => self::REACTION_DEFAULT,
						'TOTAL_VOTES' => $arVote['TOTAL_POSITIVE_VOTES'],
					], [
						'TOTAL_VOTES' => $arVote['TOTAL_POSITIVE_VOTES'],
					]);
					if ($merge[0])
					{
						$DB->query($merge[0]);
					}
				}
			}

			$arFields = array(
				'TOTAL_VOTES' => "TOTAL_VOTES-1",
				'TOTAL_VALUE' => "TOTAL_VALUE".($votePlus ? '-'.(float)$arVote['VOTE_VALUE'] : '+'.(float)(-1 * $arVote['VOTE_VALUE'])),
				'LAST_CALCULATED' => $DB->GetNowFunction(),
			);
			$arFields[($votePlus ? 'TOTAL_POSITIVE_VOTES' : 'TOTAL_NEGATIVE_VOTES')] = ($votePlus ? 'TOTAL_POSITIVE_VOTES-1' : 'TOTAL_NEGATIVE_VOTES-1');
			$DB->Update("b_rating_voting", $arFields, "WHERE ID=".(int)$arVote['ID']);
			if ($votePlus)
			{
				$merge = $helper->prepareMerge('b_rating_voting_reaction', ['ENTITY_TYPE_ID', 'ENTITY_ID', 'REACTION'], [
					'ENTITY_TYPE_ID' => $arParam['ENTITY_TYPE_ID'],
					'ENTITY_ID' => $arParam['ENTITY_ID'],
					'REACTION' => $arVote['REACTION'],
					'TOTAL_VOTES' => 0,
				], [
					'TOTAL_VOTES' => new \Bitrix\Main\DB\SqlExpression('b_rating_voting_reaction.TOTAL_VOTES - 1'),
				]);
				if ($merge[0])
				{
					$DB->query($merge[0]);
				}
			}
			$DB->Query("DELETE FROM b_rating_vote WHERE ID=".(int)$arVote['VOTE_ID']);

			$arParam['REACTIONS_LIST'] = array();
			$rsRVR = $DB->Query("SELECT REACTION, TOTAL_VOTES FROM b_rating_voting_reaction WHERE ENTITY_TYPE_ID='".$DB->ForSql($arParam['ENTITY_TYPE_ID'])."' AND ENTITY_ID='".(int)$arParam['ENTITY_ID']."'");
			while($arRVR = $rsRVR->fetch())
			{
				$arParam['REACTIONS_LIST'][$arRVR['REACTION']] = $arRVR['TOTAL_VOTES'];
			}

			foreach(GetModuleEvents("main", "OnCancelRatingVote", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array((int)$arVote['VOTE_ID'], $arParam));

			$userData = \CAllRatings::getUserData((int)$arParam['USER_ID'], (float)$arVote['VOTE_VALUE']);

			if (CModule::IncludeModule('pull'))
			{
				CPullStack::AddShared(Array(
					'module_id' => 'main',
					'command' => 'rating_vote',
					'params' => Array(
						"TYPE" => "CANCEL",
						"USER_ID" => (int)$arParam['USER_ID'],
						"ENTITY_TYPE_ID" => $arParam["ENTITY_TYPE_ID"],
						"ENTITY_ID" => (int)$arParam['ENTITY_ID'],
						"TOTAL_POSITIVE_VOTES" => (int)($arVote['TOTAL_POSITIVE_VOTES'] + ($votePlus ? -1 : 1)),
						"RESULT" => $votePlus? 'PLUS': 'MINUS',
						"USER_DATA" => $userData,
						"REACTION" => $arVote['REACTION'],
						"REACTIONS_LIST" => $arParam['REACTIONS_LIST']
					)
				));
			}

			if (CACHED_b_rating_vote!==false)
			{
				$bucket_size = (int)CACHED_b_rating_bucket_size;
				if($bucket_size <= 0)
					$bucket_size = 100;
				$bucket = (int)((int)$arParam['ENTITY_ID'] / $bucket_size);
				$CACHE_MANAGER->Clean("b_rvg_".$DB->ForSql($arParam["ENTITY_TYPE_ID"]).$bucket, "b_rating_voting");
			}

			return $userData;
		}

		return false;
	}

	public static function UpdateRatingUserBonus($arParam)
	{
		global $DB;

		$arParam['RATING_ID'] = (int)$arParam['RATING_ID'];
		$arParam['ENTITY_ID'] = (int)$arParam['ENTITY_ID'];
		$arParam['BONUS'] = (float)$arParam['BONUS'];

		$arFields = array(
			'RATING_ID'	=> $arParam['RATING_ID'],
			'ENTITY_ID'	=> $arParam['ENTITY_ID'],
			'BONUS'		=> $arParam['BONUS'],
		);

		if (isset($arParam['VOTE_WEIGHT']))
			$arFields['VOTE_WEIGHT'] = (float)$arParam['VOTE_WEIGHT'];

		if (isset($arParam['VOTE_COUNT']))
			$arFields['VOTE_COUNT'] = (int)$arParam['VOTE_COUNT'];

		$rows = $DB->Update("b_rating_user", $arFields, "WHERE RATING_ID = ".$arParam['RATING_ID']." AND ENTITY_ID = ".$arParam['ENTITY_ID']);
		if ($rows == 0)
		{
			$rsRB = $DB->Query("SELECT * FROM b_rating_user WHERE RATING_ID = ".$arParam['RATING_ID']." AND ENTITY_ID = ".$arParam['ENTITY_ID']);
			if (!$rsRB->SelectedRowsCount())
				$DB->Insert("b_rating_user", $arFields);
		}
		if (CACHED_b_rating_vote!==false)
		{
			global $CACHE_MANAGER;
			$bucket_size = (int)CACHED_b_rating_bucket_size;
			if($bucket_size <= 0)
				$bucket_size = 100;

			$CACHE_MANAGER->Clean("b_rvu_".$arParam['RATING_ID'].(int)($arParam['ENTITY_ID'] / $bucket_size), "b_rating_user");
		}
		return true;
	}

	public static function GetRatingUserProp($ratingId, $entityId)
	{
		global $DB;

		$ratingId = (int)$ratingId;

		static $cache = array();
		if(!array_key_exists($ratingId, $cache))
			$cache[$ratingId] = array();

		$arResult = array();
		$arToSelect = array();
		if(is_array($entityId))
		{
			foreach($entityId as $value)
			{
				$value = (int)$value;
				if($value > 0)
				{
					if(array_key_exists($value, $cache[$ratingId]))
						$arResult[$value] = $cache[$ratingId][$value];
					else
					{
						$arResult[$value] = $cache[$ratingId][$value] = array();
						$arToSelect[$value] = $value;
					}
				}
			}
		}
		else
		{
			$value = (int)$entityId;
			if($value > 0)
			{
				if(isset($cache[$ratingId][$value]))
					$arResult[$value] = $cache[$ratingId][$value];
				else
				{
					$arResult[$value] = $cache[$ratingId][$value] = array();
					$arToSelect[$value] = $value;
				}
			}
		}

		if(!empty($arToSelect))
		{
			$strSql  = "
				SELECT RATING_ID, ENTITY_ID, BONUS, VOTE_WEIGHT, VOTE_COUNT
				FROM b_rating_user
				WHERE RATING_ID = '".$ratingId."' AND ENTITY_ID IN (".implode(',', $arToSelect).")
			";
			$res = $DB->Query($strSql);
			while($arRes = $res->Fetch())
				$arResult[$arRes["ENTITY_ID"]] = $cache[$ratingId][$arRes["ENTITY_ID"]] = $arRes;
		}

		if(!is_array($entityId) && !empty($arResult))
			$arResult = array_pop($arResult);

		return $arResult;
	}

	public static function GetRatingUserPropEx($ratingId, $entityId)
	{
		global $DB, $CACHE_MANAGER;

		$ratingId = (int)$ratingId;
		$entityId = (int)$entityId;

		$arDefaultResult = array(
			"RATING_ID" => $ratingId,
			"ENTITY_ID" => $entityId,
			"BONUS" => 0,
			"VOTE_WEIGHT" => 0,
			"VOTE_COUNT" => 0
		);
		if ($ratingId <= 0 || $entityId <= 0)
			return $arDefaultResult;

		$bucket_size = (int)CACHED_b_rating_bucket_size;
		if($bucket_size <= 0)
			$bucket_size = 100;

		$bucket = (int)($entityId / $bucket_size);
		$arResult = $CACHE_MANAGER->Read(CACHED_b_rating, $cache_id="b_rvu_".$ratingId.$bucket, "b_rating_user");
		if($arResult)
		{
			$arResult = $CACHE_MANAGER->Get($cache_id);
		}
		if (!$arResult)
		{
			$sql_str = "
				SELECT RATING_ID, ENTITY_ID, BONUS, VOTE_WEIGHT, VOTE_COUNT
				FROM b_rating_user
				WHERE RATING_ID = '".$ratingId."'
				and ENTITY_ID between ".($bucket*$bucket_size)." AND ".(($bucket+1)*$bucket_size-1)."
			";
			$res = $DB->Query($sql_str);
			while($arRes = $res->Fetch())
				$arResult[$arRes["ENTITY_ID"]] = $arRes;

			$CACHE_MANAGER->Set($cache_id, $arResult);
		}

		return $arResult[$entityId] ?? $arDefaultResult;
	}

	public static function GetAuthorityRating()
	{
		global $DB;

		$authorityRatingId = COption::GetOptionString("main", "rating_authority_rating", null);
		if(is_null($authorityRatingId))
		{
			$db_res = CRatings::GetList(array("ID" => "ASC"), array( "ENTITY_ID" => "USER", "AUTHORITY" => "Y"));
			$res = $db_res->Fetch();

			$authorityRatingId = (int)$res['ID'];
			COption::SetOptionString("main", "rating_authority_rating", $authorityRatingId);
		}

		return $authorityRatingId;
	}

	public static function GetWeightList($arSort=array(), $arFilter=Array())
	{
		global $DB;

		$arSqlSearch = Array();
		$strSqlSearch = "";

		if (is_array($arFilter))
		{
			foreach ($arFilter as $key => $val)
			{
				if ((string)$val == '' || $val === "NOT_REF")
					continue;
				switch(strtoupper($key))
				{
					case "ID":
						$arSqlSearch[] = GetFilterQuery("RW.ID", $val, "N");
						break;
					case "RATING_FROM":
						$arSqlSearch[] = GetFilterQuery("RW.RATING_FROM", $val, "N");
						break;
					case "RATING_TO":
						$arSqlSearch[] = GetFilterQuery("RW.RATING_TO", $val, "N");
						break;
					case "WEIGHT":
						$arSqlSearch[] = GetFilterQuery("RW.WEIGHT", $val, "N");
						break;
					case "COUNT":
						$arSqlSearch[] = GetFilterQuery("RW.COUNT", $val, "N");
						break;
					case "MAX":
						if(in_array($val, Array('Y', 'N')))
						{
							$arSqlSearch[] = "R.MAX = '".$val."'";
						}
						break;
				}
			}
		}

		$sOrder = "";
		foreach($arSort as $key=>$val)
		{
			$ord = (mb_strtoupper($val) <> "ASC"? "DESC":"ASC");
			switch(mb_strtoupper($key))
			{
				case "ID":
					$sOrder .= ", RW.ID ".$ord;
					break;
				case "RATING_FROM":
					$sOrder .= ", RW.RATING_FROM ".$ord;
					break;
				case "RATING_TO":
					$sOrder .= ", RW.RATING_TO ".$ord;
					break;
				case "WEIGHT":
					$sOrder .= ", RW.WEIGHT ".$ord;
					break;
				case "COUNT":
					$sOrder .= ", RW.COUNT ".$ord;
					break;
			}
		}

		if ($sOrder == '')
			$sOrder = "RW.ID DESC";

		$strSqlOrder = " ORDER BY ".TrimEx($sOrder,",");

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				RW.ID, RW.RATING_FROM, RW.RATING_TO, RW.WEIGHT, RW.COUNT
			FROM
				b_rating_weight RW
			WHERE
			".$strSqlSearch."
			".$strSqlOrder;
		return $DB->Query($strSql);
	}

	public static function SetWeight($arConfigs)
	{
		global $DB;

		usort($arConfigs, array('CRatings', '__SortWeight'));
		// prepare insert
		$arAdd = array();
		foreach($arConfigs as $key => $arConfig)
		{
			//If the first condition is restricted to the bottom, otherwise we take the previous high value
			if ($key == 0)
				$arConfig['RATING_FROM'] = -1000000;
			else
				$arConfig['RATING_FROM'] = (float)$arConfigs[$key - 1]['RATING_TO'] +0.0001;
			// If this last condition is restricted to the top
			if (!array_key_exists('RATING_TO', $arConfig))
				$arConfig['RATING_TO'] = 1000000;
			elseif ($arConfig['RATING_TO'] > 1000000)
				$arConfig['RATING_TO'] = 1000000;

			$arAdd[$key]['RATING_FROM']   = (float)$arConfig['RATING_FROM'];
			$arAdd[$key]['RATING_TO']     = (float)$arConfig['RATING_TO'];
			$arAdd[$key]['WEIGHT'] = (float)$arConfig['WEIGHT'];
			$arAdd[$key]['COUNT']  = (int)$arConfig['COUNT'];
			$arConfigs[$key] = $arAdd[$key];
		}
		// insert
		$DB->Query("DELETE FROM b_rating_weight");
		foreach($arAdd as $key => $arFields)
			$DB->Insert("b_rating_weight", $arFields);

		return true;
	}

	public static function SetVoteGroup($arGroupId, $type)
	{
		global $DB, $CACHE_MANAGER;

		if (!in_array($type, array('R', 'A')))
			return false;

		if (!is_array($arGroupId))
			return false;

		$arFields = array();

		foreach ($arGroupId as $key => $value)
		{
			$arField = array();
			$arField['GROUP_ID'] = (int)$value;
			$arField['TYPE'] = "'".$type."'";
			$arFields[$key] = $arField;
		}

		$DB->Query("DELETE FROM b_rating_vote_group WHERE TYPE = '".$type."'");
		foreach($arFields as $key => $arField)
			$DB->Insert("b_rating_vote_group", $arField);

		$CACHE_MANAGER->Clean("ratings_vg");

		return true;
	}

	public static function GetVoteGroup($type = '')
	{
		global $DB;

		$bAllType = false;
		if (!in_array($type, array('R', 'A')))
			$bAllType = true;

		$strSql = "SELECT ID, GROUP_ID, TYPE FROM b_rating_vote_group RVG";

		if (!$bAllType)
			$strSql .= " WHERE TYPE = '".$type."'";

		return $DB->Query($strSql);
	}

	public static function GetVoteGroupEx($type = '')
	{
		global $DB, $CACHE_MANAGER;

		$res = $CACHE_MANAGER->Read(2592000, "ratings_vg");
		if ($res)
		{
			$arResult = $CACHE_MANAGER->Get("ratings_vg");
		}
		else
		{
			$strSql = "SELECT GROUP_ID, TYPE FROM b_rating_vote_group RVG";
			$res = $DB->Query($strSql);
			while($arRes = $res->Fetch($res))
			{
				$arResult[] = $arRes;
			}
			$CACHE_MANAGER->Set("ratings_vg", $arResult);
		}
		if ($type != '')
		{
			foreach ($arResult as $key => $value)
			{
				if ($value['TYPE'] != $type)
					unset($arResult[$key]);
			}
		}
		return $arResult;
	}

	public static function ClearData()
	{
		global $DB, $CACHE_MANAGER;

		$DB->Query("TRUNCATE TABLE b_rating_prepare");
		$DB->Query("TRUNCATE TABLE b_rating_voting_prepare");

		$DB->Query("TRUNCATE TABLE b_rating_results");
		$DB->Query("TRUNCATE TABLE b_rating_component_results");

		$DB->Query("TRUNCATE TABLE b_rating_vote");
		$DB->Query("TRUNCATE TABLE b_rating_voting");
		$DB->Query("TRUNCATE TABLE b_rating_voting_reaction");

		$DB->Query("UPDATE b_rating_user SET VOTE_WEIGHT = 0, VOTE_COUNT = 0");

		$CACHE_MANAGER->CleanDir("b_rating_voting");
		$CACHE_MANAGER->CleanDir("b_rating_user");

		return true;
	}

	public static function OnUserDelete($ID)
	{
		CRatings::DeleteByUser($ID);
		return true;
	}

	public static function OnAfterUserRegister($arFields)
	{
		global $DB;

		if (isset($arFields['EXTERNAL_AUTH_ID']) && in_array($arFields['EXTERNAL_AUTH_ID'], \Bitrix\Main\UserTable::getExternalUserTypes(), true))
		{
			return false;
		}

		$userId = isset($arFields["USER_ID"]) ? (int)$arFields["USER_ID"] : (isset($arFields["ID"]) ? (int)$arFields["ID"] : 0);
		if($userId>0)
		{
			$authorityRatingId = CRatings::GetAuthorityRating();
			$ratingStartValue = COption::GetOptionString("main", "rating_start_authority", 3);
			$ratingCountVote = COption::GetOptionString("main", "rating_count_vote", 10);

			$arParam = array(
				'RATING_ID' => $authorityRatingId,
				'ENTITY_ID' => $userId,
				'BONUS' => (int)$ratingStartValue,
				'VOTE_WEIGHT' => (int)$ratingStartValue *COption::GetOptionString("main", "rating_vote_weight", 1),
				'VOTE_COUNT' => (int)$ratingCountVote + (int)$ratingStartValue,
			);
			CRatings::UpdateRatingUserBonus($arParam);

			if (IsModuleInstalled("intranet"))
			{
				$strSql = "INSERT INTO b_rating_subordinate (RATING_ID, ENTITY_ID, VOTES) VALUES ('".$authorityRatingId."', '".$userId."', '".((int)$ratingCountVote + (int)$ratingStartValue)."')";
				$DB->Query($strSql);
			}

			$sRatingAssignType = COption::GetOptionString("main", "rating_assign_type", 'manual');
			if ($sRatingAssignType === 'auto')
			{
				$assignRatingGroup = COption::GetOptionString("main", "rating_assign_rating_group", 0);
				$assignAuthorityGroup = COption::GetOptionString("main", "rating_assign_authority_group", 0);
				if ($assignRatingGroup == 0 && $assignAuthorityGroup == 0)
					return false;

				$arGroups = array();
				if ($assignRatingGroup > 0)
					$arGroups[] = (int)$assignRatingGroup;
				if ($assignAuthorityGroup > 0 && $assignRatingGroup != $assignAuthorityGroup)
					$arGroups[] = (int)$assignAuthorityGroup;

				if(!empty($arGroups))
					CUser::AppendUserGroup($userId, $arGroups);
			}
			if (CACHED_b_rating_vote!==false)
			{
				global $CACHE_MANAGER;
				$bucket_size = (int)CACHED_b_rating_bucket_size;
				if($bucket_size <= 0)
					$bucket_size = 100;

				$bucket = (int)($userId / $bucket_size);
				$CACHE_MANAGER->Clean("b_rvu_".$authorityRatingId.$bucket, "b_rating_user");
			}
		}
	}

	public static function __SortWeight($a, $b)
	{
		if (isset($a['RATING_FROM']) || isset($b['RATING_FROM']))
			return 1;

		return (float)$a['RATING_TO'] < (float)$b['RATING_TO'] ? -1 : 1;
	}

	// check only general field
	public static function __CheckFields($arFields)
	{
		$aMsg = array();

		if(is_set($arFields, "NAME") && trim($arFields["NAME"])=="")
			$aMsg[] = array("id"=>"NAME", "text"=>GetMessage("RATING_GENERAL_ERR_NAME"));
		if(is_set($arFields, "ACTIVE") && !($arFields["ACTIVE"] === 'Y' || $arFields["ACTIVE"] === 'N'))
			$aMsg[] = array("id"=>"ACTIVE", "text"=>GetMessage("RATING_GENERAL_ERR_ACTIVE"));
		if(is_set($arFields, "ENTITY_ID"))
		{
			$arObjects = CRatings::GetRatingObjects();
			if(!in_array($arFields['ENTITY_ID'], $arObjects))
				$aMsg[] = array("id"=>"ENTITY_ID", "text"=>GetMessage("RATING_GENERAL_ERR_ENTITY_ID"));
		}
		if(is_set($arFields, "CALCULATION_METHOD") && trim($arFields["CALCULATION_METHOD"])=="")
			$aMsg[] = array("id"=>"CALCULATION_METHOD", "text"=>GetMessage("RATING_GENERAL_ERR_CAL_METHOD"));

		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}

		return true;
	}

	// creates a configuration record for each item rating
	public static function __AddComponents($ID, $arFields)
	{
		global $DB;

		$arRatingConfigs = CRatings::GetRatingConfigs($arFields["ENTITY_ID"], false);

		$ID = (int)$ID;

		foreach ($arFields['CONFIGS'] as $MODULE_ID => $RAT_ARRAY)
		{
			if (!is_array($RAT_ARRAY))
				continue;

			foreach ($RAT_ARRAY as $RAT_TYPE => $COMPONENT)
			{
				if (!is_array($COMPONENT))
					continue;

				foreach ($COMPONENT as $COMPONENT_NAME => $COMPONENT_VALUE)
				{
					if (!isset($arRatingConfigs[$MODULE_ID][$MODULE_ID."_".$RAT_TYPE."_".$COMPONENT_NAME]))
						continue;

					$arFields_i = Array(
						"RATING_ID"			=> $ID,
						"ACTIVE"			=> isset($COMPONENT_VALUE["ACTIVE"]) && $COMPONENT_VALUE["ACTIVE"] === 'Y' ? 'Y' : 'N',
						"ENTITY_ID"			=> $arFields["ENTITY_ID"],
						"MODULE_ID"			=> $MODULE_ID,
						"RATING_TYPE"		=> $RAT_TYPE,
						"NAME"				=> $COMPONENT_NAME,
						"COMPLEX_NAME"		=> $arFields["ENTITY_ID"].'_'.$MODULE_ID.'_'.$RAT_TYPE.'_'.$COMPONENT_NAME,
						"CLASS"				=> $arRatingConfigs[$MODULE_ID][$MODULE_ID."_".$RAT_TYPE."_".$COMPONENT_NAME]["CLASS"],
						"CALC_METHOD"		=> $arRatingConfigs[$MODULE_ID][$MODULE_ID."_".$RAT_TYPE."_".$COMPONENT_NAME]["CALC_METHOD"],
						"EXCEPTION_METHOD"	=> $arRatingConfigs[$MODULE_ID][$MODULE_ID."_".$RAT_TYPE."_".$COMPONENT_NAME]["EXCEPTION_METHOD"],
						"REFRESH_INTERVAL"	=> $arRatingConfigs[$MODULE_ID][$MODULE_ID."_".$RAT_TYPE."_".$COMPONENT_NAME]["REFRESH_TIME"],
						"~LAST_MODIFIED"	=> $DB->GetNowFunction(),
						"~NEXT_CALCULATION" => $DB->GetNowFunction(),
						"IS_CALCULATED"		=> "N",
						"~CONFIG"			=> "'".serialize($COMPONENT_VALUE)."'",
					);

					$DB->Add("b_rating_component", $arFields_i);
				}
			}
		}

		return true;
	}

	public static function __UpdateComponents($ID, $arFields)
	{
		global $DB;

		$ID = (int)$ID;

		$DB->Query("DELETE FROM b_rating_component WHERE RATING_ID=$ID");

		CRatings::__AddComponents($ID, $arFields);

		return true;
	}

	public static function err_mess()
	{
		return "<br>Class: CRatings<br>File: ".__FILE__;
	}

	public static function getRatingVoteReaction($arParam)
	{
		global $DB;

		static $cache = array();

		$bplus = (mb_strtoupper($arParam['LIST_TYPE']) !== 'MINUS');
		$key = $arParam['ENTITY_TYPE_ID'].'_'.(int)$arParam['ENTITY_ID'].'_'.($bplus ? '1' : '0');

		if (
			isset($arParam['USE_REACTIONS_CACHE'])
			&& $arParam['USE_REACTIONS_CACHE'] === 'Y'
			&& isset($cache[$key])
		)
		{
			$result = $cache[$key];
		}
		else
		{
			$sqlStr = "
				SELECT
					REACTION,
					COUNT(RV.ID) as CNT
				FROM
					b_rating_vote RV
				WHERE
					RV.ENTITY_TYPE_ID = '".$DB->ForSql($arParam['ENTITY_TYPE_ID'])."'
					AND RV.ENTITY_ID = ".(int)$arParam['ENTITY_ID']." ".
//				($bplus ? " AND RV.VALUE > 0 ": " and RV.VALUE < 0 ")
				"GROUP BY REACTION";
			$res_cnt = $DB->Query($sqlStr);

			$cnt = 0;
			$cntReactions = array();
			while($ar_cnt = $res_cnt->fetch())
			{
				$key = (!empty($ar_cnt["REACTION"]) ? $ar_cnt["REACTION"] : self::REACTION_DEFAULT);
				if (!isset($cntReactions[$key]))
				{
					$cntReactions[$key] = 0;
				}
				$cntReactions[$key] += $ar_cnt["CNT"];
				$cnt += $ar_cnt["CNT"];
			}

			$result = $cache[$key] = array(
				'items_all' => $cnt,
				'reactions' => $cntReactions
			);
		}

		return $result;
	}

	public static function getRatingVoteList($arParam)
	{
		global $USER;

		$reactionResult = self::GetRatingVoteReaction($arParam);
		$cnt = $reactionResult['items_all'];
		$cntReactions = $reactionResult['reactions'];

		$bplus = (mb_strtoupper($arParam['LIST_TYPE']) !== 'MINUS');

		$bIntranetInstalled = IsModuleInstalled("intranet");

		$bExtended = false;
		$arUserID = array();

		if (
			(
				array_key_exists("USER_FIELDS", $arParam)
				&& is_array($arParam["USER_FIELDS"])
			)
			|| (
				array_key_exists("USER_SELECT", $arParam)
				&& is_array($arParam["USER_SELECT"])
			)
		)
		{
			$bExtended = true;
			$sqlStr = CRatings::GetRatingVoteListSQLExtended($arParam, $bplus, $bIntranetInstalled);
		}
		else
		{
			$sqlStr = CRatings::GetRatingVoteListSQL($arParam, $bplus, $bIntranetInstalled);
		}

		$arList = Array();
		$arVoteList = Array();
		if ($arParam['LIST_LIMIT'] != 0 && ceil($cnt/ (int)$arParam['LIST_LIMIT']) >= (int)$arParam['LIST_PAGE'])
		{
			$res = new CDBResult();
			$res->NavQuery($sqlStr, $cnt, Array('iNumPage' => (int)$arParam['LIST_PAGE'], 'nPageSize' => (int)$arParam['LIST_LIMIT']));

			while ($row = $res->Fetch())
			{
				$ar = $row;

				if (!$bExtended)
				{
					$ar["PHOTO"] = $ar["PHOTO_SRC"] = '';
					if (!empty($ar["PERSONAL_PHOTO"]))
					{
						$arFileTmp = CFile::ResizeImageGet(
							$row["PERSONAL_PHOTO"],
							array('width' => 58, 'height' => 58),
							BX_RESIZE_IMAGE_EXACT,
							false
						);
						$ar['PHOTO'] = CFile::ShowImage($arFileTmp['src'], 21, 21, 'border=0');
						$ar['PHOTO_SRC'] = $arFileTmp['src'];
					}
					$ar['FULL_NAME'] = CUser::FormatName(CSite::GetNameFormat(false), $row, true);
				}
				else
					$arUserID[] = $row["ID"];

				if ($ar['ID'] != $USER->GetId())
					$arList[$ar['ID']] = $ar;
				else
					$arVoteList[$ar['ID']] = $ar;
			}
			foreach ($arList as $ar)
			{
				$arVoteList[$ar['ID']] = $ar;
			}

			if (
				$bExtended
				&& !empty($arUserID)
			)
			{
				$arUserListParams = array();
				$arUsers = array();

				$arUserListParams["FIELDS"] = (
					array_key_exists("USER_FIELDS", $arParam)
					&& is_array($arParam["USER_FIELDS"])
						? $arParam["USER_FIELDS"]
						: array("NAME", "LAST_NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO")
				);

				$arUserListParams["FIELDS"] = array_unique(array_merge(array("ID"), $arUserListParams["FIELDS"]));

				if (
					array_key_exists("USER_SELECT", $arParam)
					&& is_array($arParam["USER_SELECT"])
				)
				{
					$arUserListParams["SELECT"] = $arParam["USER_SELECT"];
				}

				$rsUser = CUser::GetList(
					"ID",
					"ASC",
					array("ID" => implode("|", $arUserID)),
					$arUserListParams
				);

				while ($arUser = $rsUser->Fetch())
				{
					$arUser["PHOTO"] = $arUser["PHOTO_SRC"] = '';
					if (array_key_exists("PERSONAL_PHOTO", $arUser))
					{
						$arFileTmp = CFile::ResizeImageGet(
							$arUser["PERSONAL_PHOTO"],
							array("width" => 58, "height" => 58),
							BX_RESIZE_IMAGE_EXACT,
							false
						);
						$arUser["PHOTO_SRC"] = $arFileTmp["src"];
						$arUser["PHOTO"] = CFile::ShowImage($arFileTmp["src"], 21, 21, "border=0");
					}
					$arUser["FULL_NAME"] = CUser::FormatName(CSite::GetNameFormat(false), $arUser, true);
					$arUsers[$arUser["ID"]] = $arUser;
				}

				foreach($arVoteList as $i => $arVoteUser)
				{
					if (array_key_exists($arVoteUser["ID"], $arUsers))
					{
						foreach($arUsers[$arVoteUser["ID"]] as $key => $value)
						{
							$arVoteList[$i][$key] = $value;
						}
					}
				}
			}
		}

		return array(
			'items_all' => $cnt,
			'items_page' => count($arVoteList),
			'items' => $arVoteList,
			'reactions' => $cntReactions,
			'list_page' => isset($arParam['LIST_PAGE']) ? (int)$arParam['LIST_PAGE'] : 0,
		);
	}

	public static function getUserWeight($userId = 0)
	{
		$result = 0;

		if (!\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
		{
			return $result;
		}

		$userId = (
			!empty($userId)
				? (int)$userId
				: 0
		);

		if ($userId <= 0)
		{
			return $result;
		}

		$ratingId = \CRatings::getAuthorityRating();
		if ((int)$ratingId <= 0)
		{
			return $result;
		}

		$res = \Bitrix\Main\Application::getConnection()->query('SELECT MAX(VOTES) AS VOTES FROM b_rating_subordinate WHERE RATING_ID = '.$ratingId.' AND ENTITY_ID = '.$userId);
		if ($record = $res->fetch())
		{
			$result = (float)$record['VOTES'];
		}

		return $result;
	}

	public static function getUserData($userId = 0, $value = 0)
	{
		$result = array();

		$userId = (
			!empty($userId)
				? (int)$userId
				: 0
		);

		if ($userId <= 0)
		{
			return $result;
		}

		$res = \CUser::getById($userId);
		if ($userFields = $res->fetch())
		{
			$result = array(
				'NAME_FORMATTED' => \CUser::formatName(
					\CSite::getNameFormat(false),
					array(
						'NAME' => $userFields["NAME"],
						'LAST_NAME' => $userFields["LAST_NAME"],
						'SECOND_NAME' => $userFields["SECOND_NAME"],
						'LOGIN' => $userFields["LOGIN"],
					),
					true
				),
				'PERSONAL_PHOTO' => array(
					'ID' => $userFields["PERSONAL_PHOTO"],
					'SRC' => false
				),
				"WEIGHT" => (
					\Bitrix\Main\ModuleManager::isModuleInstalled('intranet')
						? self::getUserWeight($userId)
						: $value
				)
			);

			if ((int)$userFields['PERSONAL_PHOTO'] > 0)
			{
				$imageFile = \CFile::getFileArray($userFields["PERSONAL_PHOTO"]);
				if ($imageFile !== false)
				{
					$file = \CFile::resizeImageGet(
						$imageFile,
						array("width" => 100, "height" => 100),
						BX_RESIZE_IMAGE_EXACT,
						false
					);
					$result['PERSONAL_PHOTO']['SRC'] = $file['src'];
				}
			}
		}

		return $result;
	}

	public static function getEntityRatingData($params = array())
	{
		global $USER;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$result = array();

		$entityTypeId = (
			!empty($params['entityTypeId'])
				? $params['entityTypeId']
				: ''
		);

		$entityIdList = (
			!empty($params['entityId'])
				? $params['entityId']
				: array()
		);

		if (!is_array($entityIdList))
		{
			$entityIdList = array($entityIdList);
		}

		if (empty($entityIdList))
		{
			return $result;
		}

		$ratingId = \CRatings::getAuthorityRating();
		if ((int)$ratingId <= 0)
		{
			return $result;
		}

		$topCount = (
			isset($params['topCount'])
				? (int)$params['topCount']
				: 0
		);

		if ($topCount <= 0)
		{
			$topCount = 2;
		}

		if ($topCount > 5)
		{
			$topCount = 5;
		}

		$avatarSize = (
			isset($params['avatarSize'])
				? (int)$params['avatarSize']
				: 100
		);

		if (\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
		{
			$res = $connection->query("
				SELECT
					RS1.ENTITY_ID as USER_ID,
					RV1.ENTITY_ID as ENTITY_ID,
					MAX(RS1.VOTES) as WEIGHT
				FROM
					b_rating_subordinate RS1,
					b_rating_vote RV1
				WHERE
					RS1.ENTITY_ID = RV1.USER_ID
					AND RS1.RATING_ID = ".(int)$ratingId."
					AND RV1.ENTITY_TYPE_ID = '".$helper->forSQL($entityTypeId)."'
					AND RV1.ENTITY_ID IN (".implode(',', $entityIdList).")
				GROUP BY
					RV1.ENTITY_ID, RS1.ENTITY_ID
				ORDER BY
					RV1.ENTITY_ID,
					WEIGHT DESC
			");
		}
		else
		{
			$res = $connection->query("
				SELECT
					RV1.USER_ID as USER_ID,
					RV1.ENTITY_ID as ENTITY_ID,
					RV1.VALUE as WEIGHT
				FROM
					b_rating_vote RV1
				WHERE
					RV1.ENTITY_TYPE_ID = '".$helper->forSQL($entityTypeId)."'
					AND RV1.ENTITY_ID IN (".implode(',', $entityIdList).")
				ORDER BY
					RV1.ENTITY_ID,
					WEIGHT DESC
			");
		}

		$userWeightData = $entityUserData = array();

		$currentEntityId = false;
		$hasMine = false;

		while ($voteFields = $res->fetch())
		{
			if (
				!$hasMine
				&& $voteFields['USER_ID'] == $USER->getId()
			)
			{
				$hasMine = true;
			}

			if ($voteFields['ENTITY_ID'] != $currentEntityId)
			{
				$cnt = 0;
				$hasMine = false;
				$entityUserData[$voteFields['ENTITY_ID']] = array();
			}

			$currentEntityId = $voteFields['ENTITY_ID'];
			$cnt++;

			if ($cnt > ($hasMine ? $topCount+1 : $topCount))
			{
				continue;
			}

			$entityUserData[$voteFields['ENTITY_ID']][] = $voteFields['USER_ID'];
			if (!isset($userWeightData[$voteFields['USER_ID']]))
			{
				$userWeightData[$voteFields['USER_ID']] = (float)$voteFields['WEIGHT'];
			}
		}

		$userData = array();

		if (!empty($userWeightData))
		{
			$res = \Bitrix\Main\UserTable::getList(array(
				'filter' => array(
					'@ID' => array_keys($userWeightData)
				),
				'select' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'PERSONAL_PHOTO', 'PERSONAL_GENDER')
			));

			while ($userFields = $res->fetch())
			{
				$userData[$userFields["ID"]] = array(
					'NAME_FORMATTED' => \CUser::formatName(
						\CSite::getNameFormat(false),
						$userFields,
						true
					),
					'PERSONAL_PHOTO' => array(
						'ID' => $userFields['PERSONAL_PHOTO'],
						'SRC' => false
					),
					'PERSONAL_GENDER' => $userFields['PERSONAL_GENDER']
				);

				if ((int)$userFields['PERSONAL_PHOTO'] > 0)
				{
					$imageFile = \CFile::getFileArray($userFields["PERSONAL_PHOTO"]);
					if ($imageFile !== false)
					{
						$file = \CFile::resizeImageGet(
							$imageFile,
							array("width" => $avatarSize, "height" => $avatarSize),
							BX_RESIZE_IMAGE_EXACT,
							false
						);
						$userData[$userFields["ID"]]['PERSONAL_PHOTO']['SRC'] = $file['src'];
					}
				}
			}
		}

		foreach($entityUserData as $entityId => $userIdList)
		{
			$result[$entityId] = array();

			foreach($userIdList as $userId)
			{
				$result[$entityId][] = array(
					'ID' => $userId,
					'NAME_FORMATTED' => $userData[$userId]['NAME_FORMATTED'],
					'PERSONAL_PHOTO' => $userData[$userId]['PERSONAL_PHOTO']['ID'],
					'PERSONAL_PHOTO_SRC' => $userData[$userId]['PERSONAL_PHOTO']['SRC'],
					'PERSONAL_GENDER' => $userData[$userId]['PERSONAL_GENDER'],
					'WEIGHT' => $userWeightData[$userId]
				);
			}
		}

		foreach($result as $entityId => $data)
		{
			usort(
				$data,
				function($a, $b)
				{
					if ($a['WEIGHT'] == $b['WEIGHT'])
					{
						return 0;
					}
					return ($a['WEIGHT'] > $b['WEIGHT']) ? -1 : 1;
				}
			);
			$result[$entityId] = $data;
		}

		return $result;
	}

	public static function deleteRatingVoting(array $params = [])
	{
		global $DB;

		$entityTypeId = (
			isset($params['ENTITY_TYPE_ID'])
			&& $params['ENTITY_TYPE_ID'] <> ''
				? $params['ENTITY_TYPE_ID']
				: ''
		);
		$entityId = (
			isset($params['ENTITY_ID'])
			&& (int)$params['ENTITY_ID'] > 0
				? (int)$params['ENTITY_ID']
				: 0
		);
		if (
			$entityTypeId == ''
			|| $entityId <= 0
		)
		{
			return;
		}

		$DB->query("DELETE FROM b_rating_vote WHERE ENTITY_TYPE_ID='".$DB->forSql($entityTypeId)."' AND ENTITY_ID=".$entityId, true);
		$DB->query("DELETE FROM b_rating_voting WHERE ENTITY_TYPE_ID='".$DB->forSql($entityTypeId)."' AND ENTITY_ID=".$entityId, true);
		$DB->query("DELETE FROM b_rating_voting_reaction WHERE ENTITY_TYPE_ID='".$DB->forSql($entityTypeId)."' AND ENTITY_ID=".$entityId, true);
	}
}
