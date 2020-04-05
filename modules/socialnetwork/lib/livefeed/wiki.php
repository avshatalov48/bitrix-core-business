<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\LogTable;

final class Wiki extends Provider
{
	const PROVIDER_ID = 'WIKI';
	const CONTENT_TYPE_ID = 'WIKI';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('wiki');
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
		static $wikiParser = false;

		$elementId = $this->entityId;

		if (
			$elementId > 0
			&& Loader::includeModule('wiki')
		)
		{
			$element = \CWiki::getElementById($elementId, array(
				'CHECK_PERMISSIONS' => 'N',
				'ACTIVE' => 'Y'
			));

			if ($element)
			{
				$sourceFields = $element;

				$res = LogTable::getList(array(
					'filter' => array(
						'SOURCE_ID' => $elementId,
						'@EVENT_ID' => $this->getEventId(),
					),
					'select' => array('ID', 'URL')
				));
				if ($logEntryFields = $res->fetch())
				{
					$sourceFields = array_merge($element, array('URL' => $logEntryFields['URL']));
					$this->setLogId($logEntryFields['ID']);
				}
				$this->setSourceFields($sourceFields);

				$this->setSourceTitle($element['NAME']);
				if (!$wikiParser)
				{
					$wikiParser = new \CWikiParser();
				}
				$this->setSourceDescription(\CTextParser::clearAllTags($wikiParser->clear($wikiParser->parse($element['DETAIL_TEXT'], $element['DETAIL_TEXT_TYPE'], array()))));
			}
		}
	}

	public static function canRead($params)
	{
		return true;
	}

	protected function getPermissions(array $post)
	{
		$result = self::PERMISSION_READ;

		return $result;
	}

	public function getLiveFeedUrl()
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