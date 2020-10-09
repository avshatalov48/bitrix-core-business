<?php

namespace Bitrix\Main\Service\MicroService;

use Bitrix\Main\Engine\Controller;

class BaseReceiver extends Controller
{
	protected function getDefaultPreFilters()
	{
		return [
			new Filter\Authorization(),
			new Filter\ParametersUnpacking()
		];
	}
}