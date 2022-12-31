<?php

/**
* Bitrix Framework
* @package bitrix
* @subpackage socialnetwork
* @copyright 2001-2017 Bitrix
*/
namespace Bitrix\Socialnetwork\Integration\Tasks;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Loader;
use Bitrix\Main\UserCounterTable;
use Bitrix\Socialnetwork\Livefeed\Provider;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Socialnetwork\LogCommentTable;
use Bitrix\Socialnetwork\LogTable;

class Task
{
	public static function onTaskUpdateViewed(Event $event): EventResult
	{
		$result = new EventResult(EventResult::UNDEFINED, [], 'socialnetwork');

		$taskId = (int)$event->getParameter('taskId');
		$userId = (int)$event->getParameter('userId');

		if ($taskId <= 0 || $userId <= 0)
		{
			return $result;
		}

		if ($event->getParameter('isRealView'))
		{
			$liveFeedEntity = Provider::init([
				'ENTITY_TYPE' => Provider::DATA_ENTITY_TYPE_TASKS_TASK,
				'ENTITY_ID' => $taskId,
			]);
			if ($liveFeedEntity)
			{
				$liveFeedEntity->setContentView(['user_id' => $userId]);
				self::updateUserCounter([
					'userId' => $userId,
					'logId' => $liveFeedEntity->getLogId(),
				]);
			}
		}

		return new EventResult(EventResult::SUCCESS, [], 'socialnetwork');
	}

	private static function updateUserCounter(array $params = []): void
	{
		$logId = (int)($params['logId'] ?? 0);
		$userId = (int)($params['userId'] ?? 0);
		$siteId = SITE_ID;

		if (
			$logId <= 0
			|| $userId <= 0
		)
		{
			return;
		}

		UserCounterTable::delete([
			'USER_ID' => $userId,
			'SITE_ID' => SITE_ID,
			'CODE' => '**L' . $logId,
		]);

		$query = new \Bitrix\Main\Entity\Query(UserCounterTable::getEntity());
		$query->addFilter('=USER_ID', $userId);
		$query->addFilter('=SITE_ID', $siteId);
		$query->addSelect('CODE');

		$query->registerRuntimeField(
			'comment',
			new \Bitrix\Main\Entity\ReferenceField('LC',
				LogCommentTable::getEntity(),
				[
					'=ref.LOG_ID' => new SqlExpression('?i', $logId),
				],
				[ 'join_type' => 'INNER' ]
			)
		);

		$query->whereExpr("%s = CONCAT('**LC', %s)", [ 'CODE', 'comment.ID' ]);
		$res = $query->exec();

		while ($counterFields = $res->fetch())
		{
			UserCounterTable::delete([
				'USER_ID' => $userId,
				'SITE_ID' => $siteId,
				'CODE' => $counterFields['CODE'],
			]);
		}

		// to send pushes only
		UserCounterTable::update([
			'USER_ID' => $userId,
			'SITE_ID' => $siteId,
			'CODE' => '**',
		], [
			'SENT' => 0,
		]);
	}

	public static function onTaskUserOptionChanged(Event $event): EventResult
	{
		$result = new EventResult(
			EventResult::UNDEFINED,
			[],
			'socialnetwork'
		);

		$taskId = (int)$event->getParameter('taskId');
		$userId = (int)$event->getParameter('userId');
		$option = (int)$event->getParameter('option');
		$added = $event->getParameter('added');

		if (
			$taskId <= 0
			|| $userId <= 0
			|| $option !== \Bitrix\Tasks\Internals\UserOption\Option::MUTED
			|| !Loader::includeModule('tasks')
		)
		{
			return $result;
		}

		$logId = 0;
		$provider = new \Bitrix\Socialnetwork\Livefeed\TasksTask();
		$res = LogTable::getList([
			'filter' => [
				'@EVENT_ID' => $provider->getEventId(),
				'=SOURCE_ID' => $taskId
			],
			'select' => [ 'ID' ]
		]);
		if ($logFields = $res->fetch())
		{
			$logId = (int)$logFields['ID'];
		}
		if ($logId <= 0)
		{
			return $result;
		}

		$followDate = false;
		if (!$added)
		{
			\CSocNetLogFollow::delete($userId, 'L'.$logId);
			$followDate = ConvertTimeStamp(time() + \CTimeZone::getOffset(), 'FULL', SITE_ID); // compromise, we cannot get it from $logFields because it can have not updated value yet
		}

		\CSocNetLogFollow::set($userId, 'L'.$logId, ($added ? 'N' : 'Y'), $followDate);

		return new EventResult(
			EventResult::SUCCESS,
			[],
			'socialnetwork'
		);
	}
}

