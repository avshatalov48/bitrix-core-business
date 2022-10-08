<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync\Util\EventContext;
use Bitrix\Calendar\Sync\Util\SectionContext;
use Bitrix\Main\Result;

/** @deprecated  */
abstract class VendorSyncService
{
	/**
	 * @param Core\Event\Event $event
	 * @param EventContext $context
	 * @param Event $manager
	 * @return Result
	 */
	abstract public function createEvent(Core\Event\Event $event, EventContext $context, Event $manager): Result;

	/**
	 * @param Core\Event\Event $event
	 * @param EventContext $context
	 * @param Event $manager
	 * @return Result
	 */
	abstract public function updateEvent(Core\Event\Event $event, EventContext $context, Event $manager): Result;

	/**
	 * @param Core\Event\Event $event
	 * @param EventContext $context
	 * @param Event $manager
	 * @return Result
	 */
	abstract public function deleteEvent(Core\Event\Event $event, EventContext $context, Event $manager): Result;

	/**
	 * @param Core\Event\Event $event
	 * @param EventContext $context
	 * @param Event $manager
	 * @return Result
	 */
	abstract public function createInstance(Core\Event\Event $event, EventContext $context, Event $manager): Result;

	/**
	 * @param Core\Event\Event $event
	 * @param EventContext $context
	 * @param Event $manager
	 * @return Result
	 */
	abstract public function updateInstance(Core\Event\Event $event, EventContext $context, Event $manager): Result;

	/**
	 * @param Core\Section\Section $section
	 * @param SectionContext $context
	 * @param Section $manager
	 * @return Result
	 */
	abstract public function createSection(Core\Section\Section $section, SectionContext $context, Section $manager): Result;

	/**
	 * @param Core\Section\Section $section
	 * @param SectionContext $context
	 * @param Section $manager
	 * @return Result
	 */
	abstract public function updateSection(Core\Section\Section $section, SectionContext $context, Section $manager): Result;

	/**
	 * @param Core\Section\Section $section
	 * @param SectionContext $context
	 * @param Section $manager
	 * @return Result
	 */
	abstract public function deleteSection(Core\Section\Section $section, SectionContext $context, Section $manager): Result;
}