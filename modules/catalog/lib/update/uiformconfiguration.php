<?php

namespace Bitrix\Catalog\Update;

use Bitrix\UI\Form\EntityEditorConfiguration;
use Bitrix\Ui\Form\EntityEditorConfigScope;

class UiFormConfiguration
{
	protected const SET_MODE_FIRST = 'first';
	protected const SET_MODE_LAST = 'last';
	protected const SET_MODE_BEFORE = 'before';
	protected const SET_MODE_AFTER = 'after';

	protected const FORM_CATEGORY = 'ui.form.editor';

	protected const PRODUCT_FORM_ID = 'CATALOG_PRODUCT_CARD';

	public const PARENT_SECTION_MAIN = 'main';

	/**
	 * Returns column validation's result.
	 *
	 * @param mixed $config
	 * @return bool
	 */
	protected static function isValidColumnConfig($config): bool
	{
		if (empty($config) || !is_array($config))
		{
			return false;
		}
		if (empty($config['elements']) || !is_array($config['elements']))
		{
			return false;
		}

		return true;
	}

	/**
	 * Returns elements validation's result.
	 *
	 * @param mixed $config
	 * @return bool
	 */
	protected static function isValidElementListConfig($config): bool
	{
		if (empty($config) || !is_array($config))
		{
			return false;
		}
		if (
			!isset($config['type'])
			|| (
				empty($config['elements'])
				|| !is_array($config['elements'])
			)
		)
		{
			return false;
		}

		return true;
	}

	/**
	 * Returns row validation's result.
	 *
	 * @param mixed $config
	 * @return bool
	 */
	protected static function isValidRowConfig($config): bool
	{
		if (empty($config) || !is_array($config))
		{
			return false;
		}
		if (!isset($config['name']))
		{
			return false;
		}

		return true;
	}

	protected static function getFieldIndex(array $formSettings, string $fieldName): ?array
	{
		if (
			empty($formSettings)
			|| $fieldName === ''
		)
		{
			return null;
		}

		foreach ($formSettings as $columnIndex => $column)
		{
			if (!static::isValidColumnConfig($column))
			{
				continue;
			}

			foreach ($column['elements'] as $listIndex => $list)
			{
				if (!static::isValidElementListConfig($list))
				{
					continue;
				}
				if ($list['type'] !== 'section')
				{
					continue;
				}

				foreach ($list['elements'] as $rowIndex => $row)
				{
					if (!static::isValidRowConfig($row))
					{
						continue;
					}

					if ($row['name'] === $fieldName)
					{
						return [
							'COLUMN' => $columnIndex,
							'LIST' => $listIndex,
							'ROW' => $rowIndex,
						];
					}
				}
			}
		}

		return null;
	}

	protected static function getListIndex(array $formSettings, string $listName): ?array
	{
		if (
			empty($formSettings)
			|| $listName === ''
		)
		{
			return null;
		}

		foreach ($formSettings as $columnIndex => $column)
		{
			if (!static::isValidColumnConfig($column))
			{
				continue;
			}

			foreach ($column['elements'] as $listIndex => $list)
			{
				if (!static::isValidElementListConfig($list))
				{
					continue;
				}
				if ($list['type'] !== 'section')
				{
					continue;
				}
				if ($list['name'] === $listName)
				{
					return [
						'COLUMN' => $columnIndex,
						'LIST' => $listIndex,
						'ROW' => 0,
					];
				}
			}
		}

		return null;
	}

	protected static function checkRowIndex(array $index): bool
	{
		return (isset($index['COLUMN']) && isset($index['LIST']) && isset($index['ROW']));
	}

	protected static function isRowExists(array $formSettings, array $index): bool
	{
		if (empty($formSettings) || !static::checkRowIndex($index))
		{
			return false;
		}

		$column = $index['COLUMN'];
		$list = $index['LIST'];
		$row = $index['ROW'];

		if (!isset($formSettings[$column]))
		{
			return false;
		}
		if (!isset($formSettings[$column]['elements'][$list]))
		{
			return false;
		}
		if (!isset($formSettings[$column]['elements'][$list]['elements'][$row]))
		{
			return false;
		}

		return true;
	}

	protected static function unsetField(array $formSettings, array $index): array
	{
		if (!static::isRowExists($formSettings, $index))
		{
			return $formSettings;
		}

		$column = $index['COLUMN'];
		$list = $index['LIST'];
		$row = $index['ROW'];

		unset($formSettings[$column]['elements'][$list]['elements'][$row]);
		$formSettings[$column]['elements'][$list]['elements'] = array_values(
			$formSettings[$column]['elements'][$list]['elements']
		);

		return $formSettings;
	}

	protected static function replaceField(array $formSettings, array $index, array $field): array
	{
		if (!static::isRowExists($formSettings, $index))
		{
			return $formSettings;
		}

		$column = $index['COLUMN'];
		$list = $index['LIST'];
		$row = $index['ROW'];

		$formSettings[$column]['elements'][$list]['elements'][$row] = $field;

		return $formSettings;
	}

	protected static function setField(array $formSettings, array $index, array $field, string $mode): array
	{
		if (empty($formSettings) || !static::checkRowIndex($index))
		{
			return $formSettings;
		}

		$column = $index['COLUMN'];
		$list = $index['LIST'];
		$row = $index['ROW'];

		if (!isset($formSettings[$column]))
		{
			return $formSettings;
		}
		if (!isset($formSettings[$column]['elements'][$list]))
		{
			return $formSettings;
		}
		if (
			!isset($formSettings[$column]['elements'][$list]['elements'])
			|| !is_array($formSettings[$column]['elements'][$list]['elements'])
		)
		{
			return $formSettings;
		}

		switch ($mode)
		{
			case self::SET_MODE_FIRST:
				array_unshift(
					$formSettings[$column]['elements'][$list]['elements'],
					$field
				);
				break;
			case self::SET_MODE_LAST:
				$formSettings[$column]['elements'][$list]['elements'][] = $field;
				break;
			case self::SET_MODE_BEFORE:
				if (static::isRowExists($formSettings, $index))
				{
					if ($row === 0)
					{
						array_unshift(
							$formSettings[$column]['elements'][$list]['elements'],
							$field
						);
					}
					else
					{
						$before = array_slice($formSettings[$column]['elements'][$list]['elements'], 0, $row);
						$before[] = $field;
						$after = array_slice($formSettings[$column]['elements'][$list]['elements'], $row);
						$formSettings[$column]['elements'][$list]['elements'] = array_merge(
							$before,
							$after
						);
						unset($after, $before);
					}
				}
				break;
			case self::SET_MODE_AFTER:
				if (static::isRowExists($formSettings, $index))
				{
					if ($row === count($formSettings[$column]['elements'][$list]['elements']))
					{
						$formSettings[$column]['elements'][$list]['elements'][] = $field;
					}
					else
					{
						$before = array_slice($formSettings[$column]['elements'][$list]['elements'], 0, $row + 1);
						$before[] = $field;
						$after = array_slice($formSettings[$column]['elements'][$list]['elements'], $row + 1);
						$formSettings[$column]['elements'][$list]['elements'] = array_merge(
							$before,
							$after
						);
						unset($after, $before);
					}
				}
				break;
		}

		return $formSettings;
	}

	protected static function getConfiguration(): EntityEditorConfiguration
	{
		return new EntityEditorConfiguration(self::FORM_CATEGORY);
	}

	public static function addFormField(array $field, string $parentId): void
	{
		if (empty($field) || !isset($field['name']) || !is_string($field['name']))
		{
			return;
		}
		if ($parentId === '')
		{
			return;
		}

		$config = static::getConfiguration();
		$formSettings = $config->get(self::PRODUCT_FORM_ID, EntityEditorConfigScope::COMMON);

		if (empty($formSettings) || !is_array($formSettings))
		{
			return;
		}

		if (static::getFieldIndex($formSettings, $field['name']) !== null)
		{
			return;
		}

		$listIndex = static::getListIndex($formSettings, $parentId);
		if ($listIndex === null)
		{
			return;
		}

		$formSettings = static::setField($formSettings, $listIndex, $field, self::SET_MODE_LAST);
		$config->set(
			self::PRODUCT_FORM_ID,
			$formSettings,
			['scope' => EntityEditorConfigScope::COMMON]
		);
		unset($config);
	}
}
