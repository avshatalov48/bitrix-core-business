<?php
namespace Bitrix\Landing\Note\Source;

use \Bitrix\Forum\MessageTable;
use \Bitrix\Socialnetwork\Livefeed as SonetLivefeed;

class LiveFeed extends Entity
{
	/**
	 * Returns prepared data for landing by entity ('live feed' comment) id.
	 * @param int $sourceId Source id.
	 * @return array|null
	 */
	public static function getData(int $sourceId): ?array
	{
		if (
			\Bitrix\Main\Loader::includeModule('socialnetwork') &&
			\Bitrix\Main\Loader::includeModule('forum')
		)
		{
			$provider = SonetLivefeed\Provider::init([
				'ENTITY_TYPE' => 'FORUM_POST',
				'ENTITY_ID' => $sourceId,
				'CLONE_DISK_OBJECTS' => false
			]);
			if (!$provider || !$provider->getSourceTitle())
			{
				return null;
			}
			$res = MessageTable::getList([
				'select' => [
					'POST_MESSAGE',
					'TOPIC_TITLE' => 'TOPIC.TITLE'
				],
				'filter' => [
					'ID' => $sourceId,
					'=APPROVED' => 'Y'
				],
				'limit' => 1
			]);
			if ($comment = $res->fetch())
			{
				$title = $provider->getSourceTitle();
				$title = preg_replace('/\[[^\]]+\]/is', '', $title);
				$params = [];
				$blocks = [[
					'type' => 'header',
					'content' => $title
				]];
				if (\Bitrix\Main\Loader::includeModule('disk'))
				{
					$params = [
						'files' => self::getDiskFiles(
							$sourceId,
							\Bitrix\Disk\Uf\ForumMessageConnector::class,
							'forum'
						)
					];
				}
				$blocks = array_merge(
					$blocks,
					Parser::textToBlocks($comment['POST_MESSAGE'], $params)
				);
				return [
					'TITLE' => \truncateText($title, self::TITLE_LENGTH),
					'BLOCKS' => $blocks
				];
			}
		}

		return null;
	}
}