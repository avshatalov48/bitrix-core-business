<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Web\UserAgent;

interface DetectorInterface
{
	/**
	 * Detects a browser.
	 * @param string|null $userAgent UserAgent string.
	 * @return Browser
	 */
	public function detectBrowser(?string $userAgent): Browser;
}
