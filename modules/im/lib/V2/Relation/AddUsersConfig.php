<?php

namespace Bitrix\Im\V2\Relation;

class AddUsersConfig
{
	protected array $managerIds = [];

	public function __construct(
		array $managerIds = [],
		protected ?bool $hideHistory = null,
		protected bool $withMessage = true,
		protected bool $skipRecent = false,
		protected bool $isFakeAdd = false,
		protected Reason $reason = Reason::DEFAULT,
	)
	{
		$this->setManagerIds($managerIds);
	}

	public function isManager(int $userId): bool
	{
		return isset($this->managerIds[$userId]);
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

	public function skipRecent(): bool
	{
		return $this->skipRecent;
	}

	public function isFakeAdd(): bool
	{
		return $this->isFakeAdd;
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
