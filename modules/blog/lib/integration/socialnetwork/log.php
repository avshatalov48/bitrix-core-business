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
use Bitrix\Blog\Item\Post;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Item\LogIndex;
use Bitrix\Vote\UF\Manager;

class Log
{
	const EVENT_ID_POST = 'blog_post';
	const EVENT_ID_POST_IMPORTANT = 'blog_post_important';
	const EVENT_ID_POST_VOTE = 'blog_post_vote';
	const EVENT_ID_POST_GRAT = 'blog_post_grat';

	/**
	 * Returns set EVENT_ID processed by handler to generate content for full index.
	 *
	 * @param void
	 * @return array
	 */
	public static function getEventIdList()
	{
		return array(
			self::EVENT_ID_POST,
			self::EVENT_ID_POST_IMPORTANT,
			self::EVENT_ID_POST_VOTE,
			self::EVENT_ID_POST_GRAT
		);
	}

	/**
	 * Returns content for LogIndex.
	 *
	 * @param Event $event Event from LogIndex::setIndex().
	 * @return EventResult
	 */
	public static function onIndexGetContent(Event $event)
	{
		global $USER_FIELD_MANAGER;

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
		$post = false;

		if (intval($sourceId) > 0)
		{
			$post = Post::getById($sourceId);
		}

		if ($post)
		{
			$postFieldList = $post->getFields();

			$content .= LogIndex::getUserName($postFieldList["AUTHOR_ID"])." ";
			if (
				$postFieldList["MICRO"] != "Y"
				&& isset($postFieldList["TITLE"])
				&& $postFieldList["TITLE"] <> ''
			)
			{
				$content .= \blogTextParser::killAllTags($postFieldList["TITLE"])." ";
			}
			$content .= \blogTextParser::killAllTags($postFieldList["DETAIL_TEXT"]);

			$destinationsList = array();
			$res = \CBlogPost::getSocNetPerms($sourceId);
			foreach($res as $group => $list)
			{
				foreach($list as $key => $valuesList)
				{
					$destinationsList = array_merge($destinationsList, $valuesList);
				}
			}

			if (!empty($destinationsList))
			{
				$content .= ' '.join(' ', LogIndex::getEntitiesName($destinationsList));
			}

			if (!empty($postFieldList['UF_BLOG_POST_FILE']))
			{
				$fileNameList = LogIndex::getDiskUFFileNameList($postFieldList['UF_BLOG_POST_FILE']);
				if (!empty($fileNameList))
				{
					$content .= ' '.join(' ', $fileNameList);
				}
			}

			if (!empty($postFieldList['UF_BLOG_POST_URL_PRV']))
			{
				$metadata = \Bitrix\Main\UrlPreview\UrlMetadataTable::getRowById($postFieldList['UF_BLOG_POST_URL_PRV']);
				if (
					$metadata
					&& isset($metadata['TITLE'])
					&& $metadata['TITLE'] <> ''
				)
				{
					$content .= ' '.$metadata['TITLE'];
				}
			}

			if (
				!empty($postFieldList['UF_BLOG_POST_VOTE'])
				&& intval($postFieldList['UF_BLOG_POST_VOTE']) > 0
				&& Loader::includeModule('vote')
			)
			{
				$postUFList = $USER_FIELD_MANAGER->getUserFields("BLOG_POST", $sourceId, LANGUAGE_ID);

				if (!empty($postUFList['UF_BLOG_POST_VOTE']))
				{
					if (
						($userFieldManager = Manager::getInstance($postUFList['UF_BLOG_POST_VOTE']))
						&& ($attach = $userFieldManager->loadFromAttachId(intval($postFieldList['UF_BLOG_POST_VOTE'])))
					)
					{
						foreach ($attach["QUESTIONS"] as $question)
						{
							$content .= ' '.$question["QUESTION"];
							foreach ($question["ANSWERS"] as $answer)
							{
								$content .= ' '.$answer["MESSAGE"];
							}
						}
					}
				}
			}

			if (!empty($postFieldList['CATEGORY_ID']))
			{
				$categoryList = explode(",", $postFieldList["CATEGORY_ID"]);
				$tagList = array();
				foreach($categoryList as $value)
				{
					$category = \CBlogCategory::getByID($value);
					$tagList[] = $category["NAME"];
					$tagList[] = '#'.$category["NAME"];
				}
				if (!empty($tagList))
				{
					$content .= ' '.implode(' ', $tagList);
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

