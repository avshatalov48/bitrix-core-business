<?php
namespace Bitrix\Landing\Note\Source;

use \Bitrix\Socialnetwork\Livefeed\BlogPost;

class Blog extends Entity
{
	/**
	 * Returns prepared data for landing by entity (post) id.
	 * @param int $sourceId Source id.
	 * @return array|null
	 */
	public static function getData(int $sourceId): ?array
	{
		if (
			\Bitrix\Main\Loader::includeModule('blog') &&
			\Bitrix\Main\Loader::includeModule('socialnetwork')
		)
		{
			$post = \CBlogPost::getByID($sourceId);
			if (BlogPost::canRead(['POST' => $post]))
			{
				$params = [];
				$blocks = [[
					'type' => 'header',
					'content' => $post['TITLE']
				]];
				if (\Bitrix\Main\Loader::includeModule('disk'))
				{
					$params = [
						'files' => self::getDiskFiles(
							$sourceId,
							\Bitrix\Disk\Uf\BlogPostConnector::class,
							'blog'
						)
					];
				}
				$blocks = array_merge(
					$blocks,
					Parser::textToBlocks($post['DETAIL_TEXT'], $params)
				);
				return [
					'TITLE' => \truncateText($post['TITLE'], self::TITLE_LENGTH),
					'BLOCKS' => $blocks
				];
			}
		}

		return null;
	}
}