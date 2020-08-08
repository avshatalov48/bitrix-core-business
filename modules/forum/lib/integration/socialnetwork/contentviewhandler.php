<?php

namespace Bitrix\Forum\Integration\Socialnetwork;

use Bitrix\Main\Loader;
use Bitrix\Forum\MessageTable;
use Bitrix\Main\Event;

/**
 * Class for content view event handlers
 *
 * Class ContentViewHandler
 * @package Bitrix\Forum\Integration\Socialnetwork
 */
final class ContentViewHandler
{
	const CONTENT_TYPE_ID_COMMENT = 'FORUM_POST';

	final static function getContentTypeIdList()
	{
		return [
			self::CONTENT_TYPE_ID_COMMENT
		];
	}

	/**
	 * Handles content view event, marking IM notifications as read
	 *
	 * @param \Bitrix\Main\Event $event Event.
	 * @return int|false
	 */
	public static function onContentViewed(Event $event)
	{
		$userId = intval($event->getParameter('userId'));
		$contentTypeId = $event->getParameter('typeId');
		$contentEntityId = intval($event->getParameter('entityId'));

		if (
			$userId <= 0
			|| !in_array($contentTypeId, self::getContentTypeIdList())
			|| $contentEntityId <= 0
		)
		{
			return false;
		}

		$subTagList = [];
		if ($contentTypeId == self::CONTENT_TYPE_ID_COMMENT)
		{
			$res = MessageTable::getList([
				'filter' => [
					'=ID' => $contentEntityId
				],
				'select' => [ 'XML_ID' ]
			]);
			if ($message = $res->fetch())
			{
				if (preg_match("/^TASK_(.+)\$/", $message["XML_ID"], $match))
				{
					$taskId = intval($match[1]);

					$event = new Event(
						'forum', 'onTaskCommentContentViewed',
						[
							'userId' => $userId,
							'taskId' => $taskId,
							'commentId' => $contentEntityId
						]
					);
					$event->send();

					$subTagList[] = "TASKS|COMMENT|".$taskId.'|'.$userId.'|'.$contentEntityId.'|TASK_UPDATE';
				}
				else
				{
					$subTagList[] = "FORUM|COMMENT|".$contentEntityId.'|'.$userId;
				}
			}
		}

		if (
			Loader::includeModule('im')
			&& !empty($subTagList)
		)
		{
			$CIMNotify = new \CIMNotify();
			$CIMNotify->markNotifyReadBySubTag($subTagList);
		}

		return true;
	}
}
