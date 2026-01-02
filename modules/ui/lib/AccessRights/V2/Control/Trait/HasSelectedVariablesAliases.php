<?php

namespace Bitrix\UI\AccessRights\V2\Control\Trait;

use Bitrix\UI\AccessRights\V2\Options\RightSection\RightItem;

trait HasSelectedVariablesAliases
{
	protected array $selectedVariablesAliases = [];

	public function selectedVariablesAliases(array $selectedVariablesAliases): static
	{
		$this->selectedVariablesAliases = $selectedVariablesAliases;

		return $this;
	}

	public function selectedVariablesAlias(string $key, string $title): static
	{
		$this->selectedVariablesAliases[$key] = $title;

		return $this;
	}

	protected function configureRightItemBySelectedVariablesAliases(RightItem $rightItem): void
	{
		$rightItem->setSelectedVariablesAliases($this->selectedVariablesAliases);
	}

	public static function separator(string $alias): array
	{
		return [
			'separator' => $alias,
		];
	}
}
