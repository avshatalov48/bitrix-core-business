<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage forum
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Forum\Integration\Socialnetwork;

use Bitrix\Forum\MessageTable;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Socialnetwork\Item\LogIndex;

class LogComment
{
	const EVENT_ID_FORUM_COMMENT = 'forum';
	const EVENT_ID_TASKS_COMMENT = 'tasks_comment';
	const EVENT_ID_CALENDAR_COMMENT = 'calendar_comment';
	const EVENT_ID_WIKI_COMMENT = 'wiki_comment';
	const EVENT_ID_TIMEMAN_ENTRY_COMMENT = 'timeman_entry_comment';
	const EVENT_ID_TIMEMAN_REPORT_COMMENT = 'report_comment';
	const EVENT_ID_LISTS_NEW_ELEMENT_COMMENT = 'lists_new_element_comment';
	const EVENT_ID_CRM_ACTIVITY_ADD_COMMENT = 'crm_activity_add_comment';

	public static function getEventIdList()
	{
		return [
			self::EVENT_ID_FORUM_COMMENT,
			self::EVENT_ID_TASKS_COMMENT,
			self::EVENT_ID_CALENDAR_COMMENT,
			self::EVENT_ID_WIKI_COMMENT,
			self::EVENT_ID_TIMEMAN_ENTRY_COMMENT,
			self::EVENT_ID_TIMEMAN_REPORT_COMMENT,
			self::EVENT_ID_LISTS_NEW_ELEMENT_COMMENT,
			self::EVENT_ID_CRM_ACTIVITY_ADD_COMMENT,
		];
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
			'forum'
		);

		$eventId = $event->getParameter('eventId');
		$sourceId = $event->getParameter('sourceId');

		if (!in_array($eventId, self::getEventIdList()))
		{
			return $result;
		}

		$content = "";
		$message = false;

		if ((int)$sourceId > 0)
		{
			$select = array('*', 'UF_FORUM_MES_URL_PRV', 'SERVICE_TYPE');

			if (
				\Bitrix\Main\Config\Option::get('disk', 'successfully_converted', false)
				&& \Bitrix\Main\ModuleManager::isModuleInstalled('disk')
			)
			{
				$select[] = 'UF_FORUM_MESSAGE_DOC';
			}

			$res = MessageTable::getList(array(
				'filter' => array(
					'=ID' => $sourceId
				),
				'select' => $select
			));
			$message = $res->fetch();
		}

		if ($message)
		{
			if (!empty($message['SERVICE_TYPE']))
			{
				return $result;
			}

			$content .= LogIndex::getUserName($message["AUTHOR_ID"])." ";
			$content .= \forumTextParser::clearAllTags($message['POST_MESSAGE']);

			if (!empty($message['UF_FORUM_MESSAGE_DOC']))
			{
				$fileNameList = LogIndex::getDiskUFFileNameList($message['UF_FORUM_MESSAGE_DOC']);
				if (!empty($fileNameList))
				{
					$content .= ' '.implode(' ', $fileNameList);
				}
			}

			if (!empty($message['UF_FORUM_MES_URL_PRV']))
			{
				$metadata = \Bitrix\Main\UrlPreview\UrlMetadataTable::getRowById($message['UF_FORUM_MES_URL_PRV']);
				if (
					$metadata
					&& !empty($metadata['TITLE'])
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
			'forum'
		);

		return $result;
	}
}

