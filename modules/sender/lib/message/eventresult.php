<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2020 Bitrix
 */

namespace Bitrix\Sender\Message;

use Bitrix\Main\Result as MainResult;

/**
 * Class Result
 * @package Bitrix\Sender\Message
 */
class EventResult extends MainResult
{
	public function setSuccess($isSuccess)
	{
		$this->isSuccess = (bool)$isSuccess;
	}
}