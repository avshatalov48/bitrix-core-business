<?php

namespace Bitrix\Im\V2\Integration\AI;

use Bitrix\AI\Context;
use Bitrix\AI\Engine;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

class Restriction
{
	public const AI_TEXT_TYPE = 'text';
	public const AI_IMAGE_TYPE = 'image';

	private string $type;

	public function __construct(string $type)
	{
		$this->type = $type;
	}

	public function isAvailable(): bool
	{
		if (!Loader::includeModule('ai'))
		{
			return false;
		}

		$engine = Engine::getByCategory($this->type, new Context('im', ''));

		if (is_null($engine))
		{
			return false;
		}

		$optionName = "ai_{$this->type}_available";

		return Option::get('im', $optionName, 'N') === 'Y';
	}
}