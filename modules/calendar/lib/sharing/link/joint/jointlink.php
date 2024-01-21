<?php

namespace Bitrix\Calendar\Sharing\Link\Joint;

use Bitrix\Calendar\Sharing\Link\Link;
use Bitrix\Calendar\Sharing\Link\Member\Member;

abstract class JointLink extends Link
{
	/** @var array<Member> $members */
	private array $members = [];
	/** @var string|null $membersHash */
	private ?string $membersHash = null;

	public function getMembers(): array
	{
		return $this->members;
	}

	public function setMembers(array $members): self
	{
		foreach ($members as $key => $value)
		{
			if (!($value instanceof Member) || $value->getId() === $this->getOwnerId())
			{
				unset($members[$key]);
			}
		}
		$this->members = $members;

		return $this;
	}

	public function getMembersHash(): ?string
	{
		return $this->membersHash;
	}

	public function setMembersHash(?string $membersHash): static
	{
		$this->membersHash = $membersHash;

		return $this;
	}

	public function isJoint(): bool
	{
		return !empty($this->members);
	}

	abstract public function getOwnerId(): int;
}