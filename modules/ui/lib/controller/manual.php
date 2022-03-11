<?php

namespace Bitrix\UI\Controller;

use Bitrix\Main\Engine;
use Bitrix\UI\Util;

class Manual extends Engine\Controller
{
	public function getInitParamsAction(string $manualCode, array $urlParams)
	{
		$manualUrl = Util::getHelpdeskUrl(true) . '/manual/' . urlencode($manualCode) . '/';

		$url = \CHTTP::urlAddParams($manualUrl, $urlParams, ['encode' => true]);

		return [
			'url' => $url,
		];
	}
}