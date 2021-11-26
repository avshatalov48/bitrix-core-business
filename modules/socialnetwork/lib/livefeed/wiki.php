<?php

namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\LogTable;

final class Wiki extends Provider
{
	public const PROVIDER_ID = 'WIKI';
	public const CONTENT_TYPE_ID = 'WIKI';

	protected static $wikiClass = \CWiki::class;
	protected static $logTableClass = LogTable::class;

	public static function getId(): string
	{
		return static::PROVIDER_ID;
	}

	public function getEventId(): array
	{
		return [ 'wiki', 'wiki_del' ];
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
		static $wikiParser = false;
		static $cache = [];

		$elementId = $this->entityId;

		if ($elementId <= 0)
		{
			return;
		}

		$sourceFields = [];

		if (isset($cache[$elementId]))
		{
			$sourceFields = $cache[$elementId];
		}
		elseif (Loader::includeModule('wiki'))
		{
			$res = self::$logTableClass::getList([
				'filter' => [
					'SOURCE_ID' => $elementId,
					'@EVENT_ID' => $this->getEventId(),
				],
				'select' => [ 'ID', 'URL', 'TITLE' ]
			]);
			if ($logEntryFields = $res->fetch())
			{
				$sourceFields = [
					'LOG_ID' => $logEntryFields['ID'],
					'URL' => $logEntryFields['URL']
				];

				$element = self::$wikiClass::getElementById($elementId, [
					'CHECK_PERMISSIONS' => 'N',
					'ACTIVE' => 'Y'
				]);

				if ($element)
				{
					$sourceFields = array_merge($element, $sourceFields);
				}
				else
				{
					$sourceFields['~NAME'] = htmlspecialcharsback($logEntryFields['TITLE']);
				}
			}

			$cache[$elementId] = $sourceFields;
		}

		$this->setLogId($sourceFields['LOG_ID']);
		$this->setSourceFields($sourceFields);

		$this->setSourceTitle($sourceFields['NAME']);
		if (!$wikiParser)
		{
			$wikiParser = new \CWikiParser();
		}
		$this->setSourceDescription(\CTextParser::clearAllTags(\CWikiParser::clear($wikiParser->parse($sourceFields['DETAIL_TEXT'], $sourceFields['DETAIL_TEXT_TYPE'], []))));
	}

	public function getPinnedTitle(): string
	{
		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		$sourceFields = $this->getSourceFields();

		return (
			!empty($sourceFields['ID'])
				? Loc::getMessage('SONET_LIVEFEED_WIKI_PINNED_TITLE', [
					'#TITLE#' => $sourceFields['~NAME']
				])
				: Loc::getMessage('SONET_LIVEFEED_WIKI_DELETED_PINNED_TITLE', [
				'#TITLE#' => $sourceFields['~NAME']
				])
		);
	}

	public static function canRead($params): bool
	{
		return true;
	}

	protected function getPermissions(array $post): string
	{
		return self::PERMISSION_READ;
	}

	public function getLiveFeedUrl(): string
	{
		$pathToWikiArticle = '';

		if (
			($message = $this->getSourceFields())
			&& !empty($message)
		)
		{
			$pathToWikiArticle = str_replace(
				"#GROUPS_PATH#",
				Option::get('socialnetwork', 'workgroups_page', '/workgroups/', $this->getSiteId()),
				$message['URL']
			);
		}

		return $pathToWikiArticle;
	}
}