<?php

namespace Bitrix\Main\Web\Http\Socket;

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

		$this->id = spl_object_hash($this);
	}

	/**
	 * @return Handler
	 */
	public function getHandler(): Handler
	{
		return $this->handler;
	}
}
