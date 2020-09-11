<?php
namespace Bitrix\Landing\Note\Source;

use \Bitrix\Socialnetwork\Livefeed\BlogPost;

class BlogComment extends Entity
{
	/**
	 * Returns prepared data for landing by entity (blog comment) id.
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
			$comment = \CBlogComment::getByID($sourceId);
			if (!$comment || $comment['PUBLISH_STATUS'] != BLOG_PUBLISH_STATUS_PUBLISH)
			{
				return null;
			}
			$post = \CBlogPost::getByID($comment['POST_ID']);
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
							\Bitrix\Disk\Uf\BlogPostCommentConnector::class,
							'blog'
						)
					];
				}
				$blocks = array_merge(
					$blocks,
					Parser::textToBlocks($comment['POST_TEXT'], $params)
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