<?php

namespace Bitrix\Bizproc\Api\Response\WorkflowStateService;

use Bitrix\Main\Result;

class GetFullFilledListResponse extends Result
{
	public function getWorkflowStatesList(): array
	{
		$collection = $this->data['collection'] ?? [];

		return is_array($collection) ? $collection : [];
	}

	public function getMembersInfo(): array
	{
		$membersInfo = $this->data['membersInfo'] ?? [];

		return is_array($membersInfo) ? $membersInfo : [];
	}

	public function setWorkflowStatesList(array $collection): static
	{
		$this->data['collection'] = $collection;

		return $this;
	}

	public function setMembersInfo(array $info): static
	{
		$this->data['membersInfo'] = $info;

		return $this;
	}
}
