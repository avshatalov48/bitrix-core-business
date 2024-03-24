<?php

IncludeModuleLangFile(__FILE__);

class CRatings extends CAllRatings
{
	public static function err_mess()
	{
		return "<br>Class: CRatings<br>File: ".__FILE__;
	}

	// building rating on computed components
	public static function BuildRating($ID)
	{
		global $DB;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$ID = intval($ID);

		$resRating = CRatings::GetByID($ID);
		$arRating = $resRating->Fetch();
		if ($arRating && $arRating['ACTIVE'] == 'Y')
		{
			$DB->Query("UPDATE b_rating SET CALCULATED = 'C' WHERE id = ".$ID);

			// Insert new results
			$sqlFunc = ($arRating['CALCULATION_METHOD'] == 'SUM') ? 'SUM' : 'AVG';
			$strSql  = "
				INSERT INTO b_rating_results
					(RATING_ID, ENTITY_TYPE_ID, ENTITY_ID, CURRENT_VALUE, PREVIOUS_VALUE)
				SELECT
					".$ID." RATING_ID,
					'".$arRating['ENTITY_ID']."' ENTITY_TYPE_ID,
					RC.ENTITY_ID,
					".$sqlFunc."(RC.CURRENT_VALUE) CURRENT_VALUE,
					0 PREVIOUS_VALUE
				FROM
					b_rating_component_results RC LEFT JOIN b_rating_results RR ON RR.RATING_ID = RC.RATING_ID and RR.ENTITY_ID = RC.ENTITY_ID
				WHERE
					RC.RATING_ID = ".$ID." and RR.ID IS NULL
				GROUP BY RC.ENTITY_ID";
			$res = $DB->Query($strSql);

			// Update current results
			$strSql = $helper->prepareCorrelatedUpdate("b_rating_results", "RR", [
					'PREVIOUS_VALUE' => 'case when RR.CURRENT_VALUE = RCR.CURRENT_VALUE then RR.PREVIOUS_VALUE else RR.CURRENT_VALUE end',
					'CURRENT_VALUE' => 'RCR.CURRENT_VALUE',
				], "
					(SELECT '".$arRating['ENTITY_ID']."' ENTITY_TYPE_ID,	RC.ENTITY_ID, ".$sqlFunc."(RC.CURRENT_VALUE) CURRENT_VALUE
					FROM b_rating_component_results RC INNER JOIN b_rating_results RR on RR.RATING_ID = RC.RATING_ID and RR.ENTITY_ID = RC.ENTITY_ID
					WHERE RC.RATING_ID = ".$ID."
					GROUP BY RC.ENTITY_ID
					) as RCR
				", "
					RR.RATING_ID=".$ID."
					and	RR.ENTITY_TYPE_ID = RCR.ENTITY_TYPE_ID
					and	RR.ENTITY_ID = RCR.ENTITY_ID
				"
			);
			$res = $DB->Query($strSql);

			// Calculation position in rating
			if ($arRating['POSITION'] == 'Y')
			{
				$strSql = $helper->initRowNumber('nPos');
				if ($strSql)
				{
					$DB->Query($strSql);
				}
				$strSql = $helper->prepareCorrelatedUpdate("b_rating_results", "RR", [
						'PREVIOUS_POSITION' => 'case when RR.CURRENT_POSITION = RP.POSITION then RR.PREVIOUS_POSITION else RR.CURRENT_POSITION end',
						'CURRENT_POSITION' => 'RP.POSITION',
					], "
						(SELECT ENTITY_TYPE_ID, ENTITY_ID, CURRENT_VALUE, " . $helper->getRowNumber('nPos') . " as POSITION
						FROM b_rating_results
						WHERE RATING_ID = ".$ID."
						ORDER BY CURRENT_VALUE DESC
					) as RP
					", "
						RR.RATING_ID=".$ID."
						and	RR.ENTITY_TYPE_ID = RP.ENTITY_TYPE_ID
						and	RR.ENTITY_ID = RP.ENTITY_ID
					"
				);
				$res = $DB->Query($strSql);
			}

			// Insert new user rating prop
			$strSql  = "
				INSERT INTO b_rating_user
					(RATING_ID, ENTITY_ID)
				SELECT
					".$ID." RATING_ID,
					U.ID as ENTITY_ID
				FROM
					b_user U 
				LEFT JOIN b_rating_user RU ON RU.RATING_ID = ".$ID." and RU.ENTITY_ID = U.ID
				WHERE 
					U.ACTIVE = 'Y' 
					AND (CASE WHEN U.EXTERNAL_AUTH_ID IN ('".join("', '", \Bitrix\Main\UserTable::getExternalUserTypes())."') THEN 'Y' ELSE 'N' END) = 'N'
					AND RU.ID IS NULL	";
			$res = $DB->Query($strSql);
			// authority calc
			if ($arRating['AUTHORITY'] == 'Y')
			{
				$sRatingAssignType = COption::GetOptionString("main", "rating_assign_type", "manual");
				if ($sRatingAssignType == 'auto')
				{
					// auto assign for rating group
					$assignRatingGroup = COption::GetOptionString("main", "rating_assign_rating_group", 0);
					$assignRatingValueAdd = COption::GetOptionString("main", "rating_assign_rating_group_add", 1);
					$assignRatingValueDelete = COption::GetOptionString("main", "rating_assign_rating_group_delete", 1);

					CRatings::AutoAssignGroup($assignRatingGroup, $assignRatingValueAdd, $assignRatingValueDelete);

					// auto assign for authority group
					$assignAuthorityGroup = COption::GetOptionString("main", "rating_assign_authority_group", 0);
					$assignAuthorityValueAdd = COption::GetOptionString("main", "rating_assign_authority_group_add", 2);
					$assignAuthorityValueDelete = COption::GetOptionString("main", "rating_assign_authority_group_delete", 2);

					CRatings::AutoAssignGroup($assignAuthorityGroup, $assignAuthorityValueAdd, $assignAuthorityValueDelete);
				}

				$sRatingWeightType = COption::GetOptionString("main", "rating_weight_type", "auto");
				if ($sRatingWeightType == 'auto')
				{
					$arCI = CRatings::GetCommunityInfo($ID);
					$communitySize = $arCI['COMMUNITY_SIZE'];
					$communityAuthority = $arCI['COMMUNITY_AUTHORITY'];

					$sRatingNormalizationType = COption::GetOptionString("main", "rating_normalization_type", "auto");
					if ($sRatingNormalizationType == 'manual')
					{
						$ratingNormalization = COption::GetOptionString("main", "rating_normalization", 1000);
					}
					else
					{
						if ($communitySize <= 10)
						{
							$ratingNormalization = 10;
						}
						elseif ($communitySize <= 1000)
						{
							$ratingNormalization = 100;
						}
						else
						{
							$ratingNormalization = 1000;
						}
						COption::SetOptionString("main", "rating_normalization", $ratingNormalization);
					}

					$voteWeight = 1;
					if ($communitySize > 0)
						$voteWeight = $ratingNormalization/$communitySize;

					COption::SetOptionString("main", "rating_community_size", $communitySize);
					COption::SetOptionString("main", "rating_community_authority", $communityAuthority);
					COption::SetOptionString("main", "rating_vote_weight", $voteWeight);

					$ratingCountVote = COption::GetOptionString("main", "rating_count_vote", 10);
					$strSql =  "UPDATE b_rating_user SET VOTE_COUNT = 0, VOTE_WEIGHT =0 WHERE RATING_ID=".$ID;
					$res = $DB->Query($strSql);
					// default vote count + user authority
					$strSql = $helper->prepareCorrelatedUpdate("b_rating_user", "RU", [
							'VOTE_COUNT' => intval($ratingCountVote)." + RP.CURRENT_VALUE",
							'VOTE_WEIGHT' => "RP.CURRENT_VALUE * " . $voteWeight,
						], "
							(SELECT ENTITY_ID, CURRENT_VALUE
							FROM b_rating_results
							WHERE RATING_ID = ".$ID."
							) as RP
						", "
							RU.RATING_ID=".$ID."
							and	RU.ENTITY_ID = RP.ENTITY_ID
						"
					);
					$res = $DB->Query($strSql);
				}
				else
				{
					// Depending on current authority set correct weight votes
					// Depending on current authority set correct vote count
					$strSql =  "UPDATE b_rating_user SET VOTE_COUNT = 0, VOTE_WEIGHT =0 WHERE RATING_ID=".$ID;
					$res = $DB->Query($strSql);
					$strSql = $helper->prepareCorrelatedUpdate("b_rating_user", "RU", [
							'VOTE_COUNT' => 'RP.COUNT',
							'VOTE_WEIGHT' => 'RP.WEIGHT',
						], "
							(SELECT
							RW.RATING_FROM, RW.RATING_TO, RW.WEIGHT, RW.COUNT, RR.ENTITY_ID
							FROM
								b_rating_weight RW,
								b_rating_results RR
							WHERE
								RR.RATING_ID = ".$ID."
								and RR.CURRENT_VALUE BETWEEN RW.RATING_FROM AND RW.RATING_TO
							) as RP
						", "
							RU.RATING_ID=".$ID."
							and RU.ENTITY_ID = RP.ENTITY_ID
						"
					);
					$res = $DB->Query($strSql);
				}
			}
			global $CACHE_MANAGER;
			$CACHE_MANAGER->CleanDir("b_rating_user");

			$DB->Query("UPDATE b_rating SET CALCULATED = 'Y', LAST_CALCULATED = ".$DB->GetNowFunction()." WHERE id = ".$ID);
		}
		return true;
	}

	public static function DeleteByUser($ID)
	{
		global $CACHE_MANAGER;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$ID = intval($ID);

		$strSql = $helper->prepareCorrelatedUpdate("b_rating_voting_reaction", "RVR", [
				'TOTAL_VOTES' => 'RP.TOTAL_POSITIVE_VOTES',
			], "
				(SELECT
					ENTITY_TYPE_ID,
					ENTITY_ID,
					SUM(case when VALUE > 0 AND USER_ID <> $ID then 1 else 0 end) as TOTAL_POSITIVE_VOTES
				FROM b_rating_vote
				WHERE RATING_VOTING_ID IN (
					SELECT DISTINCT RV0.RATING_VOTING_ID FROM b_rating_vote RV0 WHERE RV0.USER_ID=$ID
				)
				GROUP BY RATING_VOTING_ID, ENTITY_TYPE_ID, ENTITY_ID
				) as RP
			", "
				RVR.ENTITY_TYPE_ID = RP.ENTITY_TYPE_ID
				AND RVR.ENTITY_ID = RP.ENTITY_ID
			"
		);
		$connection->query($strSql);

		$strSql = $helper->prepareCorrelatedUpdate("b_rating_voting", "RV", [
				'TOTAL_VALUE' => 'RP.TOTAL_VALUE',
				'TOTAL_VOTES' => 'RP.TOTAL_VOTES',
				'TOTAL_POSITIVE_VOTES' => 'RP.TOTAL_POSITIVE_VOTES',
				'TOTAL_NEGATIVE_VOTES' => 'RP.TOTAL_NEGATIVE_VOTES',
			], "
				(SELECT
					RATING_VOTING_ID,
					SUM(case when USER_ID <> $ID then VALUE else 0 end) as TOTAL_VALUE,
					SUM(case when USER_ID <> $ID then 1 else 0 end) as TOTAL_VOTES,
					SUM(case when VALUE > 0 AND USER_ID <> $ID then 1 else 0 end) as TOTAL_POSITIVE_VOTES,
					SUM(case when VALUE < 0 AND USER_ID <> $ID then 1 else 0 end) as TOTAL_NEGATIVE_VOTES
				FROM b_rating_vote
				WHERE RATING_VOTING_ID IN (
					SELECT DISTINCT RV0.RATING_VOTING_ID FROM b_rating_vote RV0 WHERE RV0.USER_ID=$ID
				)
				GROUP BY RATING_VOTING_ID
				) as RP
			", "
				RV.ID = RP.RATING_VOTING_ID
			"
		);

		$connection->query($strSql);

		$connection->query("DELETE FROM b_rating_vote WHERE USER_ID = $ID");
		$connection->query("DELETE FROM b_rating_user WHERE ENTITY_ID = $ID");
		$CACHE_MANAGER->ClearByTag('RV_CACHE');

		return true;
	}

	// insert result calculate rating
	public static function AddResults($arResults)
	{
		global $DB;

		// Only Mysql
		$strSqlPrefix = "
				INSERT INTO b_rating_results
				(RATING_ID, ENTITY_TYPE_ID, ENTITY_ID, CURRENT_VALUE, PREVIOUS_VALUE)
				VALUES
		";
		$maxValuesLen = 2048;
		$strSqlValues = "";

		foreach($arResults as $arResult)
		{
			$strSqlValues .= ",\n(".intval($arResult['RATING_ID']).", '".$DB->ForSql($arResult['ENTITY_TYPE_ID'])."', '".$DB->ForSql($arResult['ENTITY_ID'])."', '".$DB->ForSql($arResult['CURRENT_VALUE'])."', '".$DB->ForSql($arResult['PREVIOUS_VALUE'])."')";
			if(mb_strlen($strSqlValues) > $maxValuesLen)
			{
				$DB->Query($strSqlPrefix.mb_substr($strSqlValues, 2));
				$strSqlValues = "";
			}
		}
		if($strSqlValues <> '')
		{
			$DB->Query($strSqlPrefix.mb_substr($strSqlValues, 2));
		}

		return true;
	}

	// insert result calculate rating-components
	public static function AddComponentResults($arComponentConfigs)
	{
		global $DB;

		if (!is_array($arComponentConfigs))
			return false;

		$strSql  = "
			UPDATE b_rating_component
			SET LAST_CALCULATED = ".$DB->GetNowFunction().",
				NEXT_CALCULATION = '".date('Y-m-d H:i:s', time()+$arComponentConfigs['REFRESH_INTERVAL'])."'
			WHERE RATING_ID = ".intval($arComponentConfigs['RATING_ID'])." AND COMPLEX_NAME = '".$DB->ForSql($arComponentConfigs['COMPLEX_NAME'])."'";
		$DB->Query($strSql);

		return true;
	}

	public static function SetAuthorityRating($ratingId)
	{
		global $DB, $stackCacheManager;

		$ratingId = intval($ratingId);

		$DB->Query("UPDATE b_rating SET AUTHORITY = CASE WHEN ID <> $ratingId THEN 'N' ELSE 'Y' END");

		COption::SetOptionString("main", "rating_authority_rating", $ratingId);

		$stackCacheManager->Clear("b_rating");

		return true;
	}

	public static function GetCommunityInfo($ratingId)
	{
		global $DB;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$bAllGroups = false;
		$arInfo = Array();
		$arGroups = Array();
		$communityLastVisit = COption::GetOptionString("main", "rating_community_last_visit", '90');
		$res = CRatings::GetVoteGroup();
		while ($arVoteGroup = $res->Fetch())
		{
			if ($arVoteGroup['GROUP_ID'] == 2)
			{
				$bAllGroups = true;
				break;
			}
			$arGroups[] = $arVoteGroup['GROUP_ID'];
		}

		$strModulesSql = '';
		if (IsModuleInstalled("forum"))
		{
			$strModulesSql .= "
					SELECT USER_START_ID as ENTITY_ID
					FROM b_forum_topic
					WHERE START_DATE > " . $helper->addDaysToDateTime(-intval($communityLastVisit)) . "
					GROUP BY USER_START_ID
				UNION ALL
					SELECT AUTHOR_ID as ENTITY_ID
					FROM b_forum_message
					WHERE POST_DATE > " . $helper->addDaysToDateTime(-intval($communityLastVisit)) . "
					GROUP BY AUTHOR_ID
				UNION ALL
			";
		}
		if (IsModuleInstalled("blog"))
		{
			$strModulesSql .= "
					SELECT	AUTHOR_ID as ENTITY_ID
					FROM b_blog_post
					WHERE DATE_PUBLISH > " . $helper->addDaysToDateTime(-intval($communityLastVisit)) . "
					GROUP BY AUTHOR_ID
				UNION ALL
					SELECT AUTHOR_ID as ENTITY_ID
					FROM b_blog_comment
					WHERE DATE_CREATE > " . $helper->addDaysToDateTime(-intval($communityLastVisit)) . "
					GROUP BY AUTHOR_ID
				UNION ALL";
		}
		if (IsModuleInstalled("intranet"))
		{
			$ratingId = COption::GetOptionString("main", "rating_authority_rating", 0);
			$strModulesSql .= "
					SELECT ENTITY_ID
					FROM b_rating_subordinate
					WHERE RATING_ID = $ratingId
				UNION ALL";
		}
		if (!empty($strModulesSql))
		{
			$strModulesSql = "
				(
					".$strModulesSql."
					SELECT USER_ID as ENTITY_ID
					FROM b_rating_vote
					WHERE CREATED > " . $helper->addDaysToDateTime(-intval($communityLastVisit)) . "
					GROUP BY USER_ID
				) MS,
			";
		}

//		$DB->Query("TRUNCATE TABLE b_rating_prepare");
		$DB->Query("DELETE FROM b_rating_prepare");

		$strSql = '';
		if ($bAllGroups || empty($arGroups))
		{
			$strSql .= "
				INSERT INTO b_rating_prepare (ID)
				SELECT DISTINCT U.ID
				FROM ".$strModulesSql."
					b_user U
				WHERE ".(!empty($strModulesSql)? "U.ID = MS.ENTITY_ID AND": "")."
				U.ACTIVE = 'Y'
				AND (CASE WHEN U.EXTERNAL_AUTH_ID IN ('".join("', '", \Bitrix\Main\UserTable::getExternalUserTypes())."') THEN 'Y' ELSE 'N' END) = 'N'	
				AND U.LAST_LOGIN >" . $helper->addDaysToDateTime(-intval($communityLastVisit)) . "
			";
		}
		else
		{
			$strSql .= "
				INSERT INTO b_rating_prepare (ID)
				SELECT DISTINCT U.ID
				FROM ".$strModulesSql."
					b_user U
				WHERE ".(!empty($strModulesSql)? "U.ID = MS.ENTITY_ID AND": "")."
				U.ACTIVE = 'Y'
				AND (CASE WHEN U.EXTERNAL_AUTH_ID IN ('".join("', '", \Bitrix\Main\UserTable::getExternalUserTypes())."') THEN 'Y' ELSE 'N' END) = 'N'	
				AND U.LAST_LOGIN > " . $helper->addDaysToDateTime(-intval($communityLastVisit)) . "
			";
		}
		$DB->Query($strSql);

		$strSql = 'SELECT COUNT(*) as COMMUNITY_SIZE, SUM(CURRENT_VALUE) COMMUNITY_AUTHORITY
						FROM b_rating_results RC LEFT JOIN b_rating_prepare TT ON RC.ENTITY_ID = TT.ID
						WHERE RATING_ID = '.intval($ratingId).' AND TT.ID IS NOT NULL';
		$res = $DB->Query($strSql);

		return $res->Fetch();
	}

	public static function CheckAllowVote($arVoteParam)
	{
		global $USER;

		if (
			isset($arVoteParam['CURRENT_USER_ID'])
			&& (int)$arVoteParam['CURRENT_USER_ID'] > 0
		)
		{
			$userId = (int)$arVoteParam['CURRENT_USER_ID'];
			$bUserAuth = ($userId > 0);
		}
		else
		{
			$userId = (int)$USER->GetId();
			$bUserAuth = $USER->IsAuthorized();
		}

		$arInfo = array(
			'RESULT' => true,
			'ERROR_TYPE' => '',
			'ERROR_MSG' => '',
		);

		$bSelfVote = COption::GetOptionString("main", "rating_self_vote", 'N');
		if ($bSelfVote == 'N' && intval($arVoteParam['OWNER_ID']) == $userId)
		{
			$arInfo = array(
				'RESULT' => false,
				'ERROR_TYPE' => 'SELF',
				'ERROR_MSG' => GetMessage('RATING_ALLOW_VOTE_SELF'),
			);
		}
		else if (!$bUserAuth)
		{
			$arInfo = array(
				'RESULT' => false,
				'ERROR_TYPE' => 'GUEST',
				'ERROR_MSG' => GetMessage('RATING_ALLOW_VOTE_GUEST'),
			);
		}
		else
		{
			static $cacheAllowVote = array();
			static $cacheUserVote = array();
			static $cacheVoteSize = 0;
			if(!array_key_exists($userId, $cacheAllowVote))
			{
				global $DB;
				$connection = \Bitrix\Main\Application::getConnection();
				$helper = $connection->getSqlHelper();

				$sVoteType = $arVoteParam['ENTITY_TYPE_ID'] == 'USER'? 'A': 'R';

				$userVoteGroup = Array();
				$ar = CRatings::GetVoteGroupEx();
				foreach($ar as $group)
					if ($sVoteType == $group['TYPE'])
						$userVoteGroup[] = $group['GROUP_ID'];

				$userGroup = $USER->GetUserGroupArray();

				$result = array_intersect($userGroup, $userVoteGroup);
				if (empty($result))
				{
					$arInfo = $cacheAllowVote[$userId] = array(
						'RESULT' => false,
						'ERROR_TYPE' => 'ACCESS',
						'ERROR_MSG' => GetMessage('RATING_ALLOW_VOTE_ACCESS'),
					);
				}

				$authorityRatingId	 = CRatings::GetAuthorityRating();
				$arAuthorityUserProp = CRatings::GetRatingUserPropEx($authorityRatingId, $userId);
				if (
					$arAuthorityUserProp['VOTE_WEIGHT'] < 0
					|| (
						$arAuthorityUserProp['VOTE_WEIGHT'] == 0
						&& !IsModuleInstalled('intranet')
					)
				)
				{
					$arInfo = $cacheAllowVote[$userId] = array(
						'RESULT' => false,
						'ERROR_TYPE' => 'ACCESS',
						'ERROR_MSG' => GetMessage('RATING_ALLOW_VOTE_LOW_WEIGHT'),
					);
				}

				if ($arInfo['RESULT'] && $sVoteType == 'A')
				{
					$strSql = '
						SELECT COUNT(*) as VOTE
						FROM b_rating_vote RV
						WHERE RV.USER_ID = '.$userId.'
						AND RV.CREATED > ' . $helper->addDaysToDateTime(-1);
					$res = $DB->Query($strSql);
					$countVote = $res->Fetch();
					$cacheVoteSize = \Bitrix\Main\Application::getInstance()->getSession()['RATING_VOTE_COUNT'] = $countVote['VOTE'];

					$cacheUserVote[$userId] = \Bitrix\Main\Application::getInstance()->getSession()['RATING_USER_VOTE_COUNT'] = $arAuthorityUserProp['VOTE_COUNT'];
					if ($cacheVoteSize >= $cacheUserVote[$userId])
					{
						$arInfo = $cacheAllowVote[$userId] = array(
							'RESULT' => false,
							'ERROR_TYPE' => 'COUNT_VOTE',
							'ERROR_MSG' => GetMessage('RATING_ALLOW_VOTE_COUNT_VOTE'),
						);
					}
				}
			}
			else
			{
				if ($cacheAllowVote[$userId]['RESULT'])
				{
					if ($cacheVoteSize >= $cacheUserVote[$userId])
					{
						$arInfo = $cacheAllowVote[$userId] = array(
							'RESULT' => false,
							'ERROR_TYPE' => 'COUNT_VOTE',
							'ERROR_MSG' => GetMessage('RATING_ALLOW_VOTE_COUNT_VOTE'),
						);
					}
				}
				$arInfo = $cacheAllowVote[$userId];
			}
		}

		static $handlers;
		if (!isset($handlers))
			$handlers = GetModuleEvents("main", "OnAfterCheckAllowVote", true);

		foreach ($handlers as $arEvent)
		{
			$arEventResult = ExecuteModuleEventEx($arEvent, array($arVoteParam));
			if (is_array($arEventResult) && isset($arEventResult['RESULT']) && $arEventResult['RESULT'] === false
				&& isset($arEventResult['ERROR_TYPE']) && $arEventResult['ERROR_TYPE'] <> ''
				&& isset($arEventResult['ERROR_MSG']) && $arEventResult['ERROR_MSG'] <> '')
			{
				$arInfo = array(
					'RESULT' => false,
					'ERROR_TYPE' => $arEventResult['ERROR_TYPE'],
					'ERROR_MSG' => $arEventResult['ERROR_MSG'],
				);
			}
		}
		return $arInfo;
	}

	public static function SetAuthorityDefaultValue($arParams)
	{
		global $DB;

		$rsRatings = CRatings::GetList(array('ID' => 'ASC'), array('ENTITY_ID' => 'USER'));
		while ($arRatingsTmp = $rsRatings->GetNext())
			$arRatingList[] = $arRatingsTmp['ID'];

		if (isset($arParams['DEFAULT_USER_ACTIVE']) && $arParams['DEFAULT_USER_ACTIVE'] == 'Y' && IsModuleInstalled("forum") && is_array($arRatingList) && !empty($arRatingList))
		{
			$ratingStartValue = 0;
			if (isset($arParams['DEFAULT_CONFIG_NEW_USER']) && $arParams['DEFAULT_CONFIG_NEW_USER'] == 'Y')
				$ratingStartValue = COption::GetOptionString("main", "rating_start_authority", 3);

			$strSql =  "UPDATE b_rating_user SET BONUS = $ratingStartValue WHERE RATING_ID IN (".implode(',', $arRatingList).")";
			$res = $DB->Query($strSql);
			$strSql =  "
				UPDATE
					b_rating_user RU,
					(	SELECT
							TO_USER_ID as ENTITY_ID, COUNT(*) as CNT
						FROM
							b_forum_user_points FUP
						GROUP BY TO_USER_ID
					) as RP
				SET
					RU.BONUS = ".$DB->IsNull('RP.CNT', '0')."+".$ratingStartValue."
				WHERE
					RU.RATING_ID IN (".implode(',', $arRatingList).")
				and	RU.ENTITY_ID = RP.ENTITY_ID
			";
			$res = $DB->Query($strSql);
		}
		else if (isset($arParams['DEFAULT_CONFIG_NEW_USER']) && $arParams['DEFAULT_CONFIG_NEW_USER'] == 'Y' && is_array($arRatingList) && !empty($arRatingList))
		{
			$ratingStartValue = COption::GetOptionString("main", "rating_start_authority", 3);
			$strSql =  "UPDATE b_rating_user SET BONUS = ".$ratingStartValue." WHERE RATING_ID IN (".implode(',', $arRatingList).")";
			$res = $DB->Query($strSql);
		}

		return true;
	}

	public static function AutoAssignGroup($groupId, $authorityValueAdd, $authorityValueDelete)
	{
		global $DB;

		$groupId = intval($groupId);
		if ($groupId == 0)
			return false;

		$ratingId = CRatings::GetAuthorityRating();
		$ratingValueAdd = intval($authorityValueAdd);
		$ratingValueDelete = intval($authorityValueDelete);
		$sRatingWeightType = COption::GetOptionString("main", "rating_weight_type", "auto");
		if ($sRatingWeightType == 'auto')
		{
			$ratingValueAdd = $ratingValueAdd*COption::GetOptionString("main", "rating_vote_weight", 1);
			$ratingValueDelete = $ratingValueDelete*COption::GetOptionString("main", "rating_vote_weight", 1);
		}
		// remove the group from all users who it is, but you need to remove it
		$strSql = "
			DELETE
			FROM b_user_group
			WHERE (USER_ID, GROUP_ID) in (
				SELECT
					rr.ENTITY_ID as USER_ID
					, $groupId as GROUP_ID
				FROM
					b_rating_results rr
				WHERE
					rr.RATING_ID = $ratingId
				AND rr.CURRENT_VALUE < $ratingValueDelete
			)
		";
		$DB->Query($strSql);

		// add a group to all users who do not, but you need to add it
		$strSql = "
			INSERT INTO b_user_group (USER_ID, GROUP_ID)
			SELECT
				rr.ENTITY_ID, '$groupId'
			FROM
				b_rating_results rr
				LEFT JOIN b_user_group ug ON ug.GROUP_ID = $groupId AND ug.USER_ID = rr.ENTITY_ID
			WHERE
				rr.RATING_ID = $ratingId
			and rr.CURRENT_VALUE >= $ratingValueAdd
			and ug.USER_ID IS NULL";
		$DB->Query($strSql);

		return true;
	}

	public static function GetRatingVoteListSQL($arParam, $bplus, $bIntranetInstalled)
	{
		global $DB, $USER;

		$externalAuthTypes = array_diff(\Bitrix\Main\UserTable::getExternalUserTypes(), array('email', 'replica'));

		return "
			SELECT
				U.ID,
				U.NAME,
				U.LAST_NAME,
				U.SECOND_NAME,
				U.LOGIN,
				U.PERSONAL_PHOTO,
				RV.VALUE AS VOTE_VALUE,
				RV.USER_ID,
				SUM(case when RV0.ID is not null then 1 else 0 end) ".$DB->quote("RANK").",
				MIN(RV.ID) RV_ID
			FROM
				b_rating_vote RV LEFT JOIN b_rating_vote RV0 ON RV0.USER_ID = ".intval($USER->GetId())." and RV0.OWNER_ID = RV.USER_ID
				INNER JOIN b_user U ON RV.USER_ID = U.ID
			WHERE
				(CASE WHEN U.EXTERNAL_AUTH_ID IN ('".join("', '", $externalAuthTypes)."') THEN 'Y' ELSE 'N' END) = 'N'
				AND RV.ENTITY_TYPE_ID = '".$DB->ForSql($arParam['ENTITY_TYPE_ID'])."'
				and RV.ENTITY_ID =  ".intval($arParam['ENTITY_ID'])."
				".self::getReactionFilterSQL($arParam, $bplus)."
			GROUP BY U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.PERSONAL_PHOTO, RV.VALUE, RV.USER_ID
			ORDER BY ".($bIntranetInstalled? "RV.VALUE DESC, ".$DB->quote("RANK")." DESC, RV_ID DESC" : $DB->quote("RANK")." DESC, RV.VALUE DESC, RV_ID DESC");
	}

	public static function GetRatingVoteListSQLExtended($arParam, $bplus, $bIntranetInstalled)
	{
		global $DB, $USER;

		$externalAuthTypes = array_diff(\Bitrix\Main\UserTable::getExternalUserTypes(), array('email', 'replica'));

		return "
			SELECT
				U.ID,
				RV.VALUE AS VOTE_VALUE,
				RV.USER_ID,
				SUM(case when RV0.ID is not null then 1 else 0 end) ".$DB->quote("RANK").",
				MIN(RV.ID) RV_ID
			FROM
				b_rating_vote RV
				LEFT JOIN b_rating_vote RV0 ON RV0.USER_ID = ".intval($USER->GetId())." and RV0.OWNER_ID = RV.USER_ID
				INNER JOIN b_user U ON RV.USER_ID = U.ID
			WHERE
				(CASE WHEN U.EXTERNAL_AUTH_ID IN ('".join("', '", $externalAuthTypes)."') THEN 'Y' ELSE 'N' END) = 'N'
				AND RV.ENTITY_TYPE_ID = '".$DB->ForSql($arParam['ENTITY_TYPE_ID'])."'
				and RV.ENTITY_ID =  ".intval($arParam['ENTITY_ID'])."
				".self::getReactionFilterSQL($arParam, $bplus)."
			GROUP BY U.ID, RV.VALUE, RV.USER_ID
			ORDER BY ".($bIntranetInstalled? "RV.VALUE DESC, ".$DB->quote("RANK")." DESC, RV_ID DESC" : $DB->quote("RANK")." DESC, RV.VALUE DESC, RV_ID DESC");
	}

	private static function getReactionFilterSQL($arParam, $bplus)
	{
		global $DB;

		$result = (
			$bplus
			&& !empty($arParam["REACTION"])
				? (
					$arParam["REACTION"] == self::REACTION_DEFAULT
						? " and (RV.REACTION IS NULL OR RV.REACTION = '".$DB->ForSql($arParam["REACTION"])."') "
						: " and RV.REACTION = '".$DB->ForSql($arParam["REACTION"])."' "
				)
			: ""
		);

		return $result;
	}
}
