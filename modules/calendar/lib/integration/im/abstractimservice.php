<?php

namespace Bitrix\Calendar\Integration\Im;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;

abstract class AbstractImService
{
	/**
	 * @throws LoaderException|NotSupportedException
	 */
	final protected function __construct()
	{
		if (!Loader::includeModule('im'))
		{
			throw new NotSupportedException('IM module is not installed');
		}
	}
}