<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Socialnetwork\LogTable;
use Bitrix\Main\Config\Option;

final class ListsItem extends Provider
{
	const PROVIDER_ID = 'LISTS_NEW_ELEMENT';
	const CONTENT_TYPE_ID = 'LISTS_NEW_ELEMENT';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('lists_new_element');
	}

	public function getType()
	{
		return Provider::TYPE_POST;
	}

	public function getCommentProvider()
	{
		$provider = new \Bitrix\Socialnetwork\Livefeed\ForumPost();
		return $provider;
	}

	public function initSourceFields()
	{
		$elementId = $this->entityId;

		if ($elementId > 0)
		{
			$res = LogTable::getList(array(
				'filter' => array(
					'SOURCE_ID' => $elementId,
					'@EVENT_ID' => $this->getEventId(),
				),
				'select' => array('ID', 'TITLE', 'MESSAGE', 'TEXT_MESSAGE', 'PARAMS')
			));

			if ($logEntryFields = $res->fetch())
			{
				$this->setLogId($logEntryFields['ID']);
				$this->setSourceFields($logEntryFields);
				$this->setSourceTitle($logEntryFields['TITLE']);


				$description = $logEntryFields['TEXT_MESSAGE'];
				$description = preg_replace('/<script(.*?)>(.*?)<\/script>/is', '', $description);
				$this->setSourceDescription(\CTextParser::clearAllTags($description));
			}
		}
	}

	public function getLiveFeedUrl()
	{
		$pathToLogEntry = Option::get('socialnetwork', 'log_entry_page', '');
		if (!empty($pathToLogEntry))
		{
			$pathToLogEntry = \CComponentEngine::makePathFromTemplate($pathToLogEntry, array("log_id" => $this->getLogId()));
		}

		return $pathToLogEntry;
	}
}