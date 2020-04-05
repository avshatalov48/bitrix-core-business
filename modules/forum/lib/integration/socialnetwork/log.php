<?
/**
 * @access private
 */

namespace Bitrix\Forum\Integration\SocialNetwork;

use Bitrix\Forum\MessageTable;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Socialnetwork\Item\LogIndex;

class Log
{
	const EVENT_ID_FORUM = 'forum';

	/**
	 * Returns set EVENT_ID processed by handler to generate content for full index.
	 *
	 * @param void
	 * @return array
	 */
	public static function getEventIdList()
	{
		return array(
			self::EVENT_ID_FORUM
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

		if (intval($sourceId) > 0)
		{

			$select = array('*', 'TOPIC.TITLE', 'UF_FORUM_MES_URL_PRV');

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
			$content .= LogIndex::getUserName($message["AUTHOR_ID"])." ";
			$content .= $message['FORUM_MESSAGE_TOPIC_TITLE']." ";
			$content .= \forumTextParser::clearAllTags($message['POST_MESSAGE']);

			if (!empty($message['UF_FORUM_MESSAGE_DOC']))
			{
				$fileNameList = LogIndex::getDiskUFFileNameList($message['UF_FORUM_MESSAGE_DOC']);
				if (!empty($fileNameList))
				{
					$content .= ' '.join(' ', $fileNameList);
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