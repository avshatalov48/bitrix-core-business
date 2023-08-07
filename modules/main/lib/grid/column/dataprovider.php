<?php

namespace Bitrix\Main\Grid\Column;

use Bitrix\Main\Grid\Settings;

/**
 * Columns provider.
 *
 * Responsible for creating columns, used in the column collection.
 *
 * @see \Bitrix\Main\Grid\Column\Columns
 */
abstract class DataProvider
{
	private ?Settings $settings;

	/**
	 * @param Settings|null $settings if not used, may be `null`
	 */
	public function __construct(?Settings $settings = null)
	{
		$this->settings = $settings;
	}

	/**
	 * Provider settings.
	 *
	 * @return Settings
	 */
	final protected function getSettings(): Settings
	{
		return $this->settings;
	}

	/**
	 * Create column from params description.
	 *
	 * @param string $columnId
	 * @param array $params
	 *
	 * @return Column
	 */
	protected function createColumn(string $columnId, array $params = []): Column
	{
		if (!isset($params['id']))
		{
			$params['id'] = $columnId;
		}

		return new Column($columnId, $params);
	}

	/**
	 * Create columns from params descriptions.
	 *
	 * @param array[] $columns in format `[id => params]`. Params is argument `createColumn` method.
	 *
	 * @return Column[]
	 */
	protected function createColumns(array $columns): array
	{
		$result = [];

		foreach ($columns as $id => $description)
		{
			$result[$id] = $this->createColumn($id, $description);
		}

		return $result;
	}

	/**
	 * Provider columns.
	 *
	 * @return Column[] allowed without keys.
	 */
	abstract public function prepareColumns(): array;
}
