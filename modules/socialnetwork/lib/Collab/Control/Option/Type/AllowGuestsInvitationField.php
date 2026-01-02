<?php

namespace Bitrix\Socialnetwork\Collab\Control\Option\Type;

use Bitrix\Socialnetwork\Collab\Control\Option\AbstractOption;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Main\Validation\Rule\InArray;
use Bitrix\Main\Result;

class AllowGuestsInvitationField extends AbstractOption
{
	public const NAME = 'allowGuestsInvitation';
	public const DB_NAME = 'ALLOW_GUESTS_INVITATION';

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