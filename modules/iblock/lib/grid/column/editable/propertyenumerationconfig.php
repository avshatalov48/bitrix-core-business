<?php

namespace Bitrix\Iblock\Grid\Column\Editable;

use Bitrix\Main\Grid;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock\PropertyTable;

class PropertyEnumerationConfig extends Grid\Column\Editable\Config
{
	private bool $multiple;
	private string $listMode;

	private string $compiledType;

	private array $items;

	public function __construct(string $name, array $property, array $items)
	{
		$this->multiple = ($property['MULTIPLE'] ?? 'N') === 'Y';
		$this->listMode = (string)($property['LIST_TYPE'] ?? PropertyTable::LISTBOX);

		$this->compileType();

		parent::__construct($name, $this->getCompiledType());

		$this->items = $items;
	}

	private function compileType(): void
	{
		if (!isset($this->compiledType))
		{
			$this->compiledType = match ($this->listMode)
			{
				PropertyTable::LISTBOX, PropertyTable::CHECKBOX => $this->multiple
					? Grid\Editor\Types::MULTISELECT
					: Grid\Editor\Types::DROPDOWN
				,
				default => Grid\Editor\Types::CUSTOM,
			};
		}
	}

	private function getCompiledType(): string
	{
		return $this->compiledType;
	}

	private function isSelectMode(): bool
	{
		$compiledType = $this->getCompiledType();

		return
			$compiledType === Grid\Editor\Types::MULTISELECT
			|| $compiledType === Grid\Editor\Types::DROPDOWN
		;
	}

	/**
	 * @inheritDoc
	 */
	public function toArray(): array
	{
		$result = parent::toArray();

		if ($this->isSelectMode())
		{
			$result['DATA'] = [
				'ITEMS' => $this->getItemsAsDropdown(),
			];
		}

		return $result;
	}

	/**
	 * Items as dropdown.
	 *
	 * @return array
	 */
	private function getItemsAsDropdown(): array
	{
		$result = [];

		if (!$this->multiple)
		{
			$result[] = [
				'VALUE' => '',
				'NAME' => Loc::getMessage('PROPERTY_ENUM_CONFIG_EMPTY_VALUE'),
			];
		}

		foreach ($this->items as $value)
		{
			$result[] = [
				'VALUE' => $value['ID'],
				'NAME' => $value['VALUE'],
			];
		}

		return $result;
	}
}
