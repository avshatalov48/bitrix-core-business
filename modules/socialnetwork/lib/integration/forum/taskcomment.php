<?php
namespace Bitrix\Socialnetwork\Integration\Forum;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class TaskComment
{
	protected static $viewedCommentsCache = [];

	private static function addViewedComment($params = [])
	{
		if (!is_array($params))
		{
			return false;
		}

		$taskId = (isset($params['taskId']) ? intval($params['taskId']) : 0);
		$commentId = (isset($params['commentId']) ? intval($params['commentId']) : 0);
		if (
			!$taskId
			|| !$commentId
		)
		{
			return false;
		}

		if (!isset(self::$viewedCommentsCache[$taskId]))
		{
			self::$viewedCommentsCache[$taskId] = [];
		}

		if (in_array($commentId, self::$viewedCommentsCache[$taskId]))
		{
			return true;
		}

		self::$viewedCommentsCache[$taskId][] = $commentId;

		return true;
	}

	public static function getViewedCommentsTasksList()
	{
		return array_keys(self::$viewedCommentsCache);
	}

	public static function onViewed(Event $event)
	{
		$result = new EventResult(
			EventResult::UNDEFINED,
			[],
			'socialnetwork'
		);

		$taskId = $event->getParameter('taskId');
		$commentId = $event->getParameter('commentId');

		if(
			intval($taskId) <= 0
			|| intval($commentId) <= 0
		)
		{
			return $result;
		}

		self::addViewedComment([
			'taskId' => $taskId,
			'commentId' => $commentId
		]);

		return $result;
	}
}