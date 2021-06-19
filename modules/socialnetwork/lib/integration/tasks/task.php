<?php
/**
* Bitrix Framework
* @package bitrix
* @subpackage socialnetwork
* @copyright 2001-2017 Bitrix
*/
namespace Bitrix\Socialnetwork\Integration\Tasks;

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Livefeed\Provider;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
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

		if (
			$liveFeedEntity = Provider::init([
				'ENTITY_TYPE' => Provider::DATA_ENTITY_TYPE_TASKS_TASK,
				'ENTITY_ID' => $taskId,
			])
		)
		{
			$liveFeedEntity->setContentView(['user_id' => $userId]);
		}

		$result = new EventResult(EventResult::SUCCESS, [], 'socialnetwork');

		return $result;
	}

	public static function onTaskUserOptionChanged(Event $event)
	{
		$result = new EventResult(
			EventResult::UNDEFINED,
			[],
			'socialnetwork'
		);

		$taskId = intval($event->getParameter('taskId'));
		$userId = intval($event->getParameter('userId'));
		$option = $event->getParameter('option');
		$added = $event->getParameter('added');

		if (
			$taskId <= 0
			|| $userId <= 0
			|| !Loader::includeModule('tasks')
			|| $option != \Bitrix\Tasks\Internals\UserOption\Option::MUTED
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
			$logId = intval($logFields['ID']);
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

		$result = new EventResult(
			EventResult::SUCCESS,
			[],
			'socialnetwork'
		);

		return $result;
	}
}
?>