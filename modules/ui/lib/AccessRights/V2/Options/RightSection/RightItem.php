<?php

namespace Bitrix\UI\AccessRights\V2\Options\RightSection;

use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\UI\AccessRights\V2\Options\RightSection\RightItem\Type;
use Bitrix\UI\AccessRights\V2\Options\RightSection\RightItem\Variable;
use JsonSerializable;

class RightItem implements JsonSerializable, Arrayable
{
	protected ?string $subtitle = null;
	protected ?string $hint = null;
	protected ?bool $isGroupHead = null;
	protected ?string $group = null;

	protected array|string|null $minValue = null;
	protected array|string|null $maxValue = null;
	protected array|string|null $defaultValue = null;
	protected array|string|null $emptyValue = null;
	protected array|string|null $nothingSelectedValue = null;
	protected ?bool $isEmptyOnSetMinMaxValueInColumn = null;
	protected ?array $selectedVariablesAliases = null;

	protected string|int|null $allSelectedCode = null;
	protected ?bool $isEnableSearch = null;
	protected ?bool $isShowAvatars = null;
	protected ?bool $isCompactView = null;
	protected ?string $hintTitle = null;
	protected ?string $dependentVariablesPopupHint = null;

	protected ?string $iconClass = null;
	protected ?bool $isClickable = null;
	protected ?bool $isDeletable = null;
	protected ?bool $isNew = null;
	protected ?bool $isModified = null;

	/** @var Variable[] */
	protected array $variables = [];

	public function __construct(
		protected string $id,
		protected string $title,
		protected Type $type,
	)
	{
	}

	public function getId(): int|string
	{
		return $this->id;
	}

	public function setId(int|string $id): RightItem
	{
		$this->id = $id;

		return $this;
	}

	public function getType(): Type
	{
		return $this->type;
	}

	public function setType(Type $type): static
	{
		$this->type = $type;

		return $this;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function setTitle(string $title): static
	{
		$this->title = $title;

		return $this;
	}

	public function getSubtitle(): ?string
	{
		return $this->subtitle;
	}

	public function setSubtitle(?string $subtitle): static
	{
		$this->subtitle = $subtitle;

		return $this;
	}

	public function getHint(): ?string
	{
		return $this->hint;
	}

	public function setHint(?string $hint): static
	{
		$this->hint = $hint;

		return $this;
	}

	public function isGroupHead(): ?bool
	{
		return $this->isGroupHead;
	}

	public function setIsGroupHead(?bool $isGroupHead): static
	{
		$this->isGroupHead = $isGroupHead;

		return $this;
	}

	public function getGroup(): ?string
	{
		return $this->group;
	}

	public function setGroup(?string $group): static
	{
		$this->group = $group;

		return $this;
	}

	public function getMinValue(): array|string|null
	{
		return $this->minValue;
	}

	public function setMinValue(array|string|null $minValue): static
	{
		$this->minValue = $minValue;

		return $this;
	}

	public function getMaxValue(): array|string|null
	{
		return $this->maxValue;
	}

	public function setMaxValue(array|string|null $maxValue): static
	{
		$this->maxValue = $maxValue;

		return $this;
	}

	public function getDefaultValue(): array|string|null
	{
		return $this->defaultValue;
	}

	public function setDefaultValue(array|string|null $defaultValue): static
	{
		$this->defaultValue = $defaultValue;

		return $this;
	}

	public function getEmptyValue(): array|string|null
	{
		return $this->emptyValue;
	}

	public function setEmptyValue(array|string|null $emptyValue): static
	{
		$this->emptyValue = $emptyValue;

		return $this;
	}

	public function getNothingSelectedValue(): array|string|null
	{
		return $this->nothingSelectedValue;
	}

	public function setNothingSelectedValue(array|string|null $nothingSelectedValue): static
	{
		$this->nothingSelectedValue = $nothingSelectedValue;

		return $this;
	}

	public function isSetEmptyOnSetMinMaxValueInColumn(): ?bool
	{
		return $this->isEmptyOnSetMinMaxValueInColumn;
	}

	public function setIsEmptyOnSetMinMaxValueInColumn(?bool $isEmptyOnSetMinMaxValueInColumn): static
	{
		$this->isEmptyOnSetMinMaxValueInColumn = $isEmptyOnSetMinMaxValueInColumn;

		return $this;
	}

	public function getSelectedVariablesAliases(): ?array
	{
		return $this->selectedVariablesAliases;
	}

	public function setSelectedVariablesAliases(?array $selectedVariablesAliases): static
	{
		$this->selectedVariablesAliases = $selectedVariablesAliases;

		return $this;
	}

	public function getAllSelectedCode(): int|string|null
	{
		return $this->allSelectedCode;
	}

	public function setAllSelectedCode(int|string|null $allSelectedCode): static
	{
		$this->allSelectedCode = $allSelectedCode;

		return $this;
	}

	public function isEnableSearch(): ?bool
	{
		return $this->isEnableSearch;
	}

	public function setIsEnableSearch(?bool $isEnableSearch): static
	{
		$this->isEnableSearch = $isEnableSearch;

		return $this;
	}

	public function isShowAvatars(): ?bool
	{
		return $this->isShowAvatars;
	}

	public function setIsShowAvatars(?bool $isShowAvatars): static
	{
		$this->isShowAvatars = $isShowAvatars;

		return $this;
	}

	public function isCompactView(): ?bool
	{
		return $this->isCompactView;
	}

	public function setIsCompactView(?bool $isCompactView): static
	{
		$this->isCompactView = $isCompactView;

		return $this;
	}

	public function getHintTitle(): ?string
	{
		return $this->hintTitle;
	}

	public function setHintTitle(?string $hintTitle): static
	{
		$this->hintTitle = $hintTitle;

		return $this;
	}

	public function getVariables(): array
	{
		return $this->variables;
	}

	public function setVariables(array $variables): static
	{
		$this->variables = $variables;

		return $this;
	}

	public function addVariable(Variable $variable): static
	{
		$this->variables[] = $variable;

		return $this;
	}

	public function getDependentVariablesPopupHint(): ?string
	{
		return $this->dependentVariablesPopupHint;
	}

	public function setDependentVariablesPopupHint(?string $dependentVariablesPopupHint): static
	{
		$this->dependentVariablesPopupHint = $dependentVariablesPopupHint;

		return $this;
	}

	public function getIconClass(): ?string
	{
		return $this->iconClass;
	}

	public function setIconClass(?string $iconClass): static
	{
		$this->iconClass = $iconClass;

		return $this;
	}

	public function isClickable(): ?bool
	{
		return $this->isClickable;
	}

	public function setIsClickable(?bool $isClickable): static
	{
		$this->isClickable = $isClickable;

		return $this;
	}

	public function isDeletable(): ?bool
	{
		return $this->isDeletable;
	}

	public function setIsDeletable(?bool $isDeletable): static
	{
		$this->isDeletable = $isDeletable;

		return $this;
	}

	public function isNew(): ?bool
	{
		return $this->isNew;
	}

	public function setIsNew(?bool $isNew): static
	{
		$this->isNew = $isNew;

		return $this;
	}

	public function isModified(): ?bool
	{
		return $this->isModified;
	}

	public function setIsModified(?bool $isModified): static
	{
		$this->isModified = $isModified;

		return $this;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->getId(),
			'type' => $this->getType()->value,
			'title' => $this->getTitle(),
			'subtitle' => $this->getSubtitle(),
			'hint' => $this->getHint(),
			'group' => $this->getGroup(),
			'groupHead' => $this->isGroupHead(),
			'minValue' => $this->getMinValue(),
			'maxValue' => $this->getMaxValue(),
			'defaultValue' => $this->getDefaultValue(),
			'emptyValue' => $this->getEmptyValue(),
			'nothingSelectedValue' => $this->getNothingSelectedValue(),
			'setEmptyOnSetMinMaxValueInColumn' => $this->isSetEmptyOnSetMinMaxValueInColumn(),

			'variables' => array_map(static fn (Variable $variable) => $variable->toArray(), $this->getVariables()),

			'allSelectedCode' => $this->getAllSelectedCode(),
			'selectedVariablesAliases' => $this->getSelectedVariablesAliases(),
			'enableSearch' => $this->isEnableSearch(),
			'showAvatars' => $this->isShowAvatars(),
			'compactView' => $this->isCompactView(),
			'hintTitle' => $this->getHintTitle(),

			'dependentVariablesPopupHint' => $this->getDependentVariablesPopupHint(),

			'iconClass' => $this->getIconClass(),
			'isClickable' => $this->isClickable(),
			'isDeletable' => $this->isDeletable(),
			'isNew' => $this->isNew(),
			'isModified' => $this->isModified(),
		];
	}

	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
