<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2025 Bitrix
 */

namespace Bitrix\Main\Web;

class HttpDebug
{
	public const REQUEST_HEADERS = 0b00000001;
	public const REQUEST_BODY = 0b00000010;
	public const REQUEST = 0b00000011;
	public const RESPONSE_HEADERS = 0b00000100;
	public const RESPONSE_BODY = 0b00001000;
	public const RESPONSE = 0b00001100;
	public const CONNECT = 0b00010000;
	public const DIAGNOSTICS = 0b00100000;
	public const ALL = 0b00111111;
	public const DEFAULT = self::CONNECT | self::REQUEST_HEADERS | self::RESPONSE_HEADERS;
}
