<?php

namespace Bitrix\Calendar\Sync\Factories;

use Bitrix\Calendar\Core\Base\Collection;
use Bitrix\Calendar\Core\Base\Map;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Core\Section\SectionMap;
use Bitrix\Calendar\Internals\EO_SectionConnection;
use Bitrix\Calendar\Internals\EventConnectionTable;
use Bitrix\Calendar\Internals\SectionConnectionTable;
use Bitrix\Calendar\Sync\Builders\BuilderEventConnectionFromDM;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\Server;
use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Sync\Util\Context;
use Bitrix\Dav\Internals\DavConnectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\SystemException;

class FactoriesCollection extends Collection
{
	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function createBySectionId(Section $section, array $availableService = null): FactoriesCollection
	{
		$collection = [];

		if (!Loader::includeModule('dav'))
		{
			return new self($collection);
		}
		// $links = self::getLinks($section, $availableService);
		$links = SectionConnectionTable::query()
			->setSelect([
				'*',
				'CONNECTION',
			])
			->addFilter('=SECTION_ID', $section->getId())
			->addFilter('=CONNECTION.IS_DELETED', 'N')
		;

		if ($availableService)
		{
			$links->addFilter('ACCOUNT_TYPE', $availableService);
		}

		$queryResult = $links->exec();

		while ($link = $queryResult->fetchObject())
		{
			$connection = (new Sync\Builders\BuilderConnectionFromDM($link->getConnection()))->build();
			$sectionConnection = (new Sync\Builders\BuilderSectionConnectionFromDM($link))->build();

			$context = self::prepareContextForSection($connection, $sectionConnection, $section);

			$collection[] = FactoryBuilder::create(
				$connection->getVendor()->getCode(),
				$connection,
				$context
			);
		}

		return new self($collection);
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function createByEvent(Event $event, array $availableService = null)
	{
		$collection = [];
		$links = self::getLinks($event->getSection(), $availableService);
		$eventLinks = self::getEventLinksByConnectionId($event);

		/** @var EO_SectionConnection $link */
		while ($link = $links->fetchObject())
		{
			$connection = (new Sync\Builders\BuilderConnectionFromDM($link->getConnection()))->build();
			$sectionConnection = (new Sync\Builders\BuilderSectionConnectionFromDM($link))->build();

			$context = self::prepareContextForSection($connection, $sectionConnection, $event->getSection());
			$context->add('sync', 'eventConnections', $eventLinks->getItem($connection->getId()));

			$collection[] = FactoryBuilder::create(
				$connection->getVendor()->getCode(),
				$connection,
				$context
			);
		}

		return new self($collection);
	}

	/**
	 * @param int $userId
	 * @param array $availableService
	 *
	 * @return FactoriesCollection
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function createByUserId(int $userId, array $availableService = []): FactoriesCollection
	{
		$collection = [];
		if (!Loader::includeModule('dav'))
		{
			return new self($collection);
		}

		if (!$availableService)
		{
			$availableService = [
				Sync\Google\Factory::SERVICE_NAME,
				Sync\Icloud\Factory::SERVICE_NAME,
				Sync\Office365\Factory::SERVICE_NAME,
			];
		}

		$sectionConnection = DavConnectionTable::query()
			->setSelect(['*'])
			->where('ENTITY_ID', $userId)
			->where('ENTITY_TYPE', 'user')
			->where('IS_DELETED', 'N')
			->whereIn('ACCOUNT_TYPE', $availableService)
			->exec()
		;
		$context = new Context([]);

		while ($connectionDM = $sectionConnection->fetchObject())
		{
			$connection = (new Sync\Builders\BuilderConnectionFromDM($connectionDM))->build();

			$collection[] = FactoryBuilder::create(
				$connection->getVendor()->getCode(),
				$connection,
				$context
			);
		}

		return new self($collection);
	}

	/**
	 * @param Section $section
	 *
	 * @return FactoriesCollection
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function createBySection(Section $section): FactoriesCollection
	{
		$collection = [];

		if (!Loader::includeModule('dav'))
		{
			return new self($collection);
		}

		$links = SectionConnectionTable::query()
			->setSelect([
				'*',
				'CONNECTION',
			])
			->where('SECTION_ID', $section->getId())
			->where('CONNECTION.IS_DELETED', 'N')
			->whereIn('CONNECTION.ACCOUNT_TYPE', [
				Sync\Google\Factory::SERVICE_NAME,
				Sync\Icloud\Factory::SERVICE_NAME,
				Sync\Office365\Factory::SERVICE_NAME,
			])
			->exec()
		;

		while ($link = $links->fetchObject())
		{
			$connection = (new Sync\Builders\BuilderConnectionFromDM($link->getConnection()))->build();
			$context = new Context([
				'section_sync_data' => $link,
			]);

			$collection[] = FactoryBuilder::create(
				$connection->getVendor()->getCode(),
				$connection,
				$context
			);
		}

		return new self(array_filter($collection));
	}

	/**
	 * @param Connection $connection
	 * @param Sync\Connection\SectionConnection $sectionConnection
	 * @param Section $section
	 *
	 * @return Context
	 *
	 * @throws ArgumentException
	 */
	private static function prepareContextForSection(
		Connection $connection,
		Sync\Connection\SectionConnection $sectionConnection,
		Section $section
	): Context
	{
		$connectionsMap = new Sync\Connection\ConnectionMap();
		$connectionsMap->add($connection, $connection->getId());

		$sectionConnectionsMap = new Sync\Connection\SectionConnectionMap();
		$sectionConnectionsMap->add($sectionConnection, $section->getId());

		$sectionsMap = new SectionMap();
		$sectionsMap->add($section, $section->getId());

		return new Context([
			'sections' => $sectionsMap,
			'sectionConnections' => $sectionConnectionsMap,
			'connections' => $connectionsMap
		]);
	}

	/**
	 * @param Connection $connection
	 *
	 * @return FactoriesCollection
	 */
	public static function createByConnection(Connection $connection): FactoriesCollection
	{
		$context = new Context([
			'connection_data' => $connection,
		]);
		$factory = FactoryBuilder::create($connection->getVendor()->getCode(), $connection, $context);

		return new self([$factory]);
	}

	/**
	 * @param Section $section
	 * @param array|null $availableService
	 * @return Query
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private static function getLinks(
		Section $section,
		?array $availableService
	): Query
	{
		Loader::includeModule('dav');
		$links = SectionConnectionTable::query()
			->setSelect([
				'*',
				'CONNECTION',
			])
			->addFilter('SECTION_ID', $section->getId())
			->addFilter('=CONNECTION.IS_DELETED', 'N')
		;

		if ($availableService)
		{
			$links->addFilter('ACCOUNT_TYPE', $availableService);
		}

		return $links;
	}

	private static function getServer($connection): Server
	{
		return new Server($connection);
	}

	/**
	 * @param Event $event
	 *
	 * @return Map
	 *
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	private static function getEventLinksByConnectionId(Event $event): Map
	{
		$links = EventConnectionTable::query()
			->setSelect(['*'])
			->addFilter('EVENT_ID', $event->getId())
			->exec()
		;

		$map = new Sync\Connection\EventConnectionMap();

		while ($link = $links->fetchObject())
		{
			$map->add((new BuilderEventConnectionFromDM($link))->build(), $link->getConnectionId());
		}

		return $map;
	}
}
