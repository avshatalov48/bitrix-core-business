<?php

namespace Bitrix\Calendar\Core\Queue\Message;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Mappers\Mapper;
use Bitrix\Calendar\Internals\EO_QueueHandledMessage;
use Bitrix\Calendar\Internals\QueueHandledMessageTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Exception;

class HandledMessageMapper extends Mapper
{
	/**
	 * @return string
	 */
	protected function getEntityClass(): string
	{
		return HandledMessage::class;
	}

	/**
	 * @param array $params
	 *
	 * @return Result
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function getDataManagerResult(array $params): Result
	{
		$params['select'] = $params['select'] ?
			array_merge($params['select'], ['MESSAGE'])
			: ["*", 'MESSAGE'];
		$params['select'] = array_unique($params['select']);

		return QueueHandledMessageTable::getList($params);
	}

	/**
	 * @param array $filter
	 *
	 * @return object|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function getOneEntityByFilter(array $filter): ?object
	{
		/** @var EO_QueueHandledMessage $data */
		$data = $this->getDataManagerResult([
			'filter' => $filter,
			'select' => ['*', 'MESSAGE'],
			'limit' => 1
		])->fetchObject();

		if ($data)
		{
			return $this->convertToObject($data);
		}

		return null;
	}

	/**
	 * @param EO_QueueHandledMessage $objectEO
	 *
	 * @return HandledMessage|null
	 *
	 * @throws ObjectException
	 */
	protected function convertToObject($objectEO): ?Core\Base\EntityInterface
	{
		return (new BuilderHandledMessageFromDataManager($objectEO))->build();
	}

	/**
	 * @return string
	 */
	protected function getEntityName(): string
	{
		return 'handledQueueMessage';
	}

	/**
	 * @param HandledMessage $entity
	 * @param array $params
	 *
	 * @return HandledMessage|null
	 *
	 * @throws Core\Base\BaseException
	 * @throws Exception
	 */
	protected function createEntity($entity, array $params = []): ?Core\Base\EntityInterface
	{
		$data = $this->convertToArray($entity);
		$data['DATE_CREATE'] = new DateTime();
		$result = QueueHandledMessageTable::add($data);
		if ($result->isSuccess())
		{
			return $entity->setId($result->getId());
		}

		throw new Core\Base\BaseException('Error of create Queue handled message', 400);
	}

	/**
	 * @param HandledMessage $entity
	 * @param array $params
	 *
	 * @return HandledMessage|null
	 *
	 * @throws Exception
	 */
	protected function updateEntity($entity, array $params = []): ?Core\Base\EntityInterface
	{
		$data = $this->convertToArray($entity);
		$result = QueueHandledMessageTable::update($entity->getId(), $data);
		if ($result->isSuccess())
		{
			return $entity;
		}

		throw new Core\Base\BaseException('Error of update Queue handled message', 400);

	}

	/**
	 * @param HandledMessage $entity
	 * @param array $params
	 *
	 * @return HandledMessage|null
	 *
	 * @throws Exception
	 */
	protected function deleteEntity(Core\Base\EntityInterface $entity, array $params): ?Core\Base\EntityInterface
	{

		$result = QueueHandledMessageTable::delete($entity->getId());
		if ($result->isSuccess())
		{
			return null;
		}

		throw new Core\Base\BaseException('Error of delete Queue handled message');
	}

	/**
	 * @param HandledMessage $entity
	 *
	 * @return array
	 */
	private function convertToArray(HandledMessage $entity): array
	{
		return [
			'MESSAGE_ID' => $entity->getMessage()->getId(),
			'QUEUE_ID' => $entity->getQueue()->getQueueId(),
			'HASH' => $entity->getHash(),
		];
	}
}