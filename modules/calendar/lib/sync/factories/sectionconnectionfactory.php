<?php

namespace Bitrix\Calendar\Sync\Factories;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Builders\SectionBuilderFromDataManager;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Internals\EO_Section;
use Bitrix\Calendar\Internals\SectionConnectionTable;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Internals\EO_SectionConnection;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class SectionConnectionFactory
{
	/** @var Core\Mappers\Factory $mapperFactory */
	private $mapperFactory;
	public function __construct()
	{
		$this->mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
	}

	/**
	 * @param array $params
	 *
	 * @return SectionConnection|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws BaseException
	 */
	public function getSectionConnection(array $params): ?SectionConnection
	{
		$statement = SectionConnectionTable::query()->setSelect(['*']);
		if (!empty($params['filter']))
		{
			$statement->setFilter($params['filter']);
		}
		if ($link = $statement->fetchObject())
		{
			try {
				return $this->prepareLink(
					$link,
					$params['connectionObject'] ?? null,
					$params['sectionObject'] ?? null,
				);
			} catch (BaseException $e) {
			    return null;
			}
		}
		else
		{
			return null;
		}
	}

	/**
	 * @param Section $section
	 * @param Connection $connection
	 *
	 * @return SectionConnection|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getFromSectionConnection(Section $section, Connection $connection): ?SectionConnection
	{
		$linkData = SectionConnectionTable::query()
			->setSelect(['*'])
			->addFilter('CONNECTION_ID', $connection->getId())
			->addFilter('SECTION_ID', $section->getId())
			->exec()->fetchObject()
		;
		if (empty($linkData))
		{
			return null;
		}

		return $this->prepareLink($linkData, $connection, $section);
	}

	/**
	 * @param Connection $connection
	 * @param bool $onlyActive
	 *
	 * @return array
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getListByConnection(Connection $connection, bool $onlyActive = true): array
	{
		$statement = SectionConnectionTable::query();
		$statement->setSelect(['*']);
		$statement->addFilter('CONNECTION_ID', $connection->getId());
		if ($onlyActive)
		{
			$statement->addFilter('=ACTIVE', 'Y');
		}

		$links = $statement->exec();
		$result = [];
		while ($link = $links->fetchObject())
		{
			$result[] = $this->prepareLink($link, $connection);
		}

		return $result;
	}

	/**
	 * @param EO_SectionConnection $link
	 * @param Connection|null $connection
	 * @param Section|null $section
	 *
	 * @return SectionConnection
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws BaseException
	 */
	private function prepareLink(
		EO_SectionConnection $link,
		?Connection $connection = null,
		?Section $section = null
	): SectionConnection
	{
		if ($connection === null)
		{
			$connection = $this->mapperFactory->getConnection()->getById($link->getConnectionId());
		}
		$section = $section
			?? ($link->getSection()
				? $this->buildSection($link->getSection())
				: null
			);
		if ($section === null)
		{
			$section = $this->mapperFactory->getSection()->getById($link->getSectionId());

			if (!$section)
			{
				throw new BaseException('Section not found');
			}

		}

		$item = new SectionConnection();
		$item
			->setId($link->getId())
			->setSection($section)
			->setConnection($connection)
			->setVendorSectionId($link->getVendorSectionId())
			->setSyncToken($link->getSyncToken())
			->setPageToken($link->getPageToken())
			->setActive($link->getActive())
			->setLastSyncDate(new Core\Base\Date($link->getLastSyncDate()))
			->setLastSyncStatus($link->getLastSyncStatus())
			->setVersionId($link->get('VERSION_ID'))
		;

		return $item;
	}

	/**
	 * @param EO_Section $EOSection
	 *
	 * @return Section
	 */
	private function buildSection(EO_Section $EOSection): Section
	{
		return (new SectionBuilderFromDataManager($EOSection))->build();
	}
}
