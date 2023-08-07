<?php

namespace Bitrix\Main\Grid\Panel;

use ReflectionClass;

/**
 * JS actions of controls.
 *
 * @package Bitrix\Main\Grid\Panel
 */
class Actions
{
	public const CREATE = 'CREATE';
	public const SEND = 'SEND';
	public const ACTIVATE = 'ACTIVATE';
	public const SHOW = 'SHOW';
	public const HIDE = 'HIDE';
	public const REMOVE = 'REMOVE';
	public const CALLBACK = 'CALLBACK';
	public const INLINE_EDIT = 'INLINE_EDIT';
	public const HIDE_ALL_EXPECT = 'HIDE_ALL_EXPECT';
	public const SHOW_ALL = 'SHOW_ALL';
	public const RESET_CONTROLS = 'RESET_CONTROLS';

	/**
	 * Gets types list of actions
	 *
	 * @return array
	 */
	public static function getList()
	{
		$reflection = new ReflectionClass(__CLASS__);

		return $reflection->getConstants();
	}
}
