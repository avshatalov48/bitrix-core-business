<?php

namespace Bitrix\Main\Grid\Editor;


/**
 * Class Types. Inline editor field types
 * @package Bitrix\Main\Grid\Editor
 */
class Types
{
	public const DROPDOWN = 'DROPDOWN';
	public const CHECKBOX = 'CHECKBOX';
	public const TEXT = 'TEXT';
	public const DATE = 'DATE';
	public const NUMBER = 'NUMBER';
	public const RANGE = 'RANGE';
	public const TEXTAREA = 'TEXTAREA';
	public const CUSTOM = 'CUSTOM';
	public const IMAGE = 'IMAGE';
	public const MONEY = 'MONEY';
	public const MULTISELECT = 'MULTISELECT';

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
