<?
/**
 * @access private
 */

namespace Bitrix\Wiki\Integration\SocialNetwork;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Socialnetwork\Item\LogIndex;

class Log
{
	const EVENT_ID_WIKI = 'wiki';

	/**
	 * Returns set EVENT_ID processed by handler to generate content for full index.
	 *
	 * @param void
	 * @return array
	 */
	public static function getEventIdList()
	{
		return array(
			self::EVENT_ID_WIKI
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
		static $wikiParser = null;

		$result = new EventResult(
			EventResult::UNDEFINED,
			array(),
			'wiki'
		);

		$eventId = $event->getParameter('eventId');
		$sourceId = $event->getParameter('sourceId');

		if (!in_array($eventId, self::getEventIdList()))
		{
			return $result;
		}

		$content = "";
		$element = false;

		if ((int)($sourceId) > 0)
		{
			$element = \CWiki::getElementById($sourceId, array(
				'CHECK_PERMISSIONS' => 'N',
				'ACTIVE' => 'Y'
			));
		}

		if ($element)
		{
			if (!$wikiParser)
			{
				$wikiParser = new \CWikiParser();
			}

			$element['DETAIL_TEXT'] = $wikiParser->parse($element['DETAIL_TEXT'], $element['DETAIL_TEXT_TYPE'], array());
			$element['DETAIL_TEXT'] = \CWikiParser::clear($element['DETAIL_TEXT']);

			$content .= LogIndex::getUserName($element["CREATED_BY"])." ";
			$content .= $element['NAME']." ";
			$content .= \CTextParser::clearAllTags($element['DETAIL_TEXT']);

			if (
				!empty($element['_TAGS'])
				&& is_array($element['_TAGS'])
			)
			{
				$tagList = [];
				foreach($element['_TAGS'] as $tag)
				{
					$tagList[] = $tag["NAME"];
					$tagList[] = '#'.$tag["NAME"];
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
			'wiki'
		);

		return $result;
	}


}