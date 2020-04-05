<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2020 Bitrix
 */

namespace Bitrix\Sender\Message;

/**
 * Interface iBeforeAfter
 * @package Bitrix\Sender\Message
 */
interface iBeforeAfter
{
	public function onBeforeStart():\Bitrix\Main\Result;

	public function onAfterEnd():\Bitrix\Main\Result;
}