<?php
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/pull/classes/general/pull_watch.php");

class CPullWatch extends CAllPullWatch
{
	// check watch that are older than 30 minutes, remove them.
	public static function CheckExpireAgent()
	{
		global $DB, $pPERIOD;
		$pPERIOD = 1200;

		$connection = \Bitrix\Main\Application::getConnection();

		$strSql = "DELETE FROM b_pull_watch WHERE DATE_CREATE < " . $connection->getSqlHelper()->addSecondsToDateTime(-32 * 60);
		$result = $DB->Query($strSql);

		if (
			$result
			&& is_object($result)
			&& $result->AffectedRowsCount() == 1000
		)
		{
			$pPERIOD = 180;
		}

		return __METHOD__ . '();';
	}
}
