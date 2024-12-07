<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Sync\Item;

use Bitrix\Im\Model\HrSyncQueueTable;
use Bitrix\Main\Type\DateTime;

class QueueItem
{
	public function __construct(
		public readonly int $id,
		public readonly SyncInfo $syncInfo,
		public readonly int $pointer,
		public readonly bool $isLocked,
		public readonly Status $status,
	) {}

	public static function createFromRow(array $row): self
	{
		return new static(
			(int)$row['ID'],
			SyncInfo::createFromRow($row),
			(int)$row['POINTER'],
			$row['IS_LOCKED'] === 'Y',
			Status::tryFrom($row['STATUS']) ?? Status::DEFAULT,
		);
	}

	public function lock(): self
	{
		HrSyncQueueTable::update($this->id, ['IS_LOCKED' => true, 'DATE_UPDATE' => new DateTime()]);

		return $this->copy(isLocked: true);
	}

	public function unlock(): self
	{
		HrSyncQueueTable::update($this->id, ['IS_LOCKED' => false, 'DATE_UPDATE' => new DateTime()]);

		return $this->copy(isLocked: false);
	}

	public function setErrorStatus(): self
	{
		HrSyncQueueTable::update($this->id, ['STATUS' => Status::ERROR->value, 'DATE_UPDATE' => new DateTime()]);

		return $this->copy(status: Status::ERROR);
	}

	public function updatePointer(int $pointer): self
	{
		HrSyncQueueTable::update($this->id, ['POINTER' => $pointer, 'STATUS' => Status::DEFAULT->value, 'DATE_UPDATE' => new DateTime()]);

		return $this->copy(pointer: $pointer, status: Status::DEFAULT);
	}

	protected function copy(
		?int $id = null,
		?SyncInfo $syncInfo = null,
		?int $pointer = null,
		?bool $isLocked = null,
		?Status $status = null
	): self
	{
		return new static(
			$id ?? $this->id,
			$syncInfo ?? $this->syncInfo,
			$pointer ?? $this->pointer,
			$isLocked ?? $this->isLocked,
			$status ?? $this->status,
		);
	}
}