<?php

namespace Bitrix\Main\Grid\Row;

use Generator;

/**
 * Assembles row values.
 *
 * The main task of this class is to form a list of `FieldAssembler'.
 *
 * Usage example:
 * ```php
	final class ProductRowAssembler extends RowAssembler
	{
		protected function prepareFieldAssemblers(): array
		{
			return [
				new UserFieldAssembler([
					'CREATED_BY',
					'MODIFIED_BY',
				]),
				new StoreWithInfoFieldAssembler([
					'STORE_ID',
				]),
			];
		}
	}
 * ```
 *
 * @see \Bitrix\Main\Grid\Row\FieldAssembler
 * @see \Bitrix\Main\Grid\Row\Assembler\EmptyRowAssembler
 */
abstract class RowAssembler
{
	/**
	 * @var FieldAssembler[]
	 */
	private array $fieldAssemblers;
	/**
	 * @var string[]
	 */
	private array $visibleColumnIds;

	/**
	 * @param string[] $visibleColumnIds
	 */
	public function __construct(array $visibleColumnIds)
	{
		$this->visibleColumnIds = $visibleColumnIds;
	}

	/**
	 * @return FieldAssembler[]
	 */
	abstract protected function prepareFieldAssemblers(): array;

	/**
	 * @return string[]
	 */
	final protected function getVisibleColumnIds(): array
	{
		return $this->visibleColumnIds;
	}

	/**
	 * @return FieldAssembler[]
	 */
	private function getAssemblers(): array
	{
		if (!isset($this->fieldAssemblers))
		{
			$this->fieldAssemblers = [];

			foreach ($this->prepareFieldAssemblers() as $assembler)
			{
				$this->fieldAssemblers[] = $assembler;
			}
		}

		return $this->fieldAssemblers;
	}

	/**
	 * @return FieldAssembler[]
	 */
	private function getFilteredAssemblers(): Generator
	{
		foreach ($this->getAssemblers() as $fieldAssembler)
		{
			$assemblerColumnsIds = $fieldAssembler->getColumnIds();
			$columnsIds = array_intersect($assemblerColumnsIds, $this->getVisibleColumnIds());

			if (!empty($columnsIds))
			{
				if (count($assemblerColumnsIds) !== count($columnsIds))
				{
					yield $fieldAssembler->clone($columnsIds);
				}
				else
				{
					yield $fieldAssembler;
				}
			}
		}
	}

	/**
	 * Gets rows prepared for output.
	 *
	 * @param array[] $rowsList
	 *
	 * @return array[]
	 */
	public function prepareRows(array $rowsList): array
	{
		foreach ($this->getFilteredAssemblers() as $fieldAssembler)
		{
			$rowsList = $fieldAssembler->prepareRows($rowsList);
		}

		return $rowsList;
	}
}
