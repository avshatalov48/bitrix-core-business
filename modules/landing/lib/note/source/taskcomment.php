<?php
namespace Bitrix\Landing\Note\Source;

use \Bitrix\Forum\MessageTable;

class TaskComment extends Entity
{
	/**
	 * Returns prepared data for landing by entity (task comment) id.
	 * @param int $sourceId Source id.
	 * @return array|null
	 */
	public static function getData(int $sourceId): ?array
	{
		if (
			\Bitrix\Main\Loader::includeModule('tasks') &&
			\Bitrix\Main\Loader::includeModule('forum')
		)
		{
			$res = MessageTable::getList([
				'select' => [
					'TOPIC_ID', 'POST_MESSAGE'
				],
				'filter' => [
					'ID' => $sourceId,
					'=APPROVED' => 'Y'
				],
				'limit' => 1
			]);
			if ($comment = $res->fetch())
			{
				[$tasks, ] = \CTaskItem::fetchList(
					\Bitrix\Landing\Manager::getUserId(),
					[],
					['FORUM_TOPIC_ID' => $comment['TOPIC_ID']]
				);
				if ($tasks)
				{
					$params = [];
					$taskData = $tasks[0]->getData();
					$blocks = [[
						'type' => 'header',
						'content' => $taskData['TITLE']
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
						'TITLE' => \truncateText($taskData['TITLE'], self::TITLE_LENGTH),
						'BLOCKS' => $blocks
					];
				}
			}
		}

		return null;
	}
}