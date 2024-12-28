<?php

namespace Bitrix\Im\V2\Relation;

class AddUsersConfig
{
	private array $managerIds = [];
	private ?bool $hideHistory;
	private bool $withMessage;
	private bool $skipRecent;
	private bool $isFakeAdd;
	private Reason $reason;

	public function __construct(
		array $managerIds = [],
		?bool $hideHistory = null,
		bool $withMessage = true,
		bool $skipRecent = false,
		bool $isFakeAdd = false,
		Reason $reason = Reason::DEFAULT,
	)
	{
		$this->setManagerIds($managerIds);
		$this->hideHistory = $hideHistory;
		$this->withMessage = $withMessage;
		$this->skipRecent = $skipRecent;
		$this->isFakeAdd = $isFakeAdd;
		$this->reason = $reason;
	}

	public function isManager(int $userId): bool
	{
		return isset($this->managerIds[$userId]);
	}

	public function getManagerIds(): array
	{
		return $this->managerIds;
	}

	public function setManagerIds(array $managerIds): AddUsersConfig
	{
		foreach ($managerIds as $managerId)
		{
			$this->managerIds[$managerId] = $managerId;
		}

		return $this;
	}

	public function isHideHistory(): ?bool
	{
		return $this->hideHistory;
	}

	public function setHideHistory(?bool $hideHistory): AddUsersConfig
	{
		$this->hideHistory = $hideHistory;
		return $this;
	}

	public function withMessage(): bool
	{
		return $this->withMessage;
	}

	public function setWithMessage(bool $withMessage): AddUsersConfig
	{
		$this->withMessage = $withMessage;
		return $this;
	}

	public function skipRecent(): bool
	{
		return $this->skipRecent;
	}

	public function setSkipRecent(bool $skipRecent): AddUsersConfig
	{
		$this->skipRecent = $skipRecent;
		return $this;
	}

	public function isFakeAdd(): bool
	{
		return $this->isFakeAdd;
	}

	public function setIsFakeAdd(bool $isFakeAdd): AddUsersConfig
	{
		$this->isFakeAdd = $isFakeAdd;
		return $this;
	}

	public function getReason(): Reason
	{
		return $this->reason;
	}

	public function setReason(Reason $reason): AddUsersConfig
	{
		$this->reason = $reason;
		return $this;
	}
}
