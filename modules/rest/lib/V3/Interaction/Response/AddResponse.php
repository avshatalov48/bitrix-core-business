<?php

namespace Bitrix\Rest\V3\Interaction\Response;

class AddResponse extends Response
{
	/**
	 * @param int $id
	 */
	public function __construct(public int $id)
	{
    }
}
