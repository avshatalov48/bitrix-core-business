<?php

namespace Bitrix\Bizproc\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Localization\Loc;

abstract class Base extends Controller
{
	protected function init()
	{
		Loc::loadLanguageFile(__FILE__);
		parent::init();
	}
}