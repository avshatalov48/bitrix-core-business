<?php

namespace Bitrix\Bizproc\Internal\Entity\WorkflowState;

use Bitrix\Bizproc\Internal\Entity\EntityInterface;
use Bitrix\Main\Type\DateTime;

class WorkflowState implements EntityInterface
{
	private ?string $id = null;
	private ?string $moduleId = null;
	private ?string $entity = null;
	private ?string $documentId = null;
	private ?int $documentIdInt = null;
	private ?int $workflowTemplateId = null;
	private ?string $state = null;
	private ?string $stateTitle = null;
	private ?string $stateParameters = null;
	private ?int $modified = null;
	private ?int $started = null;
	private ?int $startedBy = null;

	public function getId(): ?string
	{
		return $this->id;
	}

	public function setId(?string $id): self
	{
		$this->id = $id;

		return $this;
	}

	public function getModuleId(): ?string
	{
		return $this->moduleId;
	}

	public function setModuleId(?string $moduleId): self
	{
		$this->moduleId = $moduleId;

		return $this;
	}

	public function getEntity(): ?string
	{
		return $this->entity;
	}

	public function setEntity(?string $entity): self
	{
		$this->entity = $entity;

		return $this;
	}

	public function getDocumentId(): ?string
	{
		return $this->documentId;
	}

	public function setDocumentId(?string $documentId): self
	{
		$this->documentId = $documentId;

		return $this;
	}

	public function getDocumentIdInt(): ?int
	{
		return $this->documentIdInt;
	}

	public function setDocumentIdInt(?int $documentIdInt): self
	{
		$this->documentIdInt = $documentIdInt;

		return $this;
	}

	public function getWorkflowTemplateId(): ?int
	{
		return $this->workflowTemplateId;
	}

	public function setWorkflowTemplateId(?int $workflowTemplateId): self
	{
		$this->workflowTemplateId = $workflowTemplateId;

		return $this;
	}

	public function getState(): ?string
	{
		return $this->state;
	}

	public function setState(?string $state): self
	{
		$this->state = $state;

		return $this;
	}

	public function getStateTitle(): ?string
	{
		return $this->stateTitle;
	}

	public function setStateTitle(?string $stateTitle): self
	{
		$this->stateTitle = $stateTitle;

		return $this;
	}

	public function getStateParameters(): ?string
	{
		return $this->stateParameters;
	}

	public function setStateParameters(?string $stateParameters): self
	{
		$this->stateParameters = $stateParameters;

		return $this;
	}

	public function getModified(): ?int
	{
		return $this->modified;
	}

	public function setModified(?int $modified): self
	{
		$this->modified = $modified;

		return $this;
	}

	public function getStarted(): ?int
	{
		return $this->started;
	}

	public function setStarted(?int $started): self
	{
		$this->started = $started;

		return $this;
	}

	public function getStartedBy(): ?int
	{
		return $this->startedBy;
	}

	public function setStartedBy(?int $startedBy): self
	{
		$this->startedBy = $startedBy;

		return $this;
	}

	public static function mapFromArray(array $props): static
	{
		$result = new self();

		if (isset($props['id']))
		{
			$result->setId((string)$props['id']);
		}
		if (isset($props['moduleId']))
		{
			$result->setModuleId((string)$props['moduleId']);
		}
		if (isset($props['entity']))
		{
			$result->setEntity((string)$props['entity']);
		}
		if (isset($props['documentId']))
		{
			$result->setDocumentId((string)$props['documentId']);
		}
		if (isset($props['documentIdInt']))
		{
			$result->setDocumentIdInt((int)$props['documentIdInt']);
		}
		if (isset($props['workflowTemplateId']))
		{
			$result->setWorkflowTemplateId((int)$props['workflowTemplateId']);
		}
		if (isset($props['state']))
		{
			$result->setState((string)$props['state']);
		}
		if (isset($props['stateTitle']))
		{
			$result->setStateTitle((string)$props['stateTitle']);
		}
		if (isset($props['stateParameters']))
		{
			$result->setStateParameters((string)$props['stateParameters']);
		}
		if (isset($props['modified']))
		{
			$result->setModified((int)$props['modified']);
		}
		if (isset($props['started']))
		{
			$result->setStarted((int)$props['started']);
		}
		if (isset($props['startedBy']))
		{
			$result->setStartedBy((int)$props['startedBy']);
		}

		return $result;
	}


	public function toArray(): array
	{
		return [
			'id' => $this->getId(),
			'moduleId' => $this->getModuleId(),
			'entity' => $this->getEntity(),
			'documentId' => $this->getDocumentId(),
			'documentIdInt' => $this->getDocumentIdInt(),
			'workflowTemplateId' => $this->getWorkflowTemplateId(),
			'state' => $this->getState(),
			'stateTitle' => $this->getStateTitle(),
			'stateParameters' => $this->getStateParameters(),
			'modified' => $this->getModified(),
			'started' => $this->getStarted(),
			'startedBy' => $this->getStartedBy(),
		];
	}
}
