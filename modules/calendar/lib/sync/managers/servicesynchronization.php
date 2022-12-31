<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Handlers\HandlerBase;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Rooms\Util;
use Bitrix\Calendar\Sync\Connection\SectionConnectionMap;
use Bitrix\Calendar\Sync\Factories\FactoryInterface;
use Bitrix\Calendar\Sync\Util\Context;
use Bitrix\Calendar\Sync\Util\EventContext;
use Bitrix\Main\Result;

/**
 * @deprecated usage not found
 */
class ServiceSynchronization
{
	/**
	 * @var FactoryInterface|mixed
	 */
	private $factory;

	/**
	 * @param mixed $factory
	 */
	public function __construct(FactoryInterface $factory)
	{
		$this->factory = $factory;
	}

	/**
	 * @param Event $event
	 * @param Context $context
	 * @return Result
	 *
	 * @deprecated usage not found
	 */
	public function createEvent(Event $event, Context $context): Result
	{
		$manager = $this->factory->getEventManager();
		$eventContext = $this->prepareEventContextFromContext($context, $event->getSection());
		$actionResult = $manager->create($event, $eventContext);

		return $this->handleResult($actionResult);
	}

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 *
	 * @deprecated usage not found
	 */
	public function updateEvent(Event $event, EventContext $context): Result
	{
		$manager = $this->factory->getEventManager();
		$eventContext = $this->prepareEventContextFromContext($context, $event->getSection(), $event);
		$actionResult = $manager->update($event, $eventContext);

		return $this->handleResult($actionResult);
	}

	public function deleteEvent(Event $event, EventContext $context): Result
	{
		$manager = $this->factory->getEventManager();
		$eventContext = $this->prepareEventContextFromContext($context, $event->getSection());
		$actionResult = $manager->delete($event, $eventContext);

		return $this->handleResult($actionResult);
	}

	public function createInstance(Event $event, EventContext $context): Result
	{
		$manager = $this->factory->getEventManager();
		$eventContext = $this->prepareEventContextFromContext($context, $event->getSection());
		$actionResult = $manager->createInstance($event, $eventContext);

		return $this->handleResult($actionResult);
	}

	public function updateInstance(Event $event, EventContext $context): Result
	{
		$manager = $this->factory->getEventManager();
		$eventContext = $this->prepareEventContextFromContext($context, $event->getSection());
		$actionResult = $manager->updateInstance($event, $eventContext);

		return $this->handleResult($actionResult);
	}

	public function deleteInstance(Event $event, EventContext $context): Result
	{
		$manager = $this->factory->getEventManager();
		$eventContext = $this->prepareEventContextFromContext($context, $event->getSection());
		$actionResult = $manager->deleteInstance($event, $eventContext);

		return $this->handleResult($actionResult);
	}

	public function createSection(Section $section): Result
	{
		$manager = $this->factory->getSectionManager();
		$manager->create($section);

		return new Result();
	}

	public function updateSection(Section $section): Result
	{
		$manager = $this->factory->getSectionManager();
		$manager->update($section);

		return new Result();
	}

	public function deleteSection(Section $section): Result
	{
		$manager = $this->factory->getSectionManager();
		$manager->delete($section);

		return new Result();
	}

	/**
	 * @param Context $context
	 * @param Section $eventSection
	 * @param Event|null $event
	 * @return EventContext
	 */
	private function prepareEventContextFromContext(Context $context, Section $eventSection, Event $event = null): EventContext
	{
		$eventContext = new EventContext();
		/** @var SectionConnectionMap $sectionConnectionMap */
		$sectionConnectionMap = $context->sectionConnections;
		$sectionConnection = $sectionConnectionMap->getItem($eventSection->getId());

		if ($event)
		{
			if (isset($context->eventConnections))
			{
				$eventContext->setEventConnection($context->eventConnections->getItem($event->getId()));
			}

			if ($location = $event->getLocation())
			{
				$eventContext->location = Util::getTextLocation((string)$location);
			}
		}

		$eventContext->setSectionConnection($sectionConnection);

		return $eventContext;
	}

	/**
	 * @param Result $actionResult
	 * @return Result
	 */
	public function handleResult(Result $actionResult): Result
	{
		$result = new Result();
		if ($actionResult->isSuccess())
		{
			$result->setData($actionResult->getData());
		}
		else
		{
			$result->addErrors(
				$this->factory->handleErrors(
					HandlerBase::EVENT_TYPE,
					$actionResult->getErrorCollection()
				)
			);
		}

		return $result;
	}
}
