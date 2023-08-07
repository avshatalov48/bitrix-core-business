<?php

namespace Bitrix\Main\Grid\UI\Request;

use Bitrix\Main\Grid\UI\GridRequest;
use Bitrix\Main\HttpRequest;

class GridRequestFactory
{
	/**
	 * Create grid request.
	 *
	 * @param HttpRequest $request
	 *
	 * @return GridRequest
	 */
	public function createFromRequest(HttpRequest $request): GridRequest
	{
		return new GridRequest($request);
	}
}
