<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Storage\Db;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Messenger\Entity\MessageBox;
use Bitrix\Main\Messenger\Entity\MessageInterface;
use Bitrix\Main\Messenger\Internals\Exception\Storage\MappingException;
use Bitrix\Main\Messenger\Internals\Storage\Db\Model\EO_MessengerMessage;
use Bitrix\Main\Messenger\Internals\Storage\Db\Model\MessageStatus;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Json;

class MessageMapper
{
	private const ENCODING_FLAGS = Json::DEFAULT_OPTIONS ^ JSON_UNESCAPED_UNICODE;

	private string $tableClass;

	public function __construct(Entity $tableEntity)
	{
		$this->tableClass = $tableEntity->getDataClass();
	}

	/**
	 * @throws MappingException
	 * @throws ArgumentException
	 */
	public function convertFromOrm(EntityObject $ormModel): MessageBox
	{
		/** @var EO_MessengerMessage $ormModel */
		$class = $ormModel->getClass();

		if (!class_exists($class))
		{
			throw new MappingException(
				sprintf('The class "%s" does not exist', $class)
			);
		}

		if (!is_subclass_of($class, MessageInterface::class))
		{
			throw new MappingException(
				sprintf('The class "%s" does not implement "%s"', $class, MessageInterface::class)
			);
		}

		/** @var MessageInterface $class */
		$message = $class::createFromData(Json::decode($ormModel->getPayload()));

		$messageBox = new MessageBox($message);

		$messageBox
			->setId($ormModel->getId())
			->setQueueId($ormModel->getQueueId())
			->setItemId($ormModel->getItemId())
			->setCreatedAt($ormModel->getCreatedAt())
			->setAvailableAt($ormModel->getAvailableAt())
			->setTtl($ormModel->getTtl());

		return $messageBox;
	}

	/**
	 * @throws SystemException
	 */
	public function convertToOrm(MessageBox $messageBox): EntityObject
	{
		/** @var DataManager|string $tableClass */
		$tableClass = $this->tableClass;
		/** @var EntityObject|string $entityObjectClass */
		$entityObjectClass = $tableClass::getObjectClass();

		/** @var EO_MessengerMessage $ormModel */
		$ormModel = $messageBox->getId() ? $entityObjectClass::wakeUp($messageBox->getId()) : $tableClass::createObject();

		$ormModel
			->setQueueId($messageBox->getQueueId())
			->setItemId($messageBox->getItemId())
			->setClass($messageBox->getClassName())
			->setPayload(Json::encode($messageBox->getMessage(), self::ENCODING_FLAGS))
			->setCreatedAt($messageBox->getCreatedAt())
			->setTtl($messageBox->getTtl())
			->setAvailableAt($messageBox->getAvailableAt());

		if ($messageBox->isRejected())
		{
			$ormModel->setStatus(MessageStatus::New->value);
		}

		return $ormModel;
	}
}
