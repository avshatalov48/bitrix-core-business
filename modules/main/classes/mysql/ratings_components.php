<?php

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/ratings_components.php");

IncludeModuleLangFile(__FILE__);

class CRatingsComponentsMain extends CAllRatingsComponentsMain
{
	// Calc function
	public static function CalcVoteUser($arConfigs)
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		CRatings::AddComponentResults($arConfigs);

		$strSql = "DELETE FROM b_rating_component_results WHERE RATING_ID = '".intval($arConfigs['RATING_ID'])."' AND COMPLEX_NAME = '".$helper->forSql($arConfigs['COMPLEX_NAME'])."'";
		$res = $connection->query($strSql);

		$strSql = "INSERT INTO b_rating_component_results (RATING_ID, MODULE_ID, RATING_TYPE, NAME, COMPLEX_NAME, ENTITY_ID, ENTITY_TYPE_ID, CURRENT_VALUE)
					SELECT
						".intval($arConfigs['RATING_ID'])." as RATING_ID,
						'".$helper->forSql($arConfigs['MODULE_ID'])."' as MODULE_ID,
						'".$helper->forSql($arConfigs['RATING_TYPE'])."' as RATING_TYPE,
						'".$helper->forSql($arConfigs['NAME'])."' as RATING_NAME,
						'".$helper->forSql($arConfigs['COMPLEX_NAME'])."' as COMPLEX_NAME,
						RV.ENTITY_ID,
						'".$helper->forSql($arConfigs['ENTITY_ID'])."' as ENTITY_TYPE_ID,
						SUM(RVE.VALUE) * ".floatval($arConfigs['CONFIG']['COEFFICIENT'])." as CURRENT_VALUE
					FROM
						b_rating_voting RV,
						b_rating_vote RVE
					WHERE
						RV.ENTITY_TYPE_ID = 'USER' AND RV.ENTITY_ID > 0
					AND RVE.RATING_VOTING_ID = RV.ID".(intval($arConfigs['CONFIG']['LIMIT']) > 0 ? " AND RVE.CREATED > " . $helper->addDaysToDateTime(-intval($arConfigs['CONFIG']['LIMIT']))."" : "")."
					GROUP BY RV.ENTITY_ID";

		$res = $connection->query($strSql);

		return true;
	}

	public static function CalcUserBonus($arConfigs)
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$communityLastVisit = COption::GetOptionString("main", "rating_community_last_visit", '90');

		CRatings::AddComponentResults($arConfigs);

		$strSql = "DELETE FROM b_rating_component_results WHERE RATING_ID = '".intval($arConfigs['RATING_ID'])."' AND COMPLEX_NAME = '".$helper->forSql($arConfigs['COMPLEX_NAME'])."'";
		$res = $connection->query($strSql);

		$strSql = "INSERT INTO b_rating_component_results (RATING_ID, MODULE_ID, RATING_TYPE, NAME, COMPLEX_NAME, ENTITY_ID, ENTITY_TYPE_ID, CURRENT_VALUE)
					SELECT
						".intval($arConfigs['RATING_ID'])." as RATING_ID,
						'".$helper->forSql($arConfigs['MODULE_ID'])."' as MODULE_ID,
						'".$helper->forSql($arConfigs['RATING_TYPE'])."' as RATING_TYPE,
						'".$helper->forSql($arConfigs['NAME'])."' as RATING_NAME,
						'".$helper->forSql($arConfigs['COMPLEX_NAME'])."' as COMPLEX_NAME,
						RB.ENTITY_ID,
						'".$helper->forSql($arConfigs['ENTITY_ID'])."' as ENTITY_TYPE_ID,
						RB.BONUS*".floatval($arConfigs['CONFIG']['COEFFICIENT'])." as CURRENT_VALUE
					FROM
						b_rating_user RB
						LEFT JOIN b_user U ON U.ID = RB.ENTITY_ID AND U.ACTIVE = 'Y' AND U.LAST_LOGIN > " . $helper->addDaysToDateTime(-intval($communityLastVisit)) . "
					WHERE
						RB.RATING_ID = ".intval($arConfigs['RATING_ID'])."
						AND U.ID IS NOT NULL
					";
		$res = $connection->Query($strSql);

		return true;
	}
}
