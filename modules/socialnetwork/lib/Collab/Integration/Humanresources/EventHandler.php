<?php

namespace Bitrix\Socialnetwork\Collab\Integration\Humanresources;

use Bitrix\HumanResources\Item\NodeMember;
use Bitrix\HumanResources\Item\NodeRelation;
use Bitrix\HumanResources\Type\MemberEntityType;
use Bitrix\HumanResources\Type\RelationEntityType;
use Bitrix\Main\Event;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Socialnetwork\Collab\Integration\Humanresources\Service\StructureService;

/**
 * Handles events from the humanresources module.
 * Method names are corresponding to the event names.
 */
class EventHandler
{
	public static function OnRelationAdded(Event $event): void
	{
		/** @var NodeRelation $relation */
		$relation = $event->getParameter('relation');
		if (
			$relation->entityType !== RelationEntityType::COLLAB
			|| $relation->node === null
		)
		{
			return;
		}

		ServiceLocator::getInstance()
			->get(StructureService::class)
			->handleRelationAdded($relation)
		;
	}

	public static function OnMemberAdded(Event $event): void
	{
		/** @var NodeMember $member */
		$member = $event->getParameter('member');
		if ($member->entityType !== MemberEntityType::USER)
		{
			return;
		}

		ServiceLocator::getInstance()
			->get(StructureService::class)
			->handleMemberAdded($member)
		;
	}

	public static function OnRelationDeleted(Event $event): void
	{
		/** @var NodeRelation $relation */
		$relation = $event->getParameter('relation');
		if (
			$relation->entityType !== RelationEntityType::COLLAB
			|| $relation->node === null
		)
		{
			return;
		}

		ServiceLocator::getInstance()
			->get(StructureService::class)
			->handleRelationDeleted($relation)
		;
	}

	public static function OnMemberDeleted(Event $event): void
	{
		/** @var NodeMember $member */
		$member = $event->getParameter('member');
		if ($member->entityType !== MemberEntityType::USER)
		{
			return;
		}

		ServiceLocator::getInstance()
			->get(StructureService::class)
			->handleMemberDeleted($member)
		;
	}
}
