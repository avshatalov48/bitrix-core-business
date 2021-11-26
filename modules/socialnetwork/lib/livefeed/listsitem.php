<?php

namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Socialnetwork\LogTable;
use Bitrix\Main\Config\Option;

final class ListsItem extends Provider
{
	public const PROVIDER_ID = 'LISTS_NEW_ELEMENT';
	public const CONTENT_TYPE_ID = 'LISTS_NEW_ELEMENT';

	protected static $logTableClass = LogTable::class;

	public static function getId(): string
	{
		return static::PROVIDER_ID;
	}

	public function getEventId(): array
	{
		return [ 'lists_new_element' ];
	}

	public function getType(): string
	{
		return Provider::TYPE_POST;
	}

	public function getCommentProvider(): Provider
	{
		return new ForumPost();
	}

	public function initSourceFields()
	{
		static $cache = [];

		$elementId = $this->entityId;

		if ($elementId <= 0)
		{
			return;
		}

		if (isset($cache[$elementId]))
		{
			$logEntryFields = $cache[$elementId];
		}
		else
		{
			$res = self::$logTableClass::getList([
				'filter' => [
					'SOURCE_ID' => $elementId,
					'@EVENT_ID' => $this->getEventId(),
				],
				'select' => [ 'ID', 'TITLE', 'MESSAGE', 'TEXT_MESSAGE', 'PARAMS' ]
			]);

			$logEntryFields = $res->fetch();
			$cache[$elementId] = $logEntryFields;
		}

		if (empty($logEntryFields))
		{
			return;
		}

		$this->setLogId($logEntryFields['ID']);
		$this->setSourceFields($logEntryFields);
		$this->setSourceTitle($logEntryFields['TITLE']);

		$description = $logEntryFields['TEXT_MESSAGE'];
		$description = preg_replace('/<script(.*?)>(.*?)<\/script>/is', '', $description);
		$description = \CTextParser::clearAllTags($description);
		$this->setSourceDescription($description);
	}

	public function getPinnedTitle()
	{
		$result = '';

		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		$logEntryFields = $this->getSourceFields();
		if (empty($logEntryFields))
		{
			return $result;
		}

		return $logEntryFields['TITLE'];
	}

	public function getPinnedDescription()
	{
		$result = '';

		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		$logEntryFields = $this->getSourceFields();
		if (empty($logEntryFields))
		{
			return $result;
		}

		$description = $logEntryFields['TEXT_MESSAGE'];
		$description = preg_replace('/<script(.*?)>(.*?)<\/script>/is', '', $description);
		$description = \CTextParser::clearAllTags($description);

		return truncateText(htmlspecialcharsback($description), 100);
	}

	public function getLiveFeedUrl(): string
	{
		$pathToLogEntry = Option::get('socialnetwork', 'log_entry_page');
		if (!empty($pathToLogEntry))
		{
			$pathToLogEntry = \CComponentEngine::makePathFromTemplate($pathToLogEntry, array("log_id" => $this->getLogId()));
		}

		return $pathToLogEntry;
	}
}