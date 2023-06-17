<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Main\Web\Http;

interface DebugInterface
{
	/**
	 * Sets debug level using HttpDebug::* constants.
	 * @param int $debugLevel
	 */
	public function setDebugLevel(int $debugLevel);

	/**
	 * Returns the current level.
	 * @return int HttpDebug::* constants
	 */
	public function getDebugLevel(): int;
}
