<?php

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

			$data = unserialize($arRes['MESSAGE'], ["allowed_classes" => false]);
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
		if (is_array($channelId))
		{
			$channelList = array_map(
				fn($channel) => \Bitrix\Pull\Model\Channel::createWithFields(['CHANNEL_ID' => $channel]),
				$channelId
			);
			return \Bitrix\Pull\Event::add($channelList, $params);
		}
		else if (is_string($channelId))
		{
			return \Bitrix\Pull\Event::add(
				\Bitrix\Pull\Model\Channel::createWithFields(['CHANNEL_ID' => $channelId]),
				$params
			);
		}
		else
		{
			throw new \Bitrix\Main\ArgumentException('channelId must be a string or an array of strings');
		}
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
		try
		{
			$sharedChannel = \Bitrix\Pull\Model\Channel::getShared();
		}
		catch (\Bitrix\Main\SystemException $e)
		{
			// \Bitrix\Main\Application::getInstance()->getExceptionHandler()->writeToLog($e);
			return false;
		}

		return \Bitrix\Pull\Event::add(
			$sharedChannel,
			$arMessage,
			$channelType
		);
	}

	/**
	 * @deprecated
	 * @see \CAllPullStack::AddShared Use instead
	 */
	public static function AddBroadcast($arMessage)
	{
		return self::AddShared($arMessage);
	}
}
?>