<?php

namespace Bitrix\Calendar\Core\Mappers;

use Bitrix\Calendar\Core\Builders\SectionBuilderFromDataManager;
use Bitrix\Calendar\Core;
use Bitrix\Calendar\Internals\EO_Section;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Calendar\Util;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\Security\Random;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\Emoji;
use Bitrix\Main\Type\DateTime;
use CCalendarSect;
use Exception;

class Section extends Mapper implements BaseMapperInterface
{
	public const SECTION_TYPE_LOCAL = 'local';

	public const DEFAULT_SORT = 100;

	/**
	 * @param Core\Section\Section $section
	 *
	 * @return array
	 */
	private function convertToArray(Core\Section\Section $section): array
	{
		return [
			'NAME'             => $section->getName(),
			'ACTIVE'           => $section->isActive() ? self::POSITIVE_ANSWER : self::NEGATIVE_ANSWER,
			'DESCRIPTION'      => $section->getDescription(),
			'COLOR'            => $section->getColor(),
			'TEXT_COLOR'       => $section->getTextColor(),
			'SORT'             => $section->getSort() ?? self::DEFAULT_SORT,
			'CAL_TYPE'         => $section->getType(),
			'OWNER_ID'         => !$section->getOwner() ?: $section->getOwner()->getId(),
			'TIMESTAMP_X'      => new DateTime,
			'XML_ID'           => $section->getXmlId(),
			'EXTERNAL_ID'      => $section->getExternalId(),
			'GAPI_CALENDAR_ID' => $section->getGoogleId(),
			'EXPORT'           => $section->getExport(),
			'CREATED_BY'       => !$section->getCreator() ?: $section->getCreator()->getId(),
			'PARENT_ID'        => $section->getParentId(),
			'DATE_CREATE'      => new DateTime,
			'DAV_EXCH_CAL'     => $section->getDavExchangeCal(),
			'DAV_EXCH_MOD'     => $section->getDavExchangeMod(),
			'CAL_DAV_CON'      => $section->getCalDavConnectionId(),
			'CAL_DAV_CAL'      => $section->getCalDavCal(),
			'CAL_DAV_MOD'      => $section->getCalDavMod(),
			'IS_EXCHANGE'      => $section->isExchange() ? 1 : 0,
			'SYNC_TOKEN'       => $section->getSyncToken(),
			'EXTERNAL_TYPE'    => $section->getExternalType(),
			'PAGE_TOKEN'       => $section->getPageToken(),
		];
	}

	/**
	 * @param array $filter
	 *
	 * @return Core\Section\Section|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function getOneEntityByFilter(array $filter): ?object
	{
		if ($sectionData = SectionTable::query()
			->setFilter($filter)
			->setSelect(['*'])
			->fetchObject()
		) {
			return $this->convertToObject($sectionData);
		}

		return null;
	}

	/**
	 * @param EO_Section $objectEO
	 *
	 * @return Core\Section\Section
	 */
	protected function convertToObject($objectEO): Core\Base\EntityInterface
	{
		return (new SectionBuilderFromDataManager($objectEO))->build();
	}

	/**
	 * @return string
	 */
	protected function getEntityName(): string
	{
		return 'section';
	}

	/**
	 * @param Core\Section\Section $entity
	 * @param array $params
	 *
	 * @return Core\Base\EntityInterface|null
	 *
	 * @throws Core\Base\BaseException
	 * @throws Exception
	 */
	protected function createEntity($entity, array $params = []): ?Core\Base\EntityInterface
	{
		$arrayEntity = $this->prepareArrayEntityForDB($entity);

		$result = SectionTable::add($arrayEntity);

		if ($result->isSuccess())
		{
			$this->sendPushEdit($entity->getOwner()->getId());
			$entity->setId((int)$result->getId());
			$entity->setXmlId($this->saveXmlId($result->getId(), $entity->getType()));

			return $entity;
		}

		throw new Core\Base\BaseException('Error of create section');
	}

	/**
	 * @param Core\Section\Section $entity
	 *
	 * @param array $params
	 *
	 * @return Core\Base\EntityInterface|null
	 * @throws Core\Base\BaseException
	 * @throws Exception
	 */
	protected function updateEntity($entity, array $params = []): ?Core\Base\EntityInterface
	{
		$arrayEntity = $this->prepareArrayEntityForDB($entity);

		$result = SectionTable::update(
			$entity->getId(),
			$arrayEntity
		);

		if ($result->isSuccess())
		{
			$this->sendPushEdit($entity->getOwner()->getId());
			return $entity->setDateModified(new Core\Base\Date());
		}

		throw new Core\Base\BaseException('Error of update section');
	}

	/**
	 * @return string
	 */
	protected function getMapClass(): string
	{
		return Core\Section\SectionMap::class;
	}

	/**
	 * @param Core\Section\Section $entity
	 * @param array $params
	 * [
	 * 		'softDelete' => mark section is not active
	 * ]
	 *
	 * @return Core\Base\EntityInterface|null
	 *
	 * @throws Core\Base\BaseException
	 */
	protected function deleteEntity(Core\Base\EntityInterface $entity, array $params = ['softDelete' => true]): ?Core\Base\EntityInterface
	{
		if (!empty($params['softDelete']))
		{
			$entity->setIsActive(false);

			return $this->updateEntity($entity, $params);
		}

		// TODO: change it to SectionTable::delete() after implementation all logic
		if (CCalendarSect::Delete($entity->getId(), false, $params))
		{
			return null;
		}

		throw new Core\Base\BaseException('Error of delete section');
	}

	/**
	 * @param array $params
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function getDataManagerResult(array $params): Result
	{
		return SectionTable::getList($params);
	}

	/**
	 * @param int $id
	 * @param string $type
	 *
	 * @return string
	 * @throws Exception
	 */
	private function saveXmlId(int $id, string $type): string
	{
		$xmlId = md5($type. '_'. $id. '_'. Random::getString(8));

		SectionTable::update($id, [
			'XML_ID' => $xmlId
		]);

		return $xmlId;
	}

	/**
	 * @param int $userId
	 *
	 * @return void
	 */
	private function sendPushEdit(int $userId): void
	{
		Util::addPullEvent('edit_section', $userId);
	}

	/**
	 * @param $entity
	 *
	 * @return array
	 */
	private function prepareArrayEntityForDB($entity): array
	{
		$arrayEntity = $this->convertToArray($entity);
		if (!empty($arrayEntity['NAME']))
		{
			$arrayEntity['NAME'] = Emoji::encode($arrayEntity['NAME']);
		}

		return $arrayEntity;
	}

	/**
	 * @return string
	 */
	protected function getEntityClass(): string
	{
		return Core\Section\Section::class;
	}
}