<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Option\Type;

use Bitrix\Main\Result;
use Bitrix\Main\Validation\Rule\InArray;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Option\AbstractOption;

class CanGuestScreenshotOption extends AbstractOption
{
	public const NAME = 'canGuestScreenshot';
	public const DB_NAME = 'CAN_GUEST_SCREENSHOT';

	public const DEFAULT_VALUE = 'Y';

	#[InArray(['Y', 'N'])]
	protected string $value;

	public function __construct(string $value)
	{
		parent::__construct(static::DB_NAME, strtoupper($value));
	}

	protected function applyImplementation(Collab $collab): Result
	{
		return new Result();
	}
}