<?php

namespace Bitrix\Im\V2\Integration\AI;

use Bitrix\AI\Context\Memory\Contract;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\V2\Integration\AI\Dto\File;
use Bitrix\Im\V2\Integration\AI\Dto\ForwardInfo;
use Bitrix\Im\V2\Integration\AI\Dto\Message;
use Bitrix\Im\V2;
use Bitrix\Im\V2\Message\Params;
use Bitrix\Imbot\Bot\CopilotChatBot;
use Bitrix\Main\Loader;

class HistoryBuilder implements Contract\MemoryBuilder
{
	private const DEFAULT_LIMIT = 200;
	private int $chatId;
	private int $limit = self::DEFAULT_LIMIT;
	private ?V2\MessageCollection $messages = null;
	private int $timeInterval;
	private bool $isMentionListen = true;

	public function __construct(int $chatId)
	{
		$this->chatId = $chatId;
	}

	public function setLimit(int $limit): static
	{
		$this->limit = $limit;

		return $this;
	}

	public function useMergeMode(int $timeInterval): static
	{
		$this->timeInterval = $timeInterval;

		return $this;
	}

	public function useMentionListeningMode(bool $isMentionListen = false): self
	{
		$this->isMentionListen = $isMentionListen;

		return $this;
	}

	/**
	 * @return Message[]
	 */
	public function build(): array
	{
		$messageIdsByOrder = $this->getMessageIdsByOrder();
		$history = [];

		$fetchMoreContext = count($messageIdsByOrder) === $this->limit;
		$messageIdsByOrder = $this->fillContext($messageIdsByOrder, $fetchMoreContext);

		foreach ($messageIdsByOrder as $messageId)
		{
			$message = $this->createMessageDto($messageId);
			if ($message)
			{
				$history[] = $message;
			}
		}

		return $history;
	}

	protected function fillContext(array $messageIdsByOrder, bool $fetchMoreContext = false): array
	{
		$this->loadMessages($messageIdsByOrder);
		$mergeMessageIds = $this->mergeCommonMessages($messageIdsByOrder);

		if (!$fetchMoreContext)
		{
			return $mergeMessageIds;
		}

		$this->limit = (count($messageIdsByOrder) - count($mergeMessageIds)) * 2;

		if ($this->limit === 0)
		{
			return $mergeMessageIds;
		}

		$additionalMessageIds = $this->getMessageIdsByOrder($messageIdsByOrder[count($messageIdsByOrder) - 1]);
		$this->loadMessages($additionalMessageIds);

		return array_merge($mergeMessageIds, $this->mergeCommonMessages($additionalMessageIds));
	}

	protected function filterMessages(array $messageIdsByOrder): array
	{
		foreach ($messageIdsByOrder as $key => $messageId)
		{
			$messageParams = $this->messages[$messageId]->getParams();

			if ($messageParams->isSet(Params::IS_DELETED))
			{
				unset($messageIdsByOrder[$key]);
			}

			if (
				$messageParams->isSet(Params::COMPONENT_PARAMS)
				&& isset($messageParams->get(Params::COMPONENT_PARAMS)->getValue()['COPILOT_ERROR'])
			)
			{
				unset($messageIdsByOrder[$key]);
			}

			if ($messageParams->get(Params::COMPONENT_ID)->getValue() === 'ChatCopilotCreationMessage')
			{
				unset($messageIdsByOrder[$key]);
			}

			$messageText = $this->messages[$messageId]->getMessage();
			if (!$this->isMentionListen && self::checkMessageMentions($this->chatId, $messageText))
			{
				unset($messageIdsByOrder[$key]);
			}
		}

		return $messageIdsByOrder;
	}

	protected function mergeCommonMessages(array $messageIdsByOrder): array
	{
		$messageIdsByOrder = $this->filterMessages($messageIdsByOrder);

		if (!isset($this->timeInterval))
		{
			return $messageIdsByOrder;
		}

		$userId = 0;
		$lastMessageId = 0;
		$lastKey = 0;
		foreach ($messageIdsByOrder as $key => $messageId)
		{
			$currentMessage = $this->messages[$messageId];

			if ($userId === 0)
			{
				$userId = $currentMessage->getAuthorId();
				$lastMessageId = $messageId;
				$lastKey = $key;
				continue;
			}

			$lastMessage = $this->messages[$lastMessageId];

			if (
				$currentMessage->getAuthorId() !== $userId
				|| $this->checkEntitiesInMessage($currentMessage)
				|| $this->checkEntitiesInMessage($lastMessage)
				|| ($lastMessage->getDateCreate() === null || $currentMessage->getDateCreate() === null)
				|| ($lastMessage->getDateCreate()->getTimestamp() - $currentMessage->getDateCreate()->getTimestamp()) > $this->timeInterval
			)
			{
				$userId = $currentMessage->getAuthorId();
				$lastMessageId = $messageId;
				$lastKey = $key;

				continue;
			}

			$currentMessage->setMessage($currentMessage->getMessage() . ' ' . $lastMessage->getMessage());

			unset($messageIdsByOrder[$lastKey]);

			$lastMessageId = $messageId;
			$lastKey = $key;
			$userId = $currentMessage->getAuthorId();
		}

		return $messageIdsByOrder;
	}

	protected function checkEntitiesInMessage(V2\Message $message): bool
	{
		$messageParams = $message->getParams();

		return $messageParams->isSet(Params::FORWARD_USER_ID)
			|| $messageParams->isSet(Params::REPLY_ID)
			|| $messageParams->isSet(Params::FILE_ID)
		;
	}

	protected function getMessageIdsByOrder(?int $lastMessageId = null): array
	{
		$query = MessageTable::query()
			->setSelect(['ID'])
			->where('CHAT_ID', $this->chatId)
			->withNonSystemOnly()
			->setLimit($this->limit)
			->setOffset(1)
			->setOrder(['DATE_CREATE' => 'DESC', 'ID' => 'DESC'])
		;

		if (isset($lastMessageId))
		{
			$query
				->where('ID', '<=', $lastMessageId);
		}

		return $query->fetchCollection()->getIdList();
	}

	protected function loadMessages(array $ids): void
	{
		$messages = new V2\MessageCollection($ids);
		$replayedMessageIds = $messages->getReplayedMessageIds();
		$replayedMessageIds = array_diff($replayedMessageIds, $ids);
		$replayedMessages = new V2\MessageCollection($replayedMessageIds);
		$replayedMessages->fillParams();
		$messages->merge($replayedMessages);
		$messages->fillFiles();

		if ($this->messages === null)
		{
			$this->messages = $messages;

			return;
		}

		foreach ($messages as $message)
		{
			if (isset($this->messages[$message->getMessageId()]))
			{
				continue;
			}

			$this->messages->add($message);
		}
	}

	protected function createMessageDto(int $messageId, bool $withReply = true): ?Message
	{
		$message = $this->messages[$messageId] ?? null;
		if (!$message)
		{
			return null;
		}

		$reply = $withReply ? $this->getReply($message) : null;
		$forwardInfo = $this->getForwardInfo($message);
		$files = $this->getFiles($message);

		return new Message(
			$messageId,
			$this->prepareText($message->getMessage()),
			$message->getDateCreate(),
			$message->getAuthor()?->getFullName() ?? '',
			$forwardInfo,
			$files,
			$reply,
		);
	}

	protected function prepareText(string $text): string
	{
		$text = preg_replace(
			"/-{54}\n(.[^\[\n]+)\s\[(.+?)\].+?\n(.+?)-{54}/s",
			'[QUOTE][USER]$1[/USER][DATE]$2[/DATE]$3[/QUOTE]',
			$text
		);

		return $text;
	}

	protected function getForwardInfo(V2\Message $message): ?ForwardInfo
	{
		if (!$message->getParams()->isSet(Params::FORWARD_USER_ID))
		{
			return null;
		}

		$originalAuthorId = $message->getParams()->get(Params::FORWARD_USER_ID)->getValue();
		$originalAuthor = V2\Entity\User\User::getInstance($originalAuthorId);

		return new ForwardInfo(
			$originalAuthor->getName(),
		);
	}

	protected function getReply(V2\Message $message): ?Message
	{
		$replyId = $message->getParams()->get(Params::REPLY_ID)->getValue();

		if (!$replyId)
		{
			return null;
		}

		return $this->createMessageDto($replyId, false);
	}

	protected function getFiles(V2\Message $message): array
	{
		$files = [];

		foreach ($message->getFiles() as $file)
		{
			$diskFile = $file->getDiskFile();

			if (isset($diskFile))
			{
				$files[] = new File($diskFile->getName());
			}
		}

		return $files;
	}

	protected static function checkMessageMentions(int $chatId, string $message): bool
	{
		if (!Loader::includeModule('imbot'))
		{
			return false;
		}

		$chat = V2\Chat::getInstance($chatId);
		$relations = $chat->getRelations()->getUsers();

		$forUsers = [];
		if (preg_match_all("/\[USER=([0-9]+)( REPLACE)?](.*?)\[\/USER]/i", $message, $matches))
		{
			foreach ($matches[1] as $userId)
			{
				$forUsers[(int)$userId] = (int)$userId;
			}
		}

		foreach ($relations as $relation)
		{
			if ($relation->getId() === CopilotChatBot::getBotId())
			{
				continue;
			}

			$userId = $relation->getId();

			if (in_array($userId, $forUsers, true))
			{
				return true;
			}
		}

		return false;
	}
}