<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2018 Bitrix
 */

namespace Bitrix\Sender\Message;

/**
 * Interface iHideable
 * @package Bitrix\Sender\Message
 */
interface iHideable
{
	/**
	 * Return true if is hidden.
	 *
	 * @return bool
	 */
	public function isHidden();
}