<?php

namespace Bitrix\UI\AccessRights\V2;

use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\UI\AccessRights\V2\Options\RightSection;
use Bitrix\UI\AccessRights\V2\Options\AdditionalMemberOptions;
use Bitrix\UI\AccessRights\V2\Options\UserGroup;
use JsonSerializable;

class Options implements JsonSerializable, Arrayable
{
	protected ?string $actionSave = null;
	protected ?string $mode = null;

	protected ?string $bodyType = null;

	protected array $additionalSaveParams = [];

	protected ?bool $isSaveOnlyChangedRights = null;

	protected ?bool $isSaveAccessRightsList = null;

	protected ?int $maxVisibleUserGroups = null;

	protected ?string $searchContainerSelector = null;

	protected AdditionalMemberOptions $additionalMembersParams;

	/** @var UserGroup[] */
	protected array $userGroups = [];

	/** @var RightSection[] */
	protected array $accessRights = [];

	protected array $analytics = [];

	public function __construct(
		protected string $component,
		protected string $containerId,
	)
	{
		$this->additionalMembersParams = (new AdditionalMemberOptions());
	}

	public function getMode(): ?string
	{
		return $this->mode;
	}

	public function setMode(?string $mode): static
	{
		$this->mode = $mode;

		return $this;
	}

	public function getBodyType(): ?string
	{
		return $this->bodyType;
	}

	public function setBodyType(?string $bodyType): static
	{
		$this->bodyType = $bodyType;

		return $this;
	}

	public function getAdditionalSaveParams(): array
	{
		return $this->additionalSaveParams;
	}

	public function setAdditionalSaveParams(array $additionalSaveParams): static
	{
		$this->additionalSaveParams = $additionalSaveParams;

		return $this;
	}

	public function isSaveOnlyChangedRights(): ?bool
	{
		return $this->isSaveOnlyChangedRights;
	}

	public function setIsSaveOnlyChangedRights(?bool $isSaveOnlyChangedRights): static
	{
		$this->isSaveOnlyChangedRights = $isSaveOnlyChangedRights;

		return $this;
	}

	public function isSaveAccessRightsList(): ?bool
	{
		return $this->isSaveAccessRightsList;
	}

	public function setIsSaveAccessRightsList(?bool $isSaveAccessRightsList): static
	{
		$this->isSaveAccessRightsList = $isSaveAccessRightsList;

		return $this;
	}

	public function getMaxVisibleUserGroups(): ?int
	{
		return $this->maxVisibleUserGroups;
	}

	public function setMaxVisibleUserGroups(?int $maxVisibleUserGroups): static
	{
		$this->maxVisibleUserGroups = $maxVisibleUserGroups;

		return $this;
	}

	public function getSearchContainerSelector(): ?string
	{
		return $this->searchContainerSelector;
	}

	public function setSearchContainerSelector(?string $searchContainerSelector): static
	{
		$this->searchContainerSelector = $searchContainerSelector;

		return $this;
	}

	public function getAdditionalMembersParams(): AdditionalMemberOptions
	{
		return $this->additionalMembersParams;
	}

	/**
	 * @param callable(AdditionalMemberOptions $params): void $configurator
	 * @return $this
	 */
	public function configureAdditionalMembersParams(callable $configurator): static
	{
		$configurator($this->additionalMembersParams);

		return $this;
	}

	public function setAdditionalMembersParams(AdditionalMemberOptions $additionalMembersParams): static
	{
		$this->additionalMembersParams = $additionalMembersParams;

		return $this;
	}

	public function getUserGroups(): array
	{
		return $this->userGroups;
	}

	public function setUserGroups(array $userGroups): static
	{
		$this->userGroups = $userGroups;

		return $this;
	}

	public function addUserGroup(UserGroup $userGroup): static
	{
		$this->userGroups[] = $userGroup;

		return $this;
	}

	public function getAccessRights(): array
	{
		return $this->accessRights;
	}

	public function setAccessRights(array $accessRights): static
	{
		$this->accessRights = $accessRights;

		return $this;
	}

	public function addAccessRight(RightSection $accessRight): static
	{
		$this->accessRights[] = $accessRight;

		return $this;
	}

	public function getComponent(): string
	{
		return $this->component;
	}

	public function setComponent(string $component): static
	{
		$this->component = $component;

		return $this;
	}

	public function getActionSave(): ?string
	{
		return $this->actionSave;
	}

	public function setActionSave(?string $actionSave): static
	{
		$this->actionSave = $actionSave;

		return $this;
	}

	public function getContainerId(): string
	{
		return $this->containerId;
	}

	public function setContainerId(string $containerId): static
	{
		$this->containerId = $containerId;

		return $this;
	}

	public function toArray(): array
	{
		return [
			'component' => $this->getComponent(),
			'actionSave' => $this->getActionSave(),
			'mode' => $this->getMode(),
			'bodyType' => $this->getBodyType(),
			'additionalSaveParams' => $this->getAdditionalSaveParams(),
			'isSaveOnlyChangedRights' => $this->isSaveOnlyChangedRights(),
			'maxVisibleUserGroups' => $this->getMaxVisibleUserGroups(),
			'searchContainerSelector' => $this->getSearchContainerSelector(),
			'additionalMembersParams' => $this->getAdditionalMembersParams()->toArray(),
			'isSaveAccessRightsList' => $this->isSaveAccessRightsList(),
			'renderToContainerId' => $this->getContainerId(),
			'userGroups' => array_map(static fn (UserGroup $userGroup) => $userGroup->toArray(), $this->userGroups),
			'accessRights' => array_map(static fn (RightSection $section) => $section->toArray(), $this->accessRights),
			'analytics' => $this->getAnalytics(),
		];
	}

	public function jsonSerialize(): array
	{
		return $this->toArray();
	}

	public function getAnalytics(): array
	{
		return $this->analytics;
	}

	public function setAnalytics(array $analytics): static
	{
		$this->analytics = $analytics;

		return $this;
	}
}
