<?php

namespace Bitrix\Main\Grid\Panel;

use ReflectionClass;

/**
 * Group actions panel control types
 * @package Bitrix\Main\Grid\Panel
 */
class Types
{
	public const DROPDOWN = 'DROPDOWN';
	public const CHECKBOX = 'CHECKBOX';
	public const TEXT = 'TEXT';
	public const BUTTON = 'BUTTON';
	public const LINK = 'LINK';
	public const CUSTOM = 'CUSTOM';
	public const HIDDEN = 'HIDDEN';
	public const DATE = 'DATE';

	/**
	 * Gets types list
	 *
	 * @return array
	 */
	public static function getList()
	{
		$reflection = new ReflectionClass(__CLASS__);

		return $reflection->getConstants();
	}
}
