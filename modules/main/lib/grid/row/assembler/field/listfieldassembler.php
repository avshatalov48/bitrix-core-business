<?php

namespace Bitrix\Main\Grid\Row\Assembler\Field;

use Bitrix\Main\Grid\Row\FieldAssembler;

/**
 * Assembles row values of list type columns.
 */
abstract class ListFieldAssembler extends FieldAssembler
{
	private array $names;

	/**
	 * Available list names.
	 *
	 * @return string[]
	 * @psalm-return array<string, int|float|string>
	 */
	abstract protected function getNames(): array;

	/**
	 * Empty value name.
	 *
	 * @return string|null
	 */
	protected function getEmptyName(): ?string
	{
		return null;
	}

	/**
	 * @inheritDoc
	 */
	final protected function prepareColumn($value)
	{
		if (empty($value))
		{
			return $this->getEmptyName();
		}

		$this->names ??= $this->getNames();

		return $this->names[$value] ?? null;
	}
}
