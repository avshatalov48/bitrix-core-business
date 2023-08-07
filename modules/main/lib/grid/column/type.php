<?php

namespace Bitrix\Main\Grid\Column;

use Bitrix\Main\Grid;

class Type
{
	// main.ui.grid constants
	public const TEXT = 'text';
	public const CHECKBOX = 'checkbox';
	public const TAGS = 'tags';
	public const LABELS = 'labels';
	public const INT = 'int';
	public const FLOAT = 'double';
	public const NUMBER = 'number';
	public const DATE = 'date';
	public const DROPDOWN = 'list';
	public const MULTISELECT = 'multiselect';
	public const MONEY = 'money';
	public const CUSTOM = 'custom';

	// \CAdminListRow constants
	public const INPUT = 'input';
	public const CALENDAR = 'calendar';
	public const SELECT = 'select';
	public const FILE = 'file';
	public const HTML = 'html';

	/**
	 * Convert miscellaneous column types from various sources to editor type.
	 *
	 * @param string $type
	 * @return string|null
	 */
	public static function getEditorType(string $type): ?string
	{
		$result = null;

		if ($type === '')
		{
			$type = self::TEXT;
		}
		switch ($type)
		{
			case self::TEXT:
			case self::INPUT:
				$result = Grid\Editor\Types::TEXT;
				break;
			case self::INT:
			case self::FLOAT:
			case self::NUMBER:
				$result = Grid\Editor\Types::NUMBER;
				break;
			case self::CHECKBOX:
				$result = Grid\Editor\Types::CHECKBOX;
				break;
			case self::DATE:
			case self::CALENDAR:
				$result = Grid\Editor\Types::DATE;
				break;
			case self::DROPDOWN:
			case self::SELECT:
				$result = Grid\Editor\Types::DROPDOWN;
				break;
			case self::MULTISELECT:
				$result = Grid\Editor\Types::MULTISELECT;
				break;
			case self::MONEY:
				$result = Grid\Editor\Types::MONEY;
				break;
			case self::FILE:
				$result = Grid\Editor\Types::IMAGE;
				break;
			case self::HTML:
				$result = Grid\Editor\Types::CUSTOM;
				break;
		}

		return $result;
	}
}
