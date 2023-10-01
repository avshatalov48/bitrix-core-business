<?php

namespace Bitrix\Forum\Integration\Search;

use Bitrix\Forum;
use Bitrix\Main;

class Topic extends Main\Update\Stepper
{
	protected static $moduleId = 'forum';

	public static function getTitle()
	{
		return 'Reindex topic';
	}

	/**
	 * @inheritDoc
	 */
	function execute(array &$option)
	{
		$storedInformation = Main\Config\Option::get('forum', 'search.reindex.topic', '');
		$topicsToReindex = [];

		if (
			!empty($storedInformation)
			&& ($topics = unserialize($storedInformation, ['allowed_classes' => false]))
			&& is_array($topics)
		)
		{
			$topicsToReindex = $topics;
		}

		if (empty($topicsToReindex) || !Main\Loader::includeModule('search'))
		{
			return self::FINISH_EXECUTION;
		}

		$state = reset($topicsToReindex);
		$topicId = $state['id'] ?? key($topicsToReindex);
		$result = self::FINISH_EXECUTION;

		if (is_array($state) && $topicId > 0)
		{
			$state['reindexFirst'] = ($state['reindexFirst'] ?? false) === true;

			if ($state['reindexFirst'] === true)
			{
				if (
					($dbRes = \CForumMessage::GetList(
						['ID' => 'ASC'],
						[
							'TOPIC_ID' => $topicId,
							'NEW_TOPIC' => 'Y',
							'GET_TOPIC_INFO' => 'Y',
							'GET_FORUM_INFO' => 'Y',
							'FILTER' => 'Y'
						]
					))
					&& ($message = $dbRes->fetch())
				)
				{
					\CForumMessage::Reindex($message['ID'], $message);
				}
			}
			else
			{
				$state['LAST_ID'] = (int) ($state['LAST_ID'] ?? 0);
				if ($state['LAST_ID'] <= 0)
				{
					\CSearch::DeleteIndex('forum', false, false, $topicId);
				}

				$limit = Main\Config\Option::get('forum', 'search_message_count', 20);
				$limit = ($limit > 0 ? $limit : 20);

				$messages = Forum\MessageTable::query()
					->setSelect(['*'])
					->addFilter('=TOPIC_ID', $topicId)
					->addFilter('>ID', $state['LAST_ID'])
					->setLimit($limit)
					->setOrder(['ID' => 'ASC'])
					->exec()
				;

				if ($message = $messages->fetch())
				{
					$forum = Forum\Forum::getById($message['FORUM_ID']);

					if ($forum['INDEXATION'] === 'Y')
					{
						$topic = Forum\Topic::getById($message['TOPIC_ID']);
						$count = 0;

						do
						{
							$count++;
							$message['FORUM_INFO'] = $forum->getData();
							$message['TOPIC_INFO'] = $topic->getData();

							\CForumMessage::Reindex($message['ID'], $message);

							$state['LAST_ID'] = $message['ID'];

						} while ($message = $messages->fetch());

						if ($count >= $limit)
						{
							$result = self::CONTINUE_EXECUTION;
						}
					}
				}
			}
		}

		if ($result === self::FINISH_EXECUTION)
		{
			array_shift($topicsToReindex);
		}
		else
		{
			$topicsToReindex[$topicId] = $state;
		}
		$option['steps'] = 1;
		$option['count'] = count($topicsToReindex);

		if (empty($topicsToReindex))
		{
			Main\Config\Option::delete('forum', ['name' => 'search.reindex.topic']);
			return self::FINISH_EXECUTION;
		}
		Main\Config\Option::set('forum', 'search.reindex.topic', serialize($topicsToReindex));
		return self::CONTINUE_EXECUTION;
	}

	public static function reindexFirstMessage(int $topicId)
	{
		static::reindex($topicId, true);
	}

	public static function reindex(int $topicId, bool $reindexOnlyFirstMessage = false)
	{
		$storedInformation = Main\Config\Option::get('forum', 'search.reindex.topic', '');
		$topicsToReindex = [];

		if (
			!empty($storedInformation)
			&& ($res = unserialize($storedInformation, ['allowed_classes' => false]))
			&& is_array($res)
		)
		{
			$topicsToReindex = $res;
		}

		$topicsToReindex[$topicId] = ['id' => $topicId] + ($reindexOnlyFirstMessage === true ? ['reindexFirst' => true] : []);

		Main\Config\Option::set('forum', 'search.reindex.topic', serialize($topicsToReindex));
		static::bind(0);
	}

	public static function deleteIndex(Forum\Topic $topic)
	{
		if (IsModuleInstalled('search') && \CModule::IncludeModule('search'))
		{
			\CSearch::DeleteIndex('forum', false, $topic['FORUM_ID'], $topic['ID']);
		}
	}
}
