<?php

namespace Bitrix\Bizproc\Task\Data;

class TaskData
{
	public readonly int $id;
	public readonly string $workflowId;
	private array $data;

	private function __construct(int $id, string $workflowId, array $data)
	{
		$this->id = $id;
		$this->workflowId = $workflowId;

		$this->data = $data;
	}

	public static function createFromArray(array $data): ?static
	{
		if (!isset($data['ID']) || !is_numeric($data['ID']))
		{
			return null;
		}

		$workflowId = $data['WORKFLOW_ID'] ?? null;
		if (empty($workflowId) || !is_string($workflowId))
		{
			$workflowId = $data['PARAMETERS']['WORKFLOW_ID'] ?? null;
			if (empty($workflowId) || !is_string($workflowId))
			{
				return null;
			}
		}

		return new static((int)$data['ID'], $workflowId, $data);
	}

	public function getData(): array
	{
		return $this->data;
	}

	public function getParameters(): ?array
	{
		return $this->data['PARAMETERS'] ?? null;
	}

	public function setParameters(array $parameters): self
	{
		$this->data['PARAMETERS'] = $parameters;

		return $this;
	}

	public function getDocumentId(): ?array
	{
		return $this->data['PARAMETERS']['DOCUMENT_ID'] ?? null;
	}
}
