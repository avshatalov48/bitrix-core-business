<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Converter;

use Bitrix\Main\Config\Option;

class ConverterFeature
{
	public static function isOn(): bool
	{
		return Option::get('socialnetwork', 'collab_converter', 'N') === 'Y';
	}
}
