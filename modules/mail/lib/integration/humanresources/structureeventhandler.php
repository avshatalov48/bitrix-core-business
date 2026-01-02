<?php

namespace Bitrix\Mail\Integration\HumanResources;

use Bitrix\HumanResources\Type\MemberEntityType;
use Bitrix\Mail\MailboxTable;
use Bitrix\Main\Event;
use Bitrix\HumanResources\Item\NodeMember;
use Bitrix\Main\Loader;

class StructureEventHandler
{
	public static function onMemberUpdated(Event $event): void
	{
		self::invalidateMailboxSharedCacheByMemberEvent($event);
	}

	public static function onMemberAdded(Event $event): void
	{
		self::invalidateMailboxSharedCacheByMemberEvent($event);
	}

	public static function onMemberDeleted(Event $event): void
	{
		self::invalidateMailboxSharedCacheByMemberEvent($event);
	}

	private static function invalidateMailboxSharedCacheByMemberEvent(Event $event): void
	{
		if (!Loader::includeModule('humanresources'))
		{
			return;
		}

		$member = $event->getParameter('member');
		if (!$member instanceof NodeMember || $member->entityType !== MemberEntityType::USER)
		{
			return;
		}

		MailboxTable::cleanUserSharedCache($member->entityId);
	}
}