<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Permission;

use Bitrix\Socialnetwork\Helper\SingletonTrait;

class GroupDictionary implements AccessDictionaryInterface
{
	use SingletonTrait;

	public const CREATE = 'group_create';
	public const UPDATE = 'group_update';
	public const DELETE = 'group_delete';
	public const VIEW = 'group_view';

	public const DELETE_INCOMING_REQUEST = 'group_delete_incoming_request';
	public const DELETE_OUTGOING_REQUEST = 'group_delete_outgoing_request';
	public const PROCESS_INCOMING_REQUEST = 'group_process_incoming_request';

	public const EXCLUDE = 'group_exclude';
	public const JOIN = 'group_join';
	public const LEAVE = 'group_leave';

	public const REMOVE_MODERATOR = 'group_remove_moderator';
	public const SET_MODERATOR = 'group_set_moderator';

	public const SET_OWNER = 'group_set_owner';
	public const SET_SCRUM_MASTER = 'group_set_scrum_master';

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