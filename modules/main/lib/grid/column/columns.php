<?php

namespace Bitrix\Main\Grid\Column;

/**
 * Columns collections.
 *
 * The main task is to work with columns: reading, filtering and generating a `select` statement.
 *
 * @see \Bitrix\Main\Grid\Column\DataProvider
 * @see \Bitrix\Main\Grid\Grid method `createColumns`
 */
class Columns implements \IteratorAggregate, \Countable
{
	/**
	 * @var DataProvider[]
	 */
	private array $providers;
	private array $columns;
	private array $providersColumns;

	/**
	 * @param DataProvider $providers
	 */
	public function __construct(DataProvider ...$providers)
	{
		$this->providers = [];
		foreach ($providers as $provider)
		{
			$this->providers[] = $provider;
		}
	}

	/**
	 * @inheritDoc
	 *
	 * @return Column[]
	 */
	final public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->getColumns());
	}

	/**
	 * @inheritDoc
	 *
	 * @return int
	 */
	final public function count(): int
	{
		return count($this->getColumns());
	}

    /**
     * Column by id.
     *
     * @param string $id
     *
     * @return Column|null
     */
	final public function getColumn(string $id): ?Column
	{
		return $this->getColumns()[$id] ?? null;
	}

	/**
	 * Columns.
	 *
	 * Recommended to use the collection as an iterator, instead of this method:
	 * ```php
		$columns = new Columns(...);
		foreach ($columns as $column)
		{
			# code...
		}
	 * ```
	 *
	 * @return Column[]
	 */
	final public function getColumns(): array
	{
		if (!isset($this->columns))
		{
			$this->columns = [];

			foreach ($this->getProvidersColumns() as $providerColumns)
			{
				/**
				 * @var Column[] $providerColumns
				 */
				foreach ($providerColumns as $column)
				{
					$this->columns[$column->getId()] = $column;
				}
			}

			$this->columns = $this->prepareColumns($this->columns);
		}

		return $this->columns;
	}

	/**
	 * @var Column[] $columns
	 *
	 * @return Column[]
	 */
	protected function prepareColumns(array $columns): array
	{
		return $columns;
	}

	/**
	 * Map of providers columns.
	 *
	 * @return array[] in format `[$providerClass => [$column, $column, ...]]`
	 */
	final protected function getProvidersColumns(): array
	{
		if (!isset($this->providersColumns))
		{
			$this->providersColumns = [];

			foreach ($this->providers as $provider)
			{
				$providerColumns = $provider->prepareColumns();
				if (empty($providerColumns))
				{
					continue;
				}

				$providerClass = get_class($provider);
				$this->providersColumns[$providerClass] = $providerColumns;
			}
		}

		return $this->providersColumns;
	}

	/**
	 * Gets a list of select names for the specified columns.
	 *
	 * Single column can use multiple fields in a query.
	 * Necessary columns will also be returned, even if they are not specified in the arguments (taking into account filtering by providers).
	 *
	 * All columns of all providers:
	 * ```php
		$ormSelect = $columns->getSelect();
	 * ```
	 *
	 * Filter by columns (typical usage: show only visible columns):
	 * ```php
		$ormSelect = $columns->getSelect(
			$grid->getVisibleColumnsIds()
		);
	 * ```
	 *
	 * Filter by providers (for example: grid contains columns from different tablets):
	 * ```php
		$elementSelect = $columns->getSelect(null, [
			\Bitrix\Iblock\Grid\Column\ElementProvider::class,
			\Bitrix\Iblock\Grid\Column\ElementPropertyProvider::class,
		]);

		$catalogSelect = $columns->getSelect(null, [
			\Bitrix\Catalog\Grid\Column\ProductProvider::class,
			\Bitrix\Catalog\Grid\Column\PriceProvider::class,
		]);
	 * ```
	 *
	 * And both filters:
	 * ```php
		$elementSelect = $columns->getSelect(
			$grid->getVisibleColumnsIds(),
			[
				\Bitrix\Iblock\Grid\Column\ElementProvider::class,
				\Bitrix\Iblock\Grid\Column\ElementPropertyProvider::class,
			]
		);
	 * ```
	 *
	 * @param string[]|null $columnIds if is `null`, returns all columns.
	 * @param string[]|null $providers array with provider's class full names.
	 * If filled in, the columns will be taken only from the specified providers.
	 * ATTENTION: necessary columns from other providers will NOT BE INCLUDED in the result!
	 *
	 * @return string[]
	 */
	public function getSelect(?array $columnIds = null, ?array $providers = null): array
	{
		$result = [];

		// filter by providers
		if (isset($providers))
		{
			$columns = [];
			foreach ($providers as $providerClass)
			{
				$providerColumns = $this->getProvidersColumns()[$providerClass] ?? null;
				if (!empty($providerColumns))
				{
					array_push($columns, ...$providerColumns);
				}
			}

			if (empty($columns))
			{
				return [];
			}

			$columns = $this->prepareColumns($columns);
		}
		else
		{
			$columns = $this->getColumns();
		}

		// filter by columns
		if (isset($columnIds))
		{
			$columnIds = array_fill_keys($columnIds, true);
			foreach ($columns as $column)
			{
				if (
					$column->isNecessary()
					|| isset($columnIds[$column->getId()])
				)
				{
					array_push($result, ...$column->getSelect());
				}
			}
		}
		else
		{
			foreach ($columns as $column)
			{
				array_push($result, ...$column->getSelect());
			}
		}

		return $result;
	}

	/**
	 * Returns only those values that are present in the column collection and are editable.
	 *
	 * @param array $values
	 *
	 * @return array
	 */
	public function prepareEditableColumnsValues(array $values): array
	{
		$result = [];

		foreach ($this->getColumns() as $column)
		{
			if (!$column->isEditable())
			{
				continue;
			}

			$id = $column->getId();
			if (array_key_exists($id, $values))
			{
				$result[$id] = $values[$id];
			}
		}

		return $result;
	}
}
