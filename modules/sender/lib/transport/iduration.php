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
 * Interface iTransportDuration
 * @package Bitrix\Sender\Transport
 */
interface iDuration
{
	/**
	 * Get send duration in seconds.
	 *
	 * @param Message\Adapter|null $message Message.
	 *
	 * @return float
	 */
	public function getDuration(Message\Adapter $message = null);
}