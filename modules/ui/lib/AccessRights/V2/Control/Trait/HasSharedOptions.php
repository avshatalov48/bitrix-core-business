<?php

namespace Bitrix\UI\AccessRights\V2\Control\Trait;

use Bitrix\UI\AccessRights\V2\Options\RightSection\RightItem;

trait HasSharedOptions
{
	protected ?string $minValue = null;
	protected ?string $maxValue = null;
	protected ?string $defaultValue = null;
	protected ?string $emptyValue = null;
	protected ?string $nothingSelectedValue = null;
	protected ?bool $setEmptyOnSetMinMaxValueInColumn = null;

	public function min(?string $minValue): static
	{
		$this->minValue = $minValue;

		return $this;
	}

	public function max(?string $maxValue): static
	{
		$this->maxValue = $maxValue;

		return $this;
	}

	public function default(?string $defaultValue): static
	{
		$this->defaultValue = $defaultValue;

		return $this;
	}

	public function empty(?string $emptyValue): static
	{
		$this->emptyValue = $emptyValue;

		return $this;
	}

	public function nothingSelected(?string $nothingSelectedValue): static
	{
		$this->nothingSelectedValue = $nothingSelectedValue;

		return $this;
	}

	public function setEmptyOnSetMinMaxValueInColumn(?bool $setEmptyOnSetMinMaxValueInColumn): static
	{
		$this->setEmptyOnSetMinMaxValueInColumn = $setEmptyOnSetMinMaxValueInColumn;

		return $this;
	}

	protected function configureRightItemBySharedOptions(RightItem $rightItem): void
	{
		$rightItem
			->setMinValue($this->minValue)
			->setMaxValue($this->maxValue)
			->setDefaultValue($this->defaultValue)
			->setEmptyValue($this->emptyValue)
			->setNothingSelectedValue($this->nothingSelectedValue)
			->setIsEmptyOnSetMinMaxValueInColumn($this->setEmptyOnSetMinMaxValueInColumn);
	}
}
