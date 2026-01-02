<?php

namespace Bitrix\Bizproc\Starter;

final class Document
{
	public readonly array $complexId;
	public readonly array $complexType;
	private ?array $changedFieldNames = null;

	public function __construct(array $complexDocumentId, ?array $complexType = null)
	{
		$this->complexId = $complexDocumentId;

		if (!$complexType)
		{
			try
			{
				$complexType = \CBPRuntime::getRuntime()->getDocumentService()->getDocumentType($complexDocumentId);
				$complexType = is_array($complexType) ? $complexType : [];
			}
			catch (\Exception $exception)
			{
				$complexType = []; // document not found
			}
		}
		$this->complexType = $complexType;
	}

	public function setChangedFieldNames(array $changedFieldNames): self
	{
		$this->changedFieldNames = $changedFieldNames;

		return $this;
	}

	public function getModuleId(): string
	{
		return $this->complexId[0] ?? '';
	}

	public function getEntity(): string
	{
		return $this->complexId[1] ?? '';
	}

	public function getType(): string
	{
		return $this->complexType[2] ?? '';
	}

	public function getId(): mixed
	{
		return $this->complexId[2];
	}

	public function hasChangedFields(): bool
	{
		return (bool)$this->changedFieldNames;
	}

	public function getChangedFieldNames(): array
	{
		return $this->changedFieldNames ?? [];
	}

	public function isFieldChanged(string $name): bool
	{
		return in_array($name, $this->getChangedFieldNames(), true);
	}
}
