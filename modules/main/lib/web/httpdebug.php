<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

namespace Bitrix\Main\Web;

class HttpDebug
{
	public const REQUEST_HEADERS = 0x01;
	public const REQUEST_BODY = 0x02;
	public const REQUEST = 0x03;
	public const RESPONSE_HEADERS = 0x04;
	public const RESPONSE_BODY = 0x08;
	public const RESPONSE = 0x0C;
	public const CONNECT = 0x10;
	public const ALL = 0x1F;
}
