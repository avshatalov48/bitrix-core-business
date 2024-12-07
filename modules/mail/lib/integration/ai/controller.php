<?php

namespace Bitrix\Mail\Integration\AI;

use Bitrix\Mail\MailMessageTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Event;
use Bitrix\Mail\Helper\Message\MessageThreadLoader;
use Bitrix\Mail;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

final class Controller
{

	public static function onContextGetMessages(Event $event): array
	{
		$moduleId = $event->getParameter('module');
		$contextId = $event->getParameter('id');
		$contextParameters = $event->getParameter('params');

		$isAddedQuote = filter_var(($contextParameters['isAddedQuote'] ?? null), FILTER_VALIDATE_BOOLEAN);
		$messageId = $contextParameters['messageId'];
		$messageIds = $contextParameters['messageIds'];

		if (!$moduleId || !$contextId)
		{
			return ['messages' => []];
		}

		if (!self::isNeededMailMessageContext($moduleId, $contextId, $isAddedQuote, $messageId, $messageIds))
		{
			return ['messages' => []];
		}

		if (!$messageIds)
		{
			$messageThreadLoader = new MessageThreadLoader($messageId);
			$messageThreadLoader->loadFullThreadMessageIds();
			$messageIds = $messageThreadLoader->getThreadMessageIds();
		}

		if (!$messageIds)
		{
			return ['messages' => []];
		}

		return self::loadMessages($messageIds);
	}

	private static function isNeededMailMessageContext(
		string $moduleId,
		string $contextId,
		bool $isAddedQuote,
		?int $messageId = null,
		?array $messageIds = null,
	): bool
	{
		if ($moduleId !== 'mail' || $isAddedQuote !== false)
		{
			return false;
		}

		if (!str_starts_with($contextId, 'mail_reply') && !str_starts_with($contextId, 'crm_mail_reply'))
		{
			return false;
		}

		if ($messageId < 0 && empty($messageIds))
		{
			return false;
		}

		return true;
	}

	/**
	 * @param int[] $messageIds
	 * @return array
	 */
	private static function loadMessages(array $messageIds): array
	{
		$userId = CurrentUser::get()->getId();
		$messageIds = array_filter($messageIds, function ($item) {
			return filter_var($item, FILTER_VALIDATE_INT) !== false;
		});

		if (empty($messageIds))
		{
			return ['messages' => []];
		}

		$message = MailMessageTable::query()
			->setSelect(['*'])
			->where('ID', end($messageIds))
			->exec()
			->fetch()
		;

		if (!$message || !Mail\Helper\Message::hasAccess($message, $userId))
		{
			return ['messages' => []];
		}

		$messages[] = [
			'content' => $message['BODY']]
		;

		return [
			'messages' => $messages,
		];
	}
}