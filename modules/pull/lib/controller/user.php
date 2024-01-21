<?php

namespace Bitrix\Pull\Controller;

use \Bitrix\Main\Application;
use Bitrix\Main\Engine;
use Bitrix\Main\Error;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use Bitrix\Pull\JsonRpcTransport;

class User extends Engine\Controller
{
	function getLastSeenAction(array $userIds, bool $sendToQueueSever = false)
	{
		$userIds = array_map(fn ($a) => (int)$a, $userIds);
		if (empty($userIds))
		{
			$this->addError(new Error("userIds should not be empty"));
		}

		$now = time();
		$timestamps = [];
		$result = [];

		$cursor = UserTable::getList([
			'select' => ['ID', 'LAST_ACTIVITY_DATE'],
			'filter' => ['=ID' => $userIds],
		]);

		foreach ($cursor->getIterator() as $row)
		{
			if ($row['LAST_ACTIVITY_DATE'] instanceof DateTime)
			{
				$userId = $row['ID'];
				$lastSeen = $row['LAST_ACTIVITY_DATE']->getTimestamp();
				$timestamps[$userId] = $lastSeen;
				$result[$userId] = $now - $lastSeen;
			}
		}

		if ($sendToQueueSever && !empty($timestamps))
		{
			Application::getInstance()->addBackgroundJob(
				function (array $timestamps) {
					(new JsonRpcTransport())->updateUsersLastSeen($timestamps);
				},
				[$timestamps]
			);
		}
		return $result;
	}
}