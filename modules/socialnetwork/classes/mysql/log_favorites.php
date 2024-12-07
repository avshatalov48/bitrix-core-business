<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/log_favorites.php");

class CSocNetLogFavorites extends CAllSocNetLogFavorites
{
	public static function Add($user_id, $log_id, array $params = array('TRIGGER_EVENT' => true))
	{
		global $DB;

		if (
			intval($user_id) <= 0
			|| intval($log_id) <= 0
		)
		{
			return false;
		}

		$strSQL = \Bitrix\Main\Application::getConnection()->getSqlHelper()->getInsertIgnore(
			'b_sonet_log_favorites',
			' (USER_ID, LOG_ID)',
			' VALUES (' . (int)$user_id . ', ' . (int)$log_id . ')'
		);

		if ($DB->Query($strSQL))
		{
			if(
				!isset($params['TRIGGER_EVENT'])
				|| $params['TRIGGER_EVENT'] === true
			)
			{
				foreach(GetModuleEvents('socialnetwork', 'OnSonetLogFavorites', true) as $arEvent)
				{
					ExecuteModuleEventEx($arEvent, array(array('USER_ID' => intval($user_id), 'LOG_ID' => intval($log_id), 'OPERATION' => 'ADD')));
				}
			}

			return true;
		}

		return false;
	}
}