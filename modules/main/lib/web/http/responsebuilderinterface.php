<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

namespace Bitrix\Main\Web\Http;

interface ResponseBuilderInterface
{
	/**
	 * @param string $response
	 * @return Response
	 */
	public function createFromString(string $response): Response;
}
