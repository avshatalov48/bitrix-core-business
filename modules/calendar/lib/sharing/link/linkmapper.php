<?php
namespace Bitrix\Calendar\Sharing\Link;

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
			'DATE_EXPIRE' => $entity->getDateExpire(),
		];
		$data = array_merge($data, $this->getSpecificFields($entity));

		$result = SharingLinkTable::add($data);
		if ($result->isSuccess())
		{
			return $entity->setId($result->getId());
		}

		return null;
	}

	/**
	 * @throws \Exception
	 */
	protected function updateEntity($entity, array $params = []): ?Link
	{
		$data = [
			'OPTIONS' => $this->getOptionsJSON($entity),
			'ACTIVE' => $entity->isActive(),
			'DATE_EXPIRE' => $entity->getDateExpire(),
		];
		$data = array_merge($data, $this->getSpecificFields($entity));

		$result = SharingLinkTable::update($entity->getId(), $data);

		if ($result->isSuccess())
		{
			return $entity;
		}

		return null;
	}

	/**
	 * @param EntityInterface $entity
	 * @param array $params
	 * @return Link|null
	 */
	protected function deleteEntity(EntityInterface $entity, array $params): ?Link
	{
		SharingLinkTable::delete($entity->getId());

		return null;
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

		$result = null;
		if (!empty($options))
		{
			$result = Json::encode($options);
		}
		return $result;
	}

	/**
	 * @param $entity
	 * @return array
	 */
	abstract protected function getOptionsArray($entity): array;

	/**
	 * @param $entity
	 * @return array
	 */
	abstract protected function getSpecificFields($entity): array;
}