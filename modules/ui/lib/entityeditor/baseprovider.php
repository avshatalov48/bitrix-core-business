<?php

namespace Bitrix\UI\EntityEditor;

abstract class BaseProvider
{
	public function getFields(): array
	{
		return [
			'GUID' => $this->getGUID(),
			'ENTITY_ID' => $this->getEntityId(),
			'ENTITY_TYPE_NAME' => $this->getEntityTypeName(),
			'ENTITY_FIELDS' => $this->getEntityFields(),
			'ENTITY_CONFIG' => $this->getEntityConfig(),
			'ENTITY_DATA' => $this->getEntityData(),
			'ENTITY_CONTROLLERS' => $this->getEntityControllers(),
			'READ_ONLY' => $this->isReadOnly(),
		];
	}

	abstract public function getGUID(): string;

	abstract public function getEntityId(): ?int;

	abstract public function getEntityTypeName(): string;

	abstract public function getEntityFields(): array;

	abstract public function getEntityConfig(): array;

	abstract public function getEntityData(): array;

	public function getEntityControllers(): array
	{
		return [];
	}

	public function isReadOnly(): bool
	{
		return false;
	}
}
