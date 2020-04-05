<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\ORM\Objectify;

/**
 * Object states registry
 * @package    bitrix
 * @subpackage main
 */
class State
{
	const RAW = 0;
	const ACTUAL = 1;
	const CHANGED = 2;
	const DELETED = 3;
}
