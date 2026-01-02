<?php

namespace Bitrix\Socialnetwork\Collab\Control\Option\Type;

use Bitrix\Main\Result;
use Bitrix\Main\Validation\Rule\InArray;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Option\AbstractOption;
use Bitrix\Socialnetwork\Collab\Control\Option\Trait\UpdateCollabChatTrait;
use Bitrix\Socialnetwork\Collab\Permission\UserRole;

class ManageMessagesAutoDelete extends AbstractOption
{
	use UpdateCollabChatTrait;

	public const NAME = 'manageMessagesAutoDelete';
	public const DB_NAME = 'MANAGE_MESSAGES_AUTO_DELETE';

	/**
	 * @see Chat::getDefaultManageMessagesAutoDelete
	 */
	public const DEFAULT_VALUE = UserRole::MODERATOR;

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
			static::DB_NAME => static::ROLE_MAP[$this->value]
		]);
	}
}