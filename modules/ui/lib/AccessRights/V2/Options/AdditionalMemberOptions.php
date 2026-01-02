<?php

namespace Bitrix\UI\AccessRights\V2\Options;

use Bitrix\Main\Type\Contract\Arrayable;
use JsonSerializable;

class AdditionalMemberOptions implements JsonSerializable, Arrayable
{
	private ?bool $addUserGroupsProviderTab = null;
	private ?bool $addProjectsProviderTab = null;
	private ?bool $addStructureTeamsProviderTab = null;
	private ?bool $useStructureDepartmentsProviderTab = null;

	public function getAddUserGroupsProviderTab(): ?bool
	{
		return $this->addUserGroupsProviderTab;
	}

	public function setAddUserGroupsProviderTab(?bool $addUserGroupsProviderTab): self
	{
		$this->addUserGroupsProviderTab = $addUserGroupsProviderTab;

		return $this;
	}

	public function getAddProjectsProviderTab(): ?bool
	{
		return $this->addProjectsProviderTab;
	}

	public function setAddProjectsProviderTab(?bool $addProjectsProviderTab): self
	{
		$this->addProjectsProviderTab = $addProjectsProviderTab;

		return $this;
	}

	public function getAddStructureTeamsProviderTab(): ?bool
	{
		return $this->addStructureTeamsProviderTab;
	}

	public function setAddStructureTeamsProviderTab(?bool $addStructureTeamsProviderTab): self
	{
		$this->addStructureTeamsProviderTab = $addStructureTeamsProviderTab;

		return $this;
	}

	public function getUseStructureDepartmentsProviderTab(): ?bool
	{
		return $this->useStructureDepartmentsProviderTab;
	}

	public function setUseStructureDepartmentsProviderTab(?bool $useStructureDepartmentsProviderTab): self
	{
		$this->useStructureDepartmentsProviderTab = $useStructureDepartmentsProviderTab;

		return $this;
	}

	public function toArray(): array
	{
		return [
			'addUserGroupsProviderTab' => $this->getAddUserGroupsProviderTab(),
			'addProjectsProviderTab' => $this->getAddProjectsProviderTab(),
			'addStructureTeamsProviderTab' => $this->getAddStructureTeamsProviderTab(),
			'useStructureDepartmentsProviderTab' => $this->getUseStructureDepartmentsProviderTab(),
		];
	}

	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
