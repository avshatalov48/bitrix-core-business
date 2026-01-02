<?php

namespace Bitrix\UI\AccessRights\V2\Options;

use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\UI\AccessRights\V2\Options\UserGroup\Member\Access;
use Bitrix\UI\AccessRights\V2\Options\UserGroup\Member;
use JsonSerializable;

class UserGroup implements JsonSerializable, Arrayable
{
	/** @var Access[] */
	protected array $accessRights = [];

	/** @var array<string, Member> */
	protected array $members = [];

	public function __construct(
		protected string|int $id,
		protected string $title,
	)
	{
	}

	public function getId(): int|string
	{
		return $this->id;
	}

	public function setId(int|string $id): UserGroup
	{
		$this->id = $id;

		return $this;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function setTitle(string $title): UserGroup
	{
		$this->title = $title;

		return $this;
	}

	public function getAccessRights(): array
	{
		return $this->accessRights;
	}

	public function setAccessRights(array $accessRights): static
	{
		$this->accessRights = $accessRights;

		return $this;
	}

	public function addAccessRight(Access $access): static
	{
		$this->accessRights[] = $access;

		return $this;
	}

	public function getMembers(): array
	{
		return $this->members;
	}

	/**
	 * @param array<string, Member> $members [accessCode => member]
	 * @return $this
	 */
	public function setMembers(array $members): static
	{
		$this->members = $members;

		return $this;
	}

	public function addMember(string $accessCode, Member $member): static
	{
		$this->members[$accessCode] = $member;

		return $this;
	}

	public function toArray(): array
	{
		$members = [];
		foreach ($this->members as $accessCode => $member)
		{
			$members[$accessCode] = $member->toArray();
		}

		return [
			'id' => $this->getId(),
			'title' => $this->getTitle(),
			'accessRights' => array_map(static fn (Access $access) => $access->toArray(), $this->getAccessRights()),
			'members' => $members,
		];
	}

	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
