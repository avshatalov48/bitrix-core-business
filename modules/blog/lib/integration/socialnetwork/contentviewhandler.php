<?php

namespace Bitrix\Blog\Integration\Socialnetwork;

use Bitrix\Main\Loader;

/**
 * Class for content view event handlers
 *
 * Class ContentViewHandler
 * @package Bitrix\Blog\Integration\Socialnetwork
 */
final class ContentViewHandler
{
	const CONTENT_TYPE_ID_POST = 'BLOG_POST';
	const CONTENT_TYPE_ID_COMMENT = 'BLOG_COMMENT';

	final static function getContentTypeIdList()
	{
		return array(
			self::CONTENT_TYPE_ID_POST,
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

		if ($contentTypeId == self::CONTENT_TYPE_ID_POST)
		{
			$subTagList[] = "BLOG|POST|".$contentEntityId.'|'.$userId;
			$subTagList[] = "BLOG|POST_MENTION|".$contentEntityId.'|'.$userId;
		}
		elseif ($contentTypeId == self::CONTENT_TYPE_ID_COMMENT)
		{
			$subTagList[] = "BLOG|COMMENT|".$contentEntityId.'|'.$userId;
			$subTagList[] = "BLOG|COMMENT_MENTION|".$contentEntityId.'|'.$userId;
		}

		if (!empty($subTagList))
		{
			$CIMNotify = new \CIMNotify();
			$CIMNotify->MarkNotifyReadBySubTag($subTagList);
		}

		return true;
	}
}
