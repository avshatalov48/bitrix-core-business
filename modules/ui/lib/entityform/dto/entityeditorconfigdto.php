<?php

namespace Bitrix\Ui\EntityForm\Dto;

use Bitrix\Ui\EntityForm\EO_EntityFormConfig;
use Bitrix\UI\Form\EntityEditorConfigScope;

final class EntityEditorConfigDto
{
	public function __construct(
		private readonly string $categoryName,
		private readonly string $entityTypeId,
		private readonly string $configScopeType,
		private readonly ?int $userScopeId,
		private ?int $userId = null,
		private readonly bool $onAdd = false,
		private readonly bool $onUpdate = false,
	) {
	}

	public static function fromEntityFormConfig(EO_EntityFormConfig $config): self
	{
		return new self(
			$config->getOptionCategory(),
			$config->getEntityTypeId(),
			EntityEditorConfigScope::CUSTOM,
			$config->getId(),
			null,
			$config->getOnAdd(),
			$config->getOnUpdate(),
		);
	}

	public function setUserId(int $int): self
	{
		$this->userId = $int;

		return $this;
	}

	public function getConfigScopeType(): string
	{
		return $this->configScopeType;
	}

	public function getCategoryName(): string
	{
		return $this->categoryName;
	}

	public function getEntityTypeId(): string
	{
		return $this->entityTypeId;
	}

	public function getUserScopeId(): ?int
	{
		return $this->userScopeId;
	}

	public function getUserId(): ?int
	{
		return $this->userId;
	}

	public function getOptionValue(): array
	{
		return [
			'scope' => $this->configScopeType,
			'userScopeId' => $this->userScopeId,
			'onAdd' => $this->onAdd,
			'onUpdate' => $this->onUpdate,
		];
	}

	public function hasOnAdd(): bool
	{
		return $this->onAdd;
	}

	public function hasOnUpdate(): bool
	{
		return $this->onUpdate;
	}

	public function getModuleIdFromCategory(): ?string
	{
		if (preg_match('/^([a-zA-Z0-9_]+)\.entity\.editor$/', $this->getCategoryName(), $matches))
		{
			return $matches[1];
		}

		return null;
	}
}
