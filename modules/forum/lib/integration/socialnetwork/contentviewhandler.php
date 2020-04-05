<?php

namespace Bitrix\Forum\Integration\Socialnetwork;

use Bitrix\Main\Loader;
use Bitrix\Forum\MessageTable;

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
		return array(
			self::CONTENT_TYPE_ID_COMMENT
		);
	}

	/**
	 * Handles content view event, marking IM notifications as read
	 *
	 * @param \Bitrix\Main\Event $event Event.
	 * @return int|false
	 */
	public static function onContentViewed(\Bitrix\Main\Event $event)
	{
		$userId = intval($event->getParameter('userId'));
		$contentTypeId = $event->getParameter('typeId');
		$contentEntityId = intval($event->getParameter('entityId'));

		if (
			$userId <= 0
			|| !in_array($contentTypeId, self::getContentTypeIdList())
			|| $contentEntityId <= 0
			|| !Loader::includeModule('im')
		)
		{
			return false;
		}

		$subTagList = array();
		if ($contentTypeId == self::CONTENT_TYPE_ID_COMMENT)
		{
			$res = MessageTable::getList(array(
				'filter' => array(
					'=ID' => $contentEntityId
				),
				'select' => array('XML_ID')
			));
			if ($message = $res->fetch())
			{
				if (preg_match("/^TASK_(.+)\$/", $message["XML_ID"], $match))
				{
					$subTagList[] = "TASKS|COMMENT|".intval($match[1]).'|'.$userId.'|'.$contentEntityId.'|TASK_UPDATE';
				}
				else
				{
					$subTagList[] = "FORUM|COMMENT|".$contentEntityId.'|'.$userId;
				}
			}
		}

		if (!empty($subTagList))
		{
			$CIMNotify = new \CIMNotify();
			$CIMNotify->markNotifyReadBySubTag($subTagList);
		}

		return true;
	}
}
