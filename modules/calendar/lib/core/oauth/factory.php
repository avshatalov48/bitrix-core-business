<?php

namespace Bitrix\Calendar\Core\Oauth;

use Bitrix\Calendar\Core\Base\SingletonTrait;
use Bitrix\Main\LoaderException;

class Factory
{
	use SingletonTrait;

	/**
	 * @throws LoaderException
	 */
	public function getByName($name): Base|null
	{
		return match ($name)
		{
			'google' => Google::getInstance(),
			'office365' => Office365::getInstance(),
			default => null,
		};
	}
}