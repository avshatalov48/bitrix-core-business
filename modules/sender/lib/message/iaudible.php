<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2019 Bitrix
 */

namespace Bitrix\Sender\Message;

/**
 * Interface iAudible
 * @package Bitrix\Sender\Message
 */
interface iAudible
{
	/**
	 * Check value of audio field and prepare it for DB
	 * @param string $optionCode Field code.
	 * @param string $newValue New field value.
	 * @return bool|string
	 */
	public function getAudioValue($optionCode, $newValue);
}