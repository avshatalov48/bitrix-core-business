<?php

namespace Bitrix\Im\Update;

use Bitrix\Im\Model\MessageUnreadTable;
use Bitrix\Im\V2\Message\CounterService;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Update\Stepper;

final class ChatInvalidCounter extends Stepper
{
	private const ITERATION_COUNT = 5;

	protected static $moduleId = 'im';

	function execute(array &$option)
	{
		if (!Loader::includeModule(self::$moduleId))
		{
			return self::CONTINUE_EXECUTION;
		}

		$result = self::CONTINUE_EXECUTION;
		for ($i = 0; $i < self::ITERATION_COUNT; ++$i)
		{
			$result = $this->makeMigrationIteration($option);

			if ($result === self::FINISH_EXECUTION)
			{
				return $result;
			}
		}

		return $result;
	}

	private function makeMigrationIteration(array &$option): bool
	{
		$lastId = $option['lastId'] ?? 0;
		$userId = $this->getNextUser($lastId);

		if ($userId === null || $userId === 0)
		{
			return self::FINISH_EXECUTION;
		}

		$option['lastId'] = $userId;

		$chatIds = $this->getChatsWithInvalidCounterByUserId($userId);

		if (empty($chatIds))
		{
			return self::CONTINUE_EXECUTION;
		}

		MessageUnreadTable::deleteByFilter(['=USER_ID' => $userId, '=CHAT_ID' => $chatIds]);
		CounterService::clearCache($userId);

		return self::CONTINUE_EXECUTION;
	}

	private function getChatsWithInvalidCounterByUserId(int $userId): array
	{
		$query = "
		SELECT x1.CHAT_ID FROM (
			SELECT bimu.CHAT_ID
			FROM b_im_message_unread bimu
			WHERE USER_ID = {$userId} GROUP BY CHAT_ID
		) x1
		
		LEFT JOIN (
		
			SELECT bir.CHAT_ID
			FROM b_im_relation bir
			WHERE
				bir.USER_ID = {$userId} AND
				bir.CHAT_ID IN (
					SELECT bimu.CHAT_ID
					FROM b_im_message_unread bimu
					WHERE USER_ID = {$userId} GROUP BY CHAT_ID
				)
		) x2 ON x1.CHAT_ID = x2.CHAT_ID
		
		WHERE x2.CHAT_ID is null
		";
		$result = Application::getConnection()->query($query);
		$chatIds = [];

		while ($row = $result->fetch())
		{
			$chatIds[] = (int)$row['CHAT_ID'];
		}

		return $chatIds;
	}

	private function getNextUser(?int $lastId = null): ?int
	{
		$query = MessageUnreadTable::query()
			->setSelect(['USER_ID'])
			->setGroup(['USER_ID'])
			->setOrder(['USER_ID'])
			->setLimit(1)
		;

		if (isset($lastId) && $lastId > 0)
		{
			$query->where('USER_ID', '>', $lastId);
		}

		$result = $query->fetch();

		if (!$result || !isset($result['USER_ID']))
		{
			return null;
		}

		return (int)$result['USER_ID'];
	}
}