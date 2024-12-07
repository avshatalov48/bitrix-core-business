<?php

use Bitrix\Main\Application;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/log_smartfilter.php");

class CSocNetLogSmartFilter extends CAllSocNetLogSmartFilter
{
	public static function Set($user_id, $type)
	{
		global $DB;

		$helper = Application::getConnection()->getSqlHelper();
		$user_id = intval($user_id);

		if ($user_id <= 0)
			return false;

		if ($type != "Y")
			$type = "N";

		$strSQL = $helper->prepareMerge(
			'b_sonet_log_smartfilter',
			[],
			['USER_ID' => $user_id, 'TYPE' => $type,],
			['TYPE' => $type,]
		);
		$res = $DB->Query($strSQL);

		if ($res)
		{
			$cache = new \CPHPCache();
			$cache->clean('sonet_smartfilter_default_'.$user_id, '/sonet/log_smartfilter/');
		}
	}
}
