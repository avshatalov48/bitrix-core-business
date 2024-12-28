<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Integration\IM;

enum ActionType: string
{
	case InviteGuest = 'invite_guest';
	case InviteUser = 'invite_user';
	case AddUser = 'add_user';
	case AddGuest = 'add_guest';
	case AcceptUser = 'accept_user';
	case CreateCollab = 'create_collab';
	case JoinUser = 'join_user';
	case LeaveUser = 'leave_user';
	case ExcludeUser = 'exclude_user';
	case CopyLink = 'copy_link';
	case RegenerateLink = 'regenerate_link';

	public static function values(): array
	{
		return array_map(static fn (ActionType $case): string => $case->value, self::cases());
	}

	public static function isValid(string $value): bool
	{
		return in_array($value, self::values(), true);
	}
}