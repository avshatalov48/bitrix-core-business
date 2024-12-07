<?php

namespace Bitrix\Socialnetwork\Integration\AI;

use Bitrix\AI\Context;
use Bitrix\AI\Engine;
use Bitrix\Forum\MessageTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Integration\AI\User\Author;
use Bitrix\Socialnetwork\Livefeed\BlogPost;

final class Controller
{
	private static array $listMessages = [];
	private static int $blogAuthorId = 0;
	private const LIMIT = 20;

	public static function onContextGetMessages(Event $event): array
	{
		$moduleId = $event->getParameter('module');
		$contextId = $event->getParameter('id');
		$contextParameters = $event->getParameter('params');
		$nextStep = $event->getParameter('next_step');

		$isCommentContext = (
			$moduleId === 'socialnetwork'
			&& str_starts_with($contextId, 'sonet_comment_')
		);
		if ($isCommentContext)
		{
			$messages = [];

			$xmlId = is_string($contextParameters['xmlId'] ?? null) ? $contextParameters['xmlId'] : null;
			if (!$xmlId)
			{
				return ['messages' => []];
			}

			if (isset(self::$listMessages[$xmlId]))
			{
				return ['messages' => self::$listMessages[$xmlId]];
			}

			if (str_starts_with($xmlId, 'BLOG_') && Loader::includeModule('blog'))
			{
				$postId = (int) mb_substr($xmlId, 5);
				$postMessages = self::getPostContext($postId);
				foreach ($postMessages as $postMessage)
				{
					$messages[] = ['content' => $postMessage];
				}
			}
			else if (
				str_starts_with($xmlId, 'TASK_')
				&& Loader::includeModule('tasks')
				&& Loader::includeModule('forum')
			)
			{
				$postMessages = self::getTaskContext($xmlId);
				foreach ($postMessages as $postMessage)
				{
					$messages[] = ['content' => $postMessage];
				}
			}

			$messages[0] = self::modifyOriginalMessage($messages[0] ?? []);

			if ($messages)
			{
				self::$listMessages[$xmlId] = $messages;
			}

			return ['messages' => $messages];
		}

		return ['messages' => []];
	}

	private static function getPostContext(int $postId): array
	{
		$messages = [];

		$textParser = new \CTextParser();

		$post = \CBlogPost::getByID($postId);
		if (!BlogPost::canRead(['POST' => $post]))
		{
			return [];
		}

		if ($post)
		{
			self::setBlogAuthorId((int)$post['AUTHOR_ID']);

			$messages[] = $textParser->clearAllTags($post['DETAIL_TEXT']);

			$comments = self::getLastComments($postId);

			$messages = array_merge($messages, $comments);
		}

		return $messages;
	}

	public static function getTaskContext(string $xmlId): array
	{
		$taskId = (int) mb_substr($xmlId, 5);

		$textParser = new \CTextParser();

		$messages = [];

		$task = \Bitrix\Tasks\Internals\Registry\TaskRegistry::getInstance()->getObject($taskId);
		self::setBlogAuthorId((int)$task->getCreatedBy());
		$messages[] = $textParser->clearAllTags($task->getDescription());

		$liveFeedEntity = \Bitrix\Socialnetwork\Livefeed\Provider::init([
			'ENTITY_TYPE' => \Bitrix\Socialnetwork\Livefeed\Provider::DATA_ENTITY_TYPE_TASKS_TASK,
			'ENTITY_ID' => $taskId,
		]);
		if ($liveFeedEntity)
		{
			$logId = (int) $liveFeedEntity->getLogId();
			if ($logId)
			{
				$comments = self::getForumComments($xmlId);

				$messages = array_merge($messages, array_reverse($comments));
			}
		}

		return $messages;
	}

	private static function getForumComments(string $xmlId): array
	{
		$textParser = new \CTextParser();

		$comments = [];

		$query = MessageTable::query();
		$query
			->setSelect(['ID', 'POST_MESSAGE'])
			->where('XML_ID', $xmlId)
			->whereNull('SERVICE_TYPE')
			->whereNull('PARAM1')
			->setOrder(['POST_DATE' => 'desc'])
			->setLimit(self::LIMIT);

		$postMessages = $query->exec()->fetchCollection();
		foreach ($postMessages as $postMessage)
		{
			$comments[] = $textParser->clearAllTags($postMessage->getPostMessage());
		}

		return $comments;
	}

	private static function getLastComments(int $postId): array
	{
		$textParser = new \CTextParser();

		$comments = [];

		$queryCommentObject = \CBlogComment::getList(
			['ID' => 'DESC'],
			[
				'PUBLISH_STATUS' => BLOG_PUBLISH_STATUS_PUBLISH,
				'POST_ID' => $postId,
				'!=POST_TEXT' => \Bitrix\Socialnetwork\CommentAux\TaskInfo::POST_TEXT,
			],
			false,
			[
				'nTopCount' => self::LIMIT
			],
			['POST_TEXT']
		);
		while ($commentData = $queryCommentObject->fetch())
		{
			$comments[] = $textParser->clearAllTags($commentData['POST_TEXT']);
		}

		return $comments;
	}

	private static function modifyOriginalMessage(array $message): array
	{
		$message['is_original_message'] = true;

		if (self::$blogAuthorId)
		{
			$author = new Author(self::$blogAuthorId);
			$message['meta'] = $author->toMeta();
		}

		return $message;
	}

	private static function setBlogAuthorId(int $userId): void
	{
		self::$blogAuthorId = $userId;
	}
}
