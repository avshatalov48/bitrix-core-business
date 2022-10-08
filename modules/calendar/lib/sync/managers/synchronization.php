<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Internals\EventConnectionTable;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\EventConnection;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\Exceptions\RemoteAccountException;
use Bitrix\Calendar\Sync\Factories\FactoriesCollection;
use Bitrix\Calendar\Sync\Factories\FactoryInterface;
use Bitrix\Calendar\Sync\Util\Context;
use Bitrix\Calendar\Sync\Util\EventContext;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Calendar\Sync\Util\SectionContext;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class Synchronization
{
	/** @var VendorSynchronization[]  */
	private array $subManagers = [];
	/**
	 * @var FactoriesCollection
	 */
	private FactoriesCollection $factories;
	/**
	 * @var mixed
	 */
	private Core\Mappers\Factory $mapperFactory;

	/**
	 * @param FactoriesCollection $factories
	 *
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 */
	public function __construct(FactoriesCollection $factories)
	{
		$this->factories = $factories;
		$this->mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');

	}

	/**
	 * @param Event $event
	 * @param Context|null $context
	 *
	 * @return Result
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function createEvent(Event $event, Context $context = null): Result
	{
		return $this->execActionEvent('createEvent', $event, $context);
	}

	/**
	 * @param Event $event
	 * @param Context $context
	 *
	 * @return Result
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function updateEvent(Event $event, Context $context): Result
	{
		return $this->execActionEvent('updateEvent', $event, $context);
	}

	/**
	 * @param Event $event
	 * @param Context $context
	 *
	 * @return Result
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function deleteEvent(Event $event, Context $context): Result
	{
		return $this->execActionEvent('deleteEvent', $event, $context);
	}

	/**
	 * @param string $method
	 * @param Event $event
	 * @param Context $context
	 *
	 * @return Result
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function execActionEvent(string $method, Event $event, Context $context): Result
	{
		$mainResult = new Result();
		$data = [];
		/** @var FactoryInterface $factory */
		foreach ($this->factories as $factory)
		{
			if ($this->checkExclude($factory, $context, $method))
			{
				continue;
			}
			try
			{
				$vendorSync = $this->getVendorSynchronization($factory);
				$eventContext = $this->prepareEventContext($event, $context, $factory);
				$vendorResult = $vendorSync->$method($event, $eventContext);
				$data[$this->getVendorCode($factory)] = $vendorResult;
				if (!$vendorResult->isSuccess())
				{
					$mainResult->addErrors($vendorResult->getErrors());
				}
			}
			catch (RemoteAccountException $e)
			{
				$mainResult->addError(new Error($e->getMessage(), $e->getCode()));
			}

		}

		return $mainResult->setData($data);
	}

	/**
	 * @param Event $event
	 * @param Context $context
	 *
	 * @return Result
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function createInstance(Event $event, Context $context): Result
	{
		return $this->execActionEvent('createInstance', $event, $context);
	}

	/**
	 * @param Event $event
	 * @param Context $context
	 *
	 * @return Result
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function updateInstance(Event $event, Context $context): Result
	{
		return $this->execActionEvent('updateInstance', $event, $context);
	}

	/**
	 * @param Event $event
	 * @param Context $context
	 *
	 * @return Result
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function deleteInstance(Event $event, Context $context): Result
	{
		$mainResult = new Result();

		if (
			!isset($context->diff['EXDATE'])
			&& !isset($context->sync['excludeDate'])
			&& !$event->getExcludedDateCollection()->count()
		)
		{
			$mainResult->addError(new Error('Not found info about exclude date'));
			return $mainResult;
		}

		if (!isset($context->sync['excludeDate']))
		{
			$diff = is_array($context->diff['EXDATE'])
				? $context->diff['EXDATE']
				: explode(';', $context->diff['EXDATE']);
			$excludeDate = $context->sync['excludeDate'] ??
				reset(array_filter(
						$event->getExcludedDateCollection()->getCollection(),
						function($item) use ($diff)
						{
							return !in_array($item->format(\CCalendar::DFormat(false)), $diff);
						})
				);
			$context->add('sync', 'excludeDate', $excludeDate);
		}

		return $this->execActionEvent('deleteInstance', $event, $context);

	}

	/**
	 * @param string $method
	 * @param Section $section
	 * @param Context $context
	 *
	 * @return Result
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function execActionSection(
		string $method,
		Section $section,
		Context $context
	): Result
	{
		$mainResult = new Result();
		$resultData = [];
		/** @var FactoryInterface $factory */
		foreach ($this->factories as $factory)
		{
			if ($this->checkExclude($factory, $context, $method))
			{
				continue;
			}
			try
			{
				$vendorSync = $this->getVendorSynchronization($factory);
				$sectionContext = $this->prepareSectionContext($section, $context, $factory);
				$vendorResult = $vendorSync->$method($section, $sectionContext);
				$resultData[$this->getVendorCode($factory)] = $vendorResult;
				if (!$vendorResult->isSuccess())
				{
					$mainResult->addErrors($vendorResult->getErrors());
				}
			} catch (RemoteAccountException $e)
			{
				$mainResult->addError(new Error($e->getMessage(), $e->getCode()));
			}
		}

		return $mainResult->setData($resultData);
	}

	/**
	 * @param Section $section
	 * @param Context $context
	 *
	 * @return Result
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function createSection(Section $section, Context $context): Result
	{
		return $this->execActionSection('createSection', $section, $context);
	}

	/**
	 * @param Section $section
	 * @param Context $context
	 *
	 * @return Result
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function updateSection(Section $section, Context $context): Result
	{
		return $this->execActionSection('updateSection', $section, $context);
	}

	/**
	 * @param Section $section
	 * @param Context $context
	 *
	 * @return Result
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function deleteSection(Section $section, Context $context): Result
	{
		$result = $this->execActionSection('deleteSection', $section, $context);
		\CCalendarSect::cleanLinkTables($section->getId());

		return $result;
	}

	/**
	 * @param Event $event
	 * @param Connection $connection
	 * @param string $version
	 *
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function upEventVersion(Event $event, Connection $connection, string $version)
	{
		/** @var EventConnection $link */
		$link = $this->mapperFactory->getEventConnection()->getMap([
			'CONNECTION_ID' => $connection->getId(),
			'EVENT_ID' => $event->getId(),
		])->fetch();

		if ($link)
		{
			$link->setVersion((int)$version);
			EventConnectionTable::update($link->getId(), [
				'fields' => [
					'VERSION' => $version,
				]
			]);
		}
	}

	/**
	 * @param FactoryInterface $factory
	 *
	 * @return VendorSynchronization
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 */
	private function getVendorSynchronization(FactoryInterface $factory): VendorSynchronization
	{
		$key = $factory->getConnection()->getVendor()->getCode();
		if (empty($this->subManagers[$key]))
		{
			$this->subManagers[$key] =  new VendorSynchronization($factory);
		}

		return $this->subManagers[$key];
	}


	/**
	 * @param FactoryInterface $factory
	 * @param Context|null $context
	 * @param string $string
	 *
	 * @return bool
	 */
	private function checkExclude(
		FactoryInterface $factory,
		?Context $context,
		string $string = ''
	): bool
	{
		if (isset($context->sync)
			&& !empty($context->sync['originalFrom'])
			&& $context->sync['originalFrom'] === $this->getVendorCode($factory)
		)
		{
			return true;
		}

		return false;
	}

	/**
	 * @param FactoryInterface $factory
	 *
	 * @return string
	 */
	private function getVendorCode(FactoryInterface $factory): string
	{
		return $factory->getConnection()->getVendor()->getCode();
	}

	/**
	 * @param Event $event
	 * @param Context $context
	 * @param FactoryInterface $factory
	 *
	 * @return EventContext
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function prepareEventContext(Event $event, Context $context, FactoryInterface $factory): EventContext
	{
		$eventLink = $context->sync['eventLink'] ?? $this->getEventConnection($event, $factory);
		$sectionLink = $context->sync['sectionLink'] ?? $this->getSectionConnection($event->getSection(), $factory);

		return (new EventContext())
			->merge($context)
			->setSectionConnection($sectionLink)
			->setEventConnection($eventLink)
		;
	}

	/**
	 * @param Section $section
	 * @param Context $context
	 * @param FactoryInterface $factory
	 *
	 * @return Context|SectionContext
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function prepareSectionContext(Section $section, Context $context, FactoryInterface $factory)
	{
		$sectionLink = $context->sync['sectionLink'] ?? $this->getSectionConnection($section, $factory);

		return (new SectionContext())
			->merge($context)
			->setSectionConnection($sectionLink)
		;
	}

	/**
	 * @param Section $section
	 * @param FactoryInterface $factory
	 *
	 * @return SectionConnection|null
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function getSectionConnection(Section $section, FactoryInterface $factory): ?SectionConnection
	{
		return $this->mapperFactory->getSectionConnection()->getMap([
			'=SECTION_ID' => $section->getId(),
			'=CONNECTION_ID' => $factory->getConnection()->getId(),
		])->fetch();
	}

	/**
	 * @param Event $event
	 * @param FactoryInterface $factory
	 *
	 * @return EventConnection|null
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws SystemException
	 */
	private function getEventConnection(Event $event, FactoryInterface $factory): ?EventConnection
	{
		return $this->mapperFactory->getEventConnection()->getMap([
			'=EVENT_ID' => $event->getId(),
			'=CONNECTION_ID' => $factory->getConnection()->getId(),
		])->fetch();
	}
}
