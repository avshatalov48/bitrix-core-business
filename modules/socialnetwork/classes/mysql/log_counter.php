<?php

/**
 * @deprecated
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/log_counter.php");

class CSocNetLogCounter extends CAllSocNetLogCounter
{
	const TYPE_LOG_ENTRY = 'L';
	const TYPE_LOG_COMMENT = 'LC';

	function Increment($log_id, $entity_type = false, $entity_id = false, $event_id = false, $created_by_id = false, $arOfEntities = false, $arAdmin = false, $transport = false, $visible = "Y", $type = CSocNetLogCounter::TYPE_LOG_ENTRY)
	{
		global $DB;

		if (intval($log_id) <= 0)
			return false;

		$counter = new CSocNetLogCounter;

		$subSelect = $counter->GetSubSelect($log_id, $entity_type, $entity_id, $event_id, $created_by_id, $arOfEntities, $arAdmin, $transport, $visible, $type);
		if ($subSelect <> '')
		{
			$strSQL = "INSERT INTO b_sonet_log_counter (USER_ID, CNT, SITE_ID, CODE) (".$subSelect.") ON DUPLICATE KEY UPDATE CNT = CNT + 1";
			$DB->Query($strSQL);
		}

		$subSelect = $counter->GetSubSelect($log_id, $entity_type, $entity_id, $event_id, $created_by_id, $arOfEntities, $arAdmin, $transport, $visible, "group");
		if ($subSelect <> '')
		{
			$strSQL = "INSERT INTO b_sonet_log_counter (USER_ID, CNT, SITE_ID, CODE) (".$subSelect.") ON DUPLICATE KEY UPDATE CNT = CNT + 1";
			$DB->Query($strSQL);
		}
	}

	function ClearByUser($user_id, $site_id = SITE_ID, $code = "**", $page_size = 0, $page_last_date_1 = "")
	{
		global $DB;

		$user_id = intval($user_id);
		if ($user_id <= 0)
			return false;

		$strSQL = "
			INSERT INTO b_sonet_log_counter (USER_ID, SITE_ID, CODE, CNT, LAST_DATE, PAGE_SIZE, PAGE_LAST_DATE_1)
			VALUES ($user_id, '".$DB->ForSQL($site_id)."', '".$DB->ForSQL($code)."', 0, ".$DB->CurrentTimeFunction().", ".(intval($page_size) > 0 ? $page_size : "NULL").", ".($page_last_date_1 <> '' ? $DB->CharToDateFunction($page_last_date_1) : "NULL").")
			ON DUPLICATE KEY UPDATE CNT = 0, LAST_DATE = ".$DB->CurrentTimeFunction().(intval($page_size) > 0 ? ", PAGE_SIZE = ".$page_size : "").($page_last_date_1 <> '' ? ", PAGE_LAST_DATE_1 = ".$DB->CharToDateFunction($page_last_date_1) : "")."
		";
		$res = $DB->Query($strSQL);

		$strSQL = "DELETE FROM b_sonet_log_counter WHERE USER_ID = ".$user_id." AND CODE = '".$code."' AND SITE_ID = '**'";
		$res = $DB->Query($strSQL);
	}

	function dbIF($condition, $yes, $no)
	{
		return " case when ".$condition." then ".$yes." else ".$no." end ";
	}
}
