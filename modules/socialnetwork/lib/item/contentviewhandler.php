<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2017 Bitrix
*/

namespace Bitrix\Socialnetwork\Item;

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\LogCommentTable;

/**
 * Class for content view event handlers
 *
 * Class ContentViewHandler
 * @package Bitrix\Socialnetwork\Item
 */
final class ContentViewHandler
{
	const CONTENT_TYPE_ID_COMMENT = 'LOG_COMMENT';

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
	public static function onContentViewed($viewParams)
	{
		$userId = intval($viewParams['userId']);
		$contentTypeId = $viewParams['typeId'];
		$contentEntityId = intval($viewParams['entityId']);

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

		}

		if (!empty($subTagList))
		{
			$CIMNotify = new \CIMNotify();
			$CIMNotify->markNotifyReadBySubTag($subTagList);
		}

		return true;
	}
}
