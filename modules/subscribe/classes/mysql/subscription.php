<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/classes/general/subscription.php");

class CSubscription extends CSubscriptionGeneral
{
	//get by e-mail
	public static function GetByEmail($email, $user_id = false)
	{
		global $DB;

		if($user_id === false)
			$sWhere = "";
		elseif($user_id > 0)
			$sWhere = "AND S.USER_ID = ".intval($user_id);
		else
			$sWhere = "AND S.USER_ID IS NULL";

		$strSql = "
			SELECT S.*,
				".$DB->DateToCharFunction("S.DATE_UPDATE", "FULL")." AS DATE_UPDATE,
				".$DB->DateToCharFunction("S.DATE_INSERT", "FULL")." AS DATE_INSERT,
				".$DB->DateToCharFunction("S.DATE_CONFIRM", "FULL")." AS DATE_CONFIRM
			FROM
				b_subscription S
			WHERE
				S.EMAIL='".$DB->ForSQL($email)."'
				".$sWhere."
		";

		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	//agent to delete unconfirmed subscription
	public static function CleanUp()
	{
		global $DB;
		$interval = intval(COption::GetOptionString("subscribe", "subscribe_confirm_period"));
		if($interval > 0)
		{
			$strSql =
				"SELECT ID ".
				"FROM b_subscription ".
				"WHERE CONFIRMED<>'Y' AND DATE_CONFIRM < DATE_ADD(now(), INTERVAL -".$interval." DAY) ";
			$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$sIn = "0";
			while($res_arr = $res->Fetch())
				$sIn .= ",".$res_arr["ID"];

			$DB->Query("DELETE FROM b_subscription_rubric WHERE SUBSCRIPTION_ID IN (".$sIn.")", false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_subscription WHERE ID IN (".$sIn.")", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		return "CSubscription::CleanUp();";
	}
}
