<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Transport;

use Bitrix\Sender\Message;

/**
 * Interface iLimitable
 * @package Bitrix\Sender\Transport
 */
interface iLimitation
{
	/**
	 * Get limiters.
	 *
	 * @param Message\iBase $message Message.
	 * @return iLimiter[]
	 */
	public function getLimiters(Message\iBase $message = null);
}