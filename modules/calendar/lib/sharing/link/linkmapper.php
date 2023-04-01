<?php
namespace Bitrix\Calendar\Sharing\Link;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\EntityInterface;
use Bitrix\Calendar\Core\Mappers\Mapper;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;

abstract class LinkMapper extends Mapper
{
	/**
	 * @param array $params
	 * @return Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getDataManagerResult(array $params): Result
	{
		return SharingLinkTable::getList($params);
	}

	/**
	 * @param array $filter
	 * @return object|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getOneEntityByFilter(array $filter): ?object
	{
		$sharingLinkEO = SharingLinkTable::query()
			->setSelect(['*'])
			->setFilter($filter)
			->exec()
			->fetchObject();

		if ($sharingLinkEO === null)
		{
			return null;
		}

		return $this->convertToObject($sharingLinkEO);
	}

	/**
	 * @throws BaseException
	 * @throws \Exception
	 */
	protected function createEntity($entity, array $params = []): ?Link
	{
		$data = [
			'OBJECT_ID' => $entity->getObjectId(),
			'OBJECT_TYPE' => $entity->getObjectType(),
			'HASH' => $entity->getHash(),
			'OPTIONS' => $this->getOptionsJSON($entity),
			'DATE_CREATE' => new DateTime(),
			'ACTIVE' => $entity->isActive(),
		];

		$result = SharingLinkTable::add($data);
		if ($result->isSuccess())
		{
			return $entity->setId($result->getId());
		}

		throw new BaseException("Error of create {$this->getEntityName()}: "
			. implode('; ', $result->getErrorMessages()),
			400);
	}

	/**
	 * @throws BaseException
	 * @throws \Exception
	 */
	protected function updateEntity($entity, array $params = []): ?Link
	{
		$data = [
			'OPTIONS' => $this->getOptionsJSON($entity),
			'ACTIVE' => $entity->isActive(),
		];

		$result = SharingLinkTable::update($entity->getId(), $data);

		if ($result->isSuccess())
		{
			return $entity;
		}

		throw new BaseException("Error of update {$this->getEntityName()}: "
			. implode('; ', $result->getErrorMessages()),
			400);
	}

	/**
	 * @param EntityInterface $entity
	 * @param array $params
	 * @return Link|null
	 * @throws BaseException
	 */
	protected function deleteEntity(EntityInterface $entity, array $params): ?Link
	{
		$result = SharingLinkTable::delete($entity->getId());

		if ($result->isSuccess())
		{
			return null;
		}

		throw new BaseException("Error of delete {$this->getEntityName()}: "
			. implode('; ', $result->getErrorMessages()),
			400);
	}

	/**
	 * @param Link $sharingLink
	 * @return array
	 */
	public function convertToArray(Link $sharingLink): array
	{
		return [
			'id' => $sharingLink->getId(),
			'type' => $sharingLink->getObjectType(),
			'dateCreate' => $sharingLink->getDateCreate(),
			'hash' => $sharingLink->getHash(),
			'url' => $sharingLink->getUrl(),
			'active' => $sharingLink->isActive(),
		];
	}

	/**
	 * @param $entity
	 * @return string|null
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function getOptionsJSON($entity): ?string
	{
		$options = $this->getOptionsArray($entity);

		return Json::encode($options);
	}

	/**
	 * @param $entity
	 * @return array
	 */
	abstract protected function getOptionsArray($entity): array;
}