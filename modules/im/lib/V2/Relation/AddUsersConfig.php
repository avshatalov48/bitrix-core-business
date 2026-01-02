<?php

namespace Bitrix\Im\V2\Relation;

final class AddUsersConfig
{
	public readonly array $managerIds;
	public readonly ?bool $hideHistory;
	public readonly bool $withMessage;
	public readonly bool $skipRecent;
	public readonly bool $isFakeAdd;
	public readonly Reason $reason;
	public readonly bool $skipAnalytics;
	public readonly array $hiddenUserIds;

	public function __construct(
		array $managerIds = [],
		?bool $hideHistory = null,
		bool $withMessage = true,
		bool $skipRecent = false,
		bool $isFakeAdd = false,
		Reason $reason = Reason::DEFAULT,
		array $hiddenUserIds = [],
		bool $skipAnalytics = true,
	)
	{
		$this->managerIds = $this->normalizeIds($managerIds);
		$this->hideHistory = $hideHistory;
		$this->withMessage = $withMessage;
		$this->skipRecent = $skipRecent;
		$this->isFakeAdd = $isFakeAdd;
		$this->reason = $reason;
		$this->hiddenUserIds = $this->normalizeIds($hiddenUserIds);
		$this->skipAnalytics = $skipAnalytics;
	}

	public function isManager(int $userId): bool
	{
		return isset($this->managerIds[$userId]);
	}

	/**
	 * @param int[] $ids
	 * @return self
	 */
	public function addHiddenUserIds(array $ids): self
	{
		$hiddenUserIds = $this->hiddenUserIds;
		$hiddenUserIds += $this->normalizeIds($ids);

		return $this->with(['hiddenUserIds' => $hiddenUserIds]);
	}

	/**
	 * @param int[] $managerIds
	 * @return self
	 */
	public function setManagerIds(array $managerIds): self
	{
		return $this->with(['managerIds' => $managerIds]);
	}

	public function setHideHistory(?bool $hideHistory): self
	{
		return $this->with(['hideHistory' => $hideHistory]);
	}

	public function isHidden(int $userId): bool
	{
		return isset($this->hiddenUserIds[$userId]);
	}

	private function normalizeIds(array $ids): array
	{
		$intIds = array_map('intval', $ids);

		return array_combine($intIds, $intIds);
	}

	private function with(array $changes): self
	{
		$newFields = array_merge($this->toArray(), $changes);

		return new self(...$newFields);
	}

	private function toArray(): array
	{
		$array = [];
		foreach ($this as $fieldName => $field)
		{
			$array[$fieldName] = $field;
		}

		return $array;
	}
}
