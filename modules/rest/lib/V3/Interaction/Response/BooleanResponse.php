<?php

namespace Bitrix\Rest\V3\Interaction\Response;

class BooleanResponse extends Response
{
	/**
	 * @param bool $result
	 */
	public function __construct(public bool $result = true)
	{
		return $this;
	}
}
