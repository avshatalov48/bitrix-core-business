<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Main\Web\Http;

use Bitrix\Main\Web\HttpDebug;

trait DebugInterfaceTrait
{
	protected int $debugLevel = HttpDebug::REQUEST_HEADERS | HttpDebug::RESPONSE_HEADERS;

	/**
	 * Sets debug level using HttpDebug::* constants.
	 * @param int $debugLevel
	 */
	public function setDebugLevel(int $debugLevel)
	{
		$this->debugLevel = $debugLevel;
	}

	/**
	 * Returns the current level.
	 * @return int HttpDebug::* constants
	 */
	public function getDebugLevel(): int
	{
		return $this->debugLevel;
	}
}
