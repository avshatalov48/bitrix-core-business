<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Main\Web\Http;

use Bitrix\Main;
use Psr\Http\Message\RequestInterface;

class RequestEventResult extends Main\EventResult
{
	protected RequestInterface $request;

	public function __construct(RequestInterface $request)
	{
		parent::__construct(parent::SUCCESS);

		$this->request = $request;
	}

	public function getRequest(): RequestInterface
	{
		return $this->request;
	}
}
