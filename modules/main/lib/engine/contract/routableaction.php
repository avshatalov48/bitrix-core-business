<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2020 Bitrix
 */

namespace Bitrix\Main\Engine\Contract;

use Bitrix\Main\Engine\Controller;

interface RoutableAction
{
	/**
	 * @return string|Controller
	 */
	public static function getControllerClass();

	/**
	 * @return string
	 */
	public static function getDefaultName();
}