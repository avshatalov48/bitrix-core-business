<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Access\Exception;

class UnknownActionException extends AccessException
{
	/**
	 * @param string $actionName
	 */
	public function __construct(string $actionName)
	{
		parent::__construct("Unknown action {$actionName}");
	}
}
