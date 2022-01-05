<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Diag;

interface LogFormatterInterface
{
	/**
	 * Formats a message.
	 * @param mixed $message
	 * @param array $context
	 * @return string
	 */
	public function format($message, array $context = []): string;
}
