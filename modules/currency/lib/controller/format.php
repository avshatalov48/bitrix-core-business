<?php

namespace Bitrix\Currency\Controller;

use Bitrix\Main\Engine\Controller;

class Format extends Controller
{
	public function getAction($currencyId)
	{
		return \CCurrencyLang::GetFormatDescription($currencyId);
	}
}
