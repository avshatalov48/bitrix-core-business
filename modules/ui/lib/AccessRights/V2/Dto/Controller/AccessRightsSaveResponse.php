<?php

namespace Bitrix\UI\AccessRights\V2\Dto\Controller;

use Bitrix\Main\Type\Contract\Arrayable;
use JsonSerializable;

final class AccessRightsSaveResponse implements Arrayable, JsonSerializable
{
	public function __construct(
		private array $userGroups,
		private ?array $accessRights = null,
	)
	{
	}

	public function setUserGroups(array $userGroups): self
	{
		$this->userGroups = $userGroups;

		return $this;
	}

	public function setAccessRights(?array $accessRights): self
	{
		$this->accessRights = $accessRights;

		return $this;
	}

	public function toArray(): array
	{
		$values = [
			'USER_GROUPS' => $this->userGroups,
		];

		if ($this->accessRights !== null)
		{
			$values['ACCESS_RIGHTS'] = $this->accessRights;
		}

		return $values;
	}

	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
