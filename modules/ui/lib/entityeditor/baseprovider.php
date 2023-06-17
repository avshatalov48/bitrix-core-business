<?php

namespace Bitrix\UI\EntityEditor;

abstract class BaseProvider implements ReturnsEditorFields
{
	public function getFields(): array
	{
		return [
			'GUID' => $this->getGUID(),
			'CONFIG_ID' => $this->getConfigId(),
			'ENTITY_ID' => $this->getEntityId(),
			'ENTITY_TYPE_NAME' => $this->getEntityTypeName(),
			'ENTITY_FIELDS' => $this->getEntityFields(),
			'ENTITY_CONFIG' => $this->getEntityConfig(),
			'ENTITY_DATA' => $this->getEntityData(),
			'ENTITY_CONTROLLERS' => $this->getEntityControllers(),
			'READ_ONLY' => $this->isReadOnly(),
			'ENTITY_CONFIG_EDITABLE' => $this->isEntityConfigEditable(),
			'MODULE_ID' => $this->getModuleId(),
		];
	}

	abstract public function getGUID(): string;

	public function getConfigId(): ?string
	{
		return null;
	}

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

	public function isEntityConfigEditable(): bool
	{
		return true;
	}

	public function getModuleId(): ?string
	{
		return null;
	}
}
