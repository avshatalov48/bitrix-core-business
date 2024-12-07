<?php

namespace Bitrix\Im\V2\Message;

use Bitrix\Im\Model\LastMessageTable;
use Bitrix\Im\V2\Message;

class LastMessages
{
	protected const COUNT_OF_LAST_USERS = 3;
	protected const LIMIT_DELETE_BATCH = 20;

	private array $chatIds;
	private ?array $lastMessages = null;

	public function __construct(array $chatIds)
	{
		$this->chatIds = $chatIds;
	}

	public static function insert(Message $message): void
	{
		if ($message->getAuthorId() === 0)
		{
			return;
		}

		LastMessageTable::merge(
			[
				'USER_ID' => $message->getAuthorId(),
				'CHAT_ID' => $message->getChatId(),
				'MESSAGE_ID' => $message->getId(),
				'DATE_CREATE' => $message->getDateCreate(),
			],
			[
				'MESSAGE_ID' => $message->getId(),
				'DATE_CREATE' => $message->getDateCreate(),
			],
			['CHAT_ID', 'USER_ID']
		);

		static::deleteExtra($message->getChatId());
	}

	public function getUsersByChatId(int $chatId): array
	{
		$this->fill();

		return $this->lastMessages[$chatId]['USERS'] ?? [];
	}

	protected function fill(): void
	{
		if (isset($this->lastMessages))
		{
			return;
		}

		$this->lastMessages = [];

		if (empty($this->chatIds))
		{
			return;
		}

		$raw = LastMessageTable::query()
			->setSelect(['USER_ID', 'CHAT_ID', 'MESSAGE_ID', 'DATE_CREATE'])
			->whereIn('CHAT_ID', $this->chatIds)
			->setOrder(['DATE_CREATE' => 'DESC'])
			->fetchAll()
		;

		foreach ($raw as $row)
		{
			$chatId = (int)$row['CHAT_ID'];
			if (count($this->lastMessages[$chatId]['USERS'] ?? []) < self::COUNT_OF_LAST_USERS)
			{
				$this->lastMessages[$chatId]['USERS'][] = (int)$row['USER_ID'];
			}
		}
	}

	protected static function deleteExtra(int $chatId): void
	{
		$extraIds = LastMessageTable::query()
			->setSelect(['ID'])
			->where('CHAT_ID', $chatId)
			->setOrder(['DATE_CREATE' => 'DESC'])
			->setLimit(self::LIMIT_DELETE_BATCH)
			->setOffset(self::COUNT_OF_LAST_USERS)
			->fetchCollection()
			->getIdList()
		;

		if (empty($extraIds))
		{
			return;
		}

		LastMessageTable::deleteByFilter(['=ID' => $extraIds]);
	}
}