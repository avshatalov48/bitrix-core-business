<?php

declare(strict_types=1);

namespace Bitrix\SocialNetwork\Collab\Access;

use Bitrix\Socialnetwork\Permission\AccessDictionaryInterface;
use Bitrix\Socialnetwork\Helper\SingletonTrait;

class CollabDictionary implements AccessDictionaryInterface
{
	use SingletonTrait;

	public const CREATE = 'collab_create';
	public const UPDATE = 'collab_update';
	public const DELETE = 'collab_delete';
	public const INVITE = 'collab_invite';
	public const LEAVE = 'collab_leave';
	public const EXCLUDE = 'collab_exclude';
	public const VIEW = 'collab_view';
	public const EXCLUDE_MODERATOR = 'collab_exclude_moderator';
	public const SET_MODERATOR = 'collab_set_moderator';

	public const COPY_LINK = 'collab_copy_link';

	public function create(): string
	{
		return static::CREATE;
	}

	public function update(): string
	{
		return static::UPDATE;
	}

	public function delete(): string
	{
		return static::DELETE;
	}
}