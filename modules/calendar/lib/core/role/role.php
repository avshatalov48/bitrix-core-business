<?php

namespace Bitrix\Calendar\Core\Role;

use Bitrix\Calendar\Core\Base\BaseProperty;

class Role extends BaseProperty implements RoleEntityInterface
{
	protected RoleEntityInterface $roleEntity;

	public function __construct(RoleEntityInterface $roleEntity)
	{
		$this->roleEntity = $roleEntity;
	}

	/**
	 * @return string
	 */
	public function getFullName(): string
	{
		return $this->roleEntity->getFullName();
	}

	/**
	 * @return int|null
	 */
	public function getId(): ?int
	{
		return $this->roleEntity->getId();
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->roleEntity->getType();
	}

	/**
	 * @return RoleEntityInterface
	 */
	public function getRoleEntity(): RoleEntityInterface
	{
		return $this->roleEntity;
	}

	/**
	 * @return string[]
	 */
	public function getFields(): array
	{
		return [
			'name',
			'id',
		];
	}

	/**
	 * @return string
	 */
	public function toString(): string
	{
		return $this->getFullName();
	}
}
