<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage vote
 * @copyright 2001-2019 Bitrix
 */
namespace Bitrix\Vote\Vote;

class Option
{
	const ALLOW_REVOTE = 1;
	const HIDE_RESULT = 2;
	/**
	 * Gets types list
	 * @return array
	 */
	public static function getList()
	{
		return (new \ReflectionClass(__CLASS__))->getConstants();
	}
}