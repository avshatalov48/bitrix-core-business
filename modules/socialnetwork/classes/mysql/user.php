<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/user.php");

class CSocNetUser extends CAllSocNetUser
{
	public static function SearchUsers($searchString, $groupId = 0, $numberOfUsers = 10)
	{
		global $DB;

		$searchString = Trim($searchString);

		$groupId = intval($groupId);
		$numberOfUsers = intval($numberOfUsers);
		if ($numberOfUsers <= 0)
			$numberOfUsers = 10;

		$searchPattern = "'".$DB->ForSql($searchString)."%'";

		$strSqlFrom = "";
		$strSqlWhere = "";
		if ($groupId > 0)
		{
			$strSqlFrom = " INNER JOIN b_sonet_user2group UG ON (U.ID = UG.USER_ID AND UG.ROLE <= '".$DB->ForSql(SONET_ROLES_USER)."') ";
			$strSqlWhere = " AND UG.GROUP_ID = ".$groupId." ";
		}

		$strSql =
			"SELECT U.ID, U.LOGIN, U.EMAIL, U.NAME, U.SECOND_NAME, U.LAST_NAME ".
			"FROM b_user U ".$strSqlFrom." ".
			"WHERE (upper(U.NAME) LIKE upper(".$searchPattern.") ".
			"	OR upper(U.LAST_NAME) LIKE upper(".$searchPattern.") ".
			"	OR upper(U.SECOND_NAME) LIKE upper(".$searchPattern.") ".
			"	OR upper(U.EMAIL) LIKE upper(".$searchPattern.") ".
			"	OR upper(U.LOGIN) LIKE upper(".$searchPattern.")) AND ACTIVE = 'Y' ".$strSqlWhere." ".
			"ORDER BY U.LAST_NAME ASC, U.NAME ASC, U.SECOND_NAME ASC ".
			"LIMIT 0, ".$numberOfUsers."";

		return $DB->Query($strSql);
	}
}
