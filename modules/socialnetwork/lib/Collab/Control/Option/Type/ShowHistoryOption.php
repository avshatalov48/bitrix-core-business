<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Option\Type;

use Bitrix\Main\Result;
use Bitrix\Main\Validation\Rule\InArray;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Option\AbstractOption;

class ShowHistoryOption extends AbstractOption
{
	public const NAME = 'showHistory';
	public const DB_NAME = 'SHOW_HISTORY';

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