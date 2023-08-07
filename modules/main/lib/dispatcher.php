<?php

namespace Bitrix\Main;

/**
 * @deprecated Use \Bitrix\Main\License
 */
final class Dispatcher
{
	public function initialize()
	{
	}

	public function getLicenseKey()
	{
		return Application::getInstance()->getLicense()->getKey();
	}
}
