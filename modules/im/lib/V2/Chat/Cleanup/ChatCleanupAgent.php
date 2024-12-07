<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Chat\Cleanup;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CAgent;

final class ChatCleanupAgent
{
	protected const CLEANUP_LIMIT = 1000;

	protected static function formatCleanupAgentName(
		int $chatId,
		int $currentChatId,
		?string $prevCompletedStep = null,
		?int $userId = null,
	): string
	{
		$params = [
			$chatId,
			$currentChatId,
		];

		if (null !== $prevCompletedStep)
		{
			$params[] = '\'' . $prevCompletedStep . '\'';
		}
		else
		{
			$params[] = 'null';
		}

		if (null !== $userId)
		{
			$params[] = $userId;
		}

		return __CLASS__ . '::processCleanup(' . implode(', ', $params) . ');';
	}

	protected static function formatChatAgentName(
		int $chatId,
		int $currentChatId,
		?int $userId = null,
	): string
	{
		$params = [$chatId, $currentChatId];

		if (null !== $userId)
		{
			$params[] = $userId;
		}

		return __CLASS__ . '::processChat(' . implode(', ', $params) . ');';
	}

	public static function register(
		int $chatId,
		?int $currentChatId = null,
		?int $userId = null,
	): void
	{
		if (null === $currentChatId)
		{
			$currentChatId = $chatId;
		}

		CAgent::addAgent(
			self::formatChatAgentName(
				$chatId,
				$currentChatId,
				$userId,
			),
			'im',
			'N',
			60,
		);
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public static function processCleanup(
		int $chatId,
		int $currentChatId, ?
		string $prevCompletedStep = null,
		?int $userId = null,
	): string
	{
		$limit = intval(Option::get('im', 'chat_cleanup_limit', self::CLEANUP_LIMIT));
		$collector = new ChatContentCollector($currentChatId);
		$nextCompletedStep = $collector->processCleanup($limit, $prevCompletedStep);

		if (null === $nextCompletedStep)
		{
			$nextChatId = (new ChatContentCollector($chatId))->getNextChatIdToCleanup();

			if (null === $nextChatId)
			{
				return '';
			}

			return self::formatChatAgentName(
				$chatId,
				$nextChatId,
				$userId,
			);
		}

		return self::formatCleanupAgentName(
			$chatId,
			$currentChatId,
			$nextCompletedStep,
			$userId,
		);
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public static function processChat(
		int $chatId,
		int $currentChatId,
		?int $userId = null,
	): string
	{
		if ($chatId !== $currentChatId)
		{
			(new ChatContentCollector($currentChatId))->cleanupChatBasics($userId);
		}

		return self::formatCleanupAgentName(
			$chatId,
			$currentChatId,
			userId: $userId,
		);
	}
}