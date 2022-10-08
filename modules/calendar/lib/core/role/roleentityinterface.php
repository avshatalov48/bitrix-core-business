<?php

namespace Bitrix\Calendar\Core\Role;

interface RoleEntityInterface
{
	public function getFullName(): string;

	public function getId(): ?int;

	public function getType(): string;
}
