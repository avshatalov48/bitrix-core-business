<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control;

use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Item\Workgroup;

class GroupResult extends Result
{
	public function getGroup(): ?Workgroup
	{
		return $this->data['group'] ?? null;
	}

	public function getGroupId(): ?int
	{
		return $this->data['groupId'] ?? null;
	}

	public function setGroup(?Workgroup $group): static
	{
		$this->data['group'] = $group;
		$this->data['groupId'] = $group?->getId();

		return $this;
	}

	public function setGroupId(?int $groupId): static
	{
		$this->data['group'] = null;
		$this->data['groupId'] = $groupId;

		return $this;
	}
}