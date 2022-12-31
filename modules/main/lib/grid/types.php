<?php

namespace Bitrix\Main\Grid;


/**
 * Class Types. Column data types
 * @package Bitrix\Main\Grid
 */
class Types
{
	public const GRID_CHECKBOX = 'checkbox';
	public const GRID_TEXT = 'text';
	public const GRID_INT = 'int';
	public const GRID_CUSTOM = 'custom';
	public const GRID_LIST = 'list';
	public const GRID_GRID = 'grid';

	/**
	 * Gets types list
	 * @return array
	 */
	public static function getList()
	{
		$reflection = new \ReflectionClass(__CLASS__);

		return $reflection->getConstants();
	}
}
