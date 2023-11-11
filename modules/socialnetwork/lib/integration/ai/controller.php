<?php

namespace Bitrix\Socialnetwork\Integration\AI;

use Bitrix\AI\Context;
use Bitrix\AI\Engine;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;

final class Controller
{
	private static array $listMessages = [];

	const TEXT_CATEGORY = 'text';
	const IMAGE_CATEGORY = 'image';

	public static function isAvailable(string $category, string $contextId = ''): bool
	{
		if (!Loader::includeModule('ai'))
		{
			return false;
		}

		$engine = Engine::getByCategory($category, new Context('socialnetwork', $contextId));
		if (is_null($engine))
		{
			return false;
		}

		return Option::get('socialnetwork', 'ai_base_enabled', 'N') === 'Y';
	}

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

			if (str_starts_with($xmlId, 'BLOG_'))
			{
				$blogId = (int) mb_substr($xmlId, 5);
				$postMessages = self::getPostContext($blogId);
				foreach ($postMessages as $postMessage)
				{
					$messages[] = ['content' => $postMessage];
				}
			}
			else if (str_starts_with($xmlId, 'TASK_') && Loader::includeModule('tasks'))
			{
				$taskId = (int) mb_substr($xmlId, 5);
				$postMessages = self::getTaskContext($taskId);
				foreach ($postMessages as $postMessage)
				{
					$messages[] = ['content' => $postMessage];
				}
			}

			$messages[0]['is_original_message'] = true;

			if ($messages)
			{
				self::$listMessages[$xmlId] = $messages;
			}

			return ['messages' => $messages];
		}

		return ['messages' => []];
	}

	private static function getPostContext(int $blogId): array
	{
		$messages = [];

		$provider = new \Bitrix\Socialnetwork\Livefeed\BlogPost();

		$textParser = new \CTextParser();

		$queryPostObject = \CSocNetLog::getList(
			[],
			[
				'EVENT_ID' => $provider->getEventId(),
				'SOURCE_ID' => $blogId,
			],
			false,
			false,
			['ID', 'TEXT_MESSAGE'],
		);
		if ($logData = $queryPostObject->fetch())
		{
			$logId = (int) $logData['ID'];

			$messages[] = $textParser->clearAllTags($logData['TEXT_MESSAGE']);

			$comments = self::getLastComments($logId);

			$messages = array_merge($messages, $comments);
		}

		return $messages;
	}

	private static function getTaskContext(int $taskId): array
	{
		$textParser = new \CTextParser();

		$messages = [];

		$task = \Bitrix\Tasks\Internals\Registry\TaskRegistry::getInstance()->getObject($taskId);
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
				$comments = self::getLastComments($logId);

				$messages = array_merge($messages, array_reverse($comments));
			}
		}

		return $messages;
	}

	private static function getLastComments(int $logId, int $limit = 10): array
	{
		$textParser = new \CTextParser();

		$comments = [];

		$queryCommentObject = \CSocNetLogComments::getList(
			['ID' => 'DESC'],
			[
				'LOG_ID' => $logId,
				'!=MESSAGE' => \Bitrix\Socialnetwork\CommentAux\TaskInfo::POST_TEXT,
			],
			false,
			['nTopCount' => $limit],
			['TEXT_MESSAGE']
		);
		while ($logCommentData = $queryCommentObject->fetch())
		{
			$comments[] = $textParser->clearAllTags($logCommentData['TEXT_MESSAGE']);
		}

		return $comments;
	}
}