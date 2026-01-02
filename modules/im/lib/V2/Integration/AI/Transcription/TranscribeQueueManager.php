<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Integration\AI\Transcription;

use Bitrix\Im\Model\AiTranscribeQueueTable;

class TranscribeQueueManager
{
	private static ?self $instance = null;

	private function __construct() {}

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function inQueue(int $fileId, int $chatId): bool
	{
		$result = AiTranscribeQueueTable::query()
			->setSelect(['ID'])
			->where('FILE_ID', $fileId)
			->where('CHAT_ID', $chatId)
			->fetch()
		;

		return $result !== false;
	}

	public function fetchChatIds(int $fileId): array
	{
		$result = AiTranscribeQueueTable::query()
			->setSelect(['CHAT_ID'])
			->where('FILE_ID', $fileId)
			->fetchAll()
		;

		$chatIds = [];
		foreach ($result as $item)
		{
			$chatIds[$item['CHAT_ID']] = (int)$item['CHAT_ID'];
		}

		return $chatIds;
	}

	public function add(int $fileId, int $chatId): void
	{
		AiTranscribeQueueTable::multiplyInsertWithoutDuplicate(
			[['FILE_ID' => $fileId, 'CHAT_ID' => $chatId]],
			['DEADLOCK_SAFE' => true, 'UNIQUE_FIELDS' => ['FILE_ID', 'CHAT_ID']]
		);
	}

	public function delete(int $fileId, ?int $chatId = null): void
	{
		$filter = ['=FILE_ID' => $fileId];

		if (isset($chatId))
		{
			$filter['=CHAT_ID'] = $chatId;
		}

		AiTranscribeQueueTable::deleteBatch($filter);
	}
}
