<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/pull/classes/general/pull_watch.php");

class CPullWatch extends CAllPullWatch
{
	// check watch that are older than 30 minutes, remove them.
	public static function CheckExpireAgent()
	{
		global $DB, $pPERIOD;
		$pPERIOD = 1200;

		$strSql = "DELETE FROM b_pull_watch WHERE DATE_CREATE < DATE_SUB(NOW(), INTERVAL 32 MINUTE) LIMIT 1000";
		$result = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if (
			$result
			&& is_object($result)
			&& $result->AffectedRowsCount() == 1000
		)
		{
			$pPERIOD = 180;
		}

		return "CPullWatch::CheckExpireAgent();";
	}
}
?>