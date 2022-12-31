<?php

namespace Bitrix\Calendar\Core\Queue\Message;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Mappers\Mapper;
use Bitrix\Calendar\Internals\EO_QueueMessage;
use Bitrix\Calendar\Internals\QueueMessageTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Exception;

class MessageMapper extends Mapper
{

	/**
	 * @return string
	 */
	protected function getEntityClass(): string
	{
		return Message::class;
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
		$params['select'] = $params['select'] ?? ["*"];
		return QueueMessageTable::getList($params);
	}

	/**
	 * @param array $filter
	 *
	 * @return Message|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function getOneEntityByFilter(array $filter): ?object
	{
		/** @var EO_QueueMessage $messageData */
		$messageData = $this->getDataManagerResult([
			'filter' => $filter,
			'select' => ['*'],
			'limit' => 1
		])->fetchObject();

		if ($messageData)
		{
			return $this->convertToObject($messageData);
		}

		return null;
	}

	/**
	 * @param EO_QueueMessage $objectEO
	 *
	 * @return Message|null
	 */
	protected function convertToObject($objectEO): ?Core\Base\EntityInterface
	{
		return (new BuilderMessageFromDataManager($objectEO))->build();
	}

	/**
	 * @return string
	 */
	protected function getEntityName(): string
	{
		return 'queueMessage';
	}

	/**
	 * @param Message $entity
	 * @param array $params
	 *
	 * @return Message|null
	 *
	 * @throws Core\Base\BaseException
	 * @throws Exception
	 */
	protected function createEntity($entity, array $params = []): ?Core\Base\EntityInterface
	{
		$data = $this->convertToArray($entity);
		$data['DATE_CREATE'] = new DateTime();
		$result = QueueMessageTable::add($data);
		if ($result->isSuccess())
		{
			return $entity->setId($result->getId());
		}

		throw new Core\Base\BaseException('Error of create Queue message', 400);
	}

	/**
	 * @param Message $entity
	 * @param array $params
	 *
	 * @return Core\Base\EntityInterface|null
	 *
	 * @throws Core\Base\BaseException
	 * @throws Exception
	 */
	protected function updateEntity($entity, array $params = []): ?Core\Base\EntityInterface
	{
		$data = $this->convertToArray($entity);
		$result = QueueMessageTable::update($entity->getId(), $data);
		if ($result->isSuccess())
		{
			return $entity;
		}

		throw new Core\Base\BaseException('Error of update Queue message', 400);
	}

	/**
	 * @param Message $entity
	 * @param array $params
	 *
	 * @return Core\Base\EntityInterface|null
	 *
	 * @throws Core\Base\BaseException
	 * @throws Exception
	 */
	protected function deleteEntity(Core\Base\EntityInterface $entity, array $params): ?Core\Base\EntityInterface
	{
		$result = QueueMessageTable::delete($entity->getId());
		if ($result->isSuccess())
		{
			return null;
		}

		throw new Core\Base\BaseException('Error of delete Queue message');
	}

	/**
	 * @param Message $entity
	 *
	 * @return array
	 */
	private function convertToArray(Message $entity): array
	{
		return [
			'MESSAGE' => [
				Dictionary::MESSAGE_PARTS['body'] => $entity->getBody(),
				Dictionary::MESSAGE_PARTS['headers'] => $entity->getHeaders(),
				Dictionary::MESSAGE_PARTS['properties'] => $entity->getProperties(),
			],
		];
	}
}