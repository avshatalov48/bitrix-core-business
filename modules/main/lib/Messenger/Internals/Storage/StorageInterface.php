<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Storage;

use Bitrix\Main\Messenger\Entity\MessageBox;

interface StorageInterface
{
	public function getOneByQueue(string $queueId): ?MessageBox;

	public function getReadyMessagesOfQueue(string $queueId, int $limit = 50): iterable;

	public function save(MessageBox $messageBox): void;

	public function delete(MessageBox $messageBox): void;
}
