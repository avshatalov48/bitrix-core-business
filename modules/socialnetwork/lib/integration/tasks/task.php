<?php
/**
* Bitrix Framework
* @package bitrix
* @subpackage socialnetwork
* @copyright 2001-2017 Bitrix
*/
namespace Bitrix\Socialnetwork\Integration\Tasks;

use Bitrix\Socialnetwork\Livefeed\Provider;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class Task
{
	public static function onTaskUpdateViewed(Event $event)
	{
		$result = new EventResult(
			EventResult::UNDEFINED,
			array(),
			'socialnetwork'
		);

		$taskId = $event->getParameter('taskId');
		$userId = $event->getParameter('userId');

		if (
			intval($taskId) <= 0
			|| intval($userId) <= 0
		)
		{
			return $result;
		}

		if ($liveFeedEntity = Provider::init(array(
			'ENTITY_TYPE' => Provider::DATA_ENTITY_TYPE_TASKS_TASK,
			'ENTITY_ID' => $taskId
		)))
		{
			$liveFeedEntity->setContentView(array(
				"userId" => $userId
			));
		}

		$result = new EventResult(
			EventResult::SUCCESS,
			array(),
			'socialnetwork'
		);

		return $result;
	}
}
?>