<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage blog
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Blog\Integration\Socialnetwork;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Blog\Item\Comment;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\CommentAux;
use Bitrix\Socialnetwork\Item\LogIndex;

class LogComment
{
	const EVENT_ID_COMMENT = 'blog_comment';

	public static function getEventIdList()
	{
		return array(
			self::EVENT_ID_COMMENT
		);
	}

	/**
	 * Return content for LogIndex.
	 *
	 * @param Event $event Event from LogIndex::setIndex().
	 * @return EventResult
	 */
	public static function onIndexGetContent(Event $event)
	{
		$result = new EventResult(
			EventResult::UNDEFINED,
			array(),
			'blog'
		);

		$eventId = $event->getParameter('eventId');
		$sourceId = $event->getParameter('sourceId');

		if (!in_array($eventId, self::getEventIdList()))
		{
			return $result;
		}

		$content = "";
		$comment = false;

		if (intval($sourceId) > 0)
		{
			$comment = Comment::getById($sourceId);
		}

		if ($comment)
		{
			$commentFieldList = $comment->getFields();

			if (!($commentAuxProvider = CommentAux\Base::findProvider($commentFieldList)))
			{
				$content .= LogIndex::getUserName($commentFieldList["AUTHOR_ID"])." ";
				$content .= \blogTextParser::killAllTags($commentFieldList["POST_TEXT"]);
			}

			if (!empty($commentFieldList['UF_BLOG_COMMENT_FILE']))
			{
				$fileNameList = LogIndex::getDiskUFFileNameList($commentFieldList['UF_BLOG_COMMENT_FILE']);
				if (!empty($fileNameList))
				{
					$content .= ' '.join(' ', $fileNameList);
				}
			}

			if (!empty($commentFieldList['UF_BLOG_COMM_URL_PRV']))
			{
				$metadata = \Bitrix\Main\UrlPreview\UrlMetadataTable::getRowById($commentFieldList['UF_BLOG_COMM_URL_PRV']);
				if (
					$metadata
					&& isset($metadata['TITLE'])
					&& strlen($metadata['TITLE']) > 0
				)
				{
					$content .= ' '.$metadata['TITLE'];
				}
			}
		}

		$result = new EventResult(
			EventResult::SUCCESS,
			array(
				'content' => $content,
			),
			'blog'
		);

		return $result;
	}
}

