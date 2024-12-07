<?php

namespace Bitrix\Main\Controller\Mail;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Mail\Providers;

class MailProviders extends Controller
{
	public function getShowcaseParamsAction(bool $isSender = false): array
	{
		$showcaseParams = new Providers\ShowcaseParams($isSender);

		return $showcaseParams->getParams();
	}
}