<?php
namespace Bitrix\Pull\Update;

class Channel
{
	public static function updatePublicChannelIdAgent()
	{
		global $CACHE_MANAGER;

		$hasChannels = false;

		$connection = \Bitrix\Main\Application::getInstance()->getConnection();
		$sqlDate = $connection->getSqlHelper()->addDaysToDateTime(-1);

		$channels = $connection->query("
			SELECT ID, USER_ID, CHANNEL_TYPE
			FROM b_pull_channel 
			WHERE DATE_CREATE > {$sqlDate} AND USER_ID <> 0 AND CHANNEL_PUBLIC_ID IS NULL
			LIMIT 100
		");
		while ($channel = $channels->fetch())
		{
			$hasChannels = true;
			$connection->query("UPDATE b_pull_channel SET CHANNEL_PUBLIC_ID = '".\CPullChannel::GetNewChannelId('public')."' WHERE ID = ".$channel['ID']);
			$CACHE_MANAGER->Clean("b_pchc_".$channel['ID'].'_'.$channel['CHANNEL_TYPE'], "b_pull_channel");
		}

		return $hasChannels ? __METHOD__. '();' : '';
	}
}