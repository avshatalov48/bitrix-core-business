<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Main\Web\Http\Curl;

use Bitrix\Main\Web\Http;

class Promise extends Http\Promise
{
	/**
	 * @param Handler $handler
	 * @param Http\Queue $queue
	 */
	public function __construct(Handler $handler, Http\Queue $queue)
	{
		parent::__construct($handler, $queue);

		$this->id = spl_object_hash($handler->getHandle());
	}

	/**
	 * @return Handler
	 */
	public function getHandler(): Handler
	{
		return $this->handler;
	}
}
