<?
class CAllPullStack
{
	// receive messages on stack
	// only works in PULL mode
	public static function Get($channelId, $lastId = 0)
	{
		global $DB;

		$newLastId = $lastId;
		$arMessage = Array();
		$strSql = "
				SELECT ps.ID, ps.MESSAGE
				FROM b_pull_stack ps ".($lastId > 0? '': 'LEFT JOIN b_pull_channel pc ON pc.CHANNEL_ID = ps.CHANNEL_ID')."
				WHERE ps.CHANNEL_ID = '".$DB->ForSQL($channelId)."'".($lastId > 0? " AND ps.ID > ".intval($lastId): " AND ps.ID > pc.LAST_ID" );
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while ($arRes = $dbRes->Fetch())
		{
			if ($newLastId < $arRes['ID'])
				$newLastId = $arRes['ID'];

			$data = unserialize($arRes['MESSAGE']);
			$data['id'] = $arRes['ID'];
			$data['extra'] = Array(
				'server_time' => date('c'),
				'server_time_unix' => microtime(true),
				'server_name' => COption::GetOptionString('main', 'server_name', $_SERVER['SERVER_NAME']),
				'revision_web' => PULL_REVISION_WEB,
				'revision_mobile' => PULL_REVISION_MOBILE,
			);

			$arMessage[] = $data;
		}

		if ($lastId < $newLastId)
			CPullChannel::UpdateLastId($channelId, $newLastId);

		return $arMessage;
	}

	// add a message to stack
	public static function AddByChannel($channelId, $params = Array())
	{
		global $DB;

		if (!CPullOptions::GetQueueServerStatus())
		{
			return false;
		}

		if (!is_array($channelId))
		{
			$channelId = Array($channelId);
		}

		$result = false;
		if ($params['module_id'] == '' || $params['command'] == '')
		{
			return false;
		}

		$extra = is_array($params['extra'])? $params['extra']: Array();
		$extra = array_merge($extra, Array(
			'server_name' => COption::GetOptionString('main', 'server_name', $_SERVER['SERVER_NAME']),
			'revision_web' => PULL_REVISION_WEB,
			'revision_mobile' => PULL_REVISION_MOBILE,
		));

		if (!isset($extra['server_time']))
		{
			$extra['server_time'] = date('c');
		}
		if (!$extra['server_time_unix'])
		{
			$extra['server_time_unix'] = microtime(true);
		}

		$arData = Array(
			'module_id' => mb_strtolower($params['module_id']),
			'command' => $params['command'],
			'params' => is_array($params['params'])? $params['params']: Array(),
			'extra' => $extra
		);

		if (!is_array($channelId) && !CPullOptions::IsServerShared() && CPullOptions::GetQueueServerVersion() == 1)
		{
			$arData['extra']['channel'] = $channelId;
		}

		$options = array('expiry' => isset($params['expiry'])? intval($params['expiry']): 86400);
		$res = CPullChannel::Send($channelId, \Bitrix\Pull\Common::jsonEncode($arData), $options);
		$result = $res? true: false;

		return $result;
	}

	public static function AddByUser($userId, $arMessage, $channelType = 'private')
	{
		return \Bitrix\Pull\Event::add($userId, $arMessage, $channelType);
	}

	public static function AddByUsers($users, $arMessage, $channelType = 'private')
	{
		return \Bitrix\Pull\Event::add($users, $arMessage, $channelType);
	}

	public static function AddShared($arMessage, $channelType = 'shared')
	{
		if (!CPullOptions::GetQueueServerStatus())
			return false;

		$arChannel = CPullChannel::GetChannelShared($channelType);
		return self::AddByChannel($arChannel['CHANNEL_ID'], $arMessage);
	}

	public static function AddBroadcast($arMessage)
	{
		return self::AddShared($arMessage);
	}
}
?>