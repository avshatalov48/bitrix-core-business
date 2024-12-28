<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Option\Type;

use Bitrix\Im\V2\Chat;
use Bitrix\Main\Validation\Rule\InArray;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Option\AbstractOption;
use Bitrix\Socialnetwork\Collab\Control\Option\Trait\UpdateCollabChatTrait;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Permission\UserRole;

class ManageMessagesOption extends AbstractOption
{
	use UpdateCollabChatTrait;

	public const NAME = 'manageMessages';
	public const DB_NAME = 'MANAGE_MESSAGES';

	public const DEFAULT_VALUE = UserRole::MEMBER;

	/** @see Chat::MANAGE_RIGHTS_OWNER  */
	protected const ROLE_MAP = [
		UserRole::MEMBER => 'MEMBER',
		UserRole::MODERATOR => 'MANAGER',
		UserRole::OWNER => 'OWNER',
	];

	#[InArray(UserRole::ALLOWED_ROLES)]
	protected string $value;

	public function __construct(string $value)
	{
		parent::__construct(static::DB_NAME, strtoupper($value));
	}

	protected function applyImplementation(Collab $collab): Result
	{
		return $this->updateChat($collab, [
			'MANAGE_MESSAGES' => static::ROLE_MAP[$this->value]
		]);
	}
}
