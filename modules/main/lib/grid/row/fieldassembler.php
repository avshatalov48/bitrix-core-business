<?php

namespace Bitrix\Main\Grid\Row;

use Bitrix\Main\Grid\Settings;

/**
 * Assembles row values for specific columns.
 *
 * Depending on the required functionality, need to override one (or several) of the methods:
 * - prepareColumn
 * - prepareRow
 * - prepareRows
 *
 * For more information, see the description of each of the methods.
 *
 * @see \Bitrix\Main\Grid\Row\RowAssembler
 * @see \Bitrix\Main\Grid\Row\Assembler\Field\ListFieldAssembler
 * @see \Bitrix\Main\Grid\Row\Assembler\Field\UserFieldAssembler
 */
abstract class FieldAssembler
{
	/**
	 * @psalm-readonly
	 *
	 * @var string[]
	 */
	private array $columnIds;
	private ?Settings $settings;

	/**
	 * @param string[] $columnIds columns to be processed
	 * @param Settings|null $settings if not used may be `null`
	 */
	public function __construct(array $columnIds, ?Settings $settings = null)
	{
		$this->columnIds = $columnIds;
		$this->settings = $settings;
	}

	/**
	 * @return string[]
	 */
	final public function getColumnIds(): array
	{
		return $this->columnIds;
	}

	/**
	 * @return Settings
	 */
	final protected function getSettings(): Settings
	{
		return $this->settings;
	}

	/**
	 * Clone instance.
	 *
	 * Column ids is read-only, so to change them, you need to create a new object.
	 *
	 * @param array $columnIds
	 *
	 * @return FieldAssembler
	 */
	public function clone(array $columnIds): FieldAssembler
	{
		$clone = clone $this;
		$clone->columnIds = $columnIds;

		return $clone;
	}

	/**
	 * Preparation of a column value.
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	protected function prepareColumn($value)
	{
		return $value;
	}

	/**
	 * Preparation of a single row.
	 *
	 * If you can't implement the functionality for each column individually, you can override this method.
	 * See also `prepareColumn` method.
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	protected function prepareRow(array $row): array
	{
		if (empty($this->getColumnIds()))
		{
			return $row;
		}

		$row['columns'] ??= [];

		foreach ($this->getColumnIds() as $columnId)
		{
			$row['columns'][$columnId] = $this->prepareColumn($row['data'][$columnId] ?? null);
		}

		return $row;
	}

	/**
	 * Preparation of all rows at once.
	 *
	 * If you need to process all rows at once, override this method.
	 * See also `prepareRow` method.
	 *
	 * @param array $rowList
	 *
	 * @return array
	 */
	public function prepareRows(array $rowList): array
	{
		foreach ($rowList as &$row)
		{
			$row = $this->prepareRow($row);
		}

		return $rowList;
	}
}
