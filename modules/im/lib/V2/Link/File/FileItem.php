<?php

namespace Bitrix\Im\V2\Link\File;

use Bitrix\Im\Model\LinkFileTable;
use Bitrix\Im\Model\EO_LinkFile;
use Bitrix\Im\V2\Common\MigrationStatusCheckerTrait;
use Bitrix\Im\V2\Entity;
use Bitrix\Im\V2\Link\BaseLinkItem;
use Bitrix\Im\V2\Entity\File\FileError;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\RestEntity;
use Bitrix\Im\V2\Result;
use Bitrix\Main\ArgumentTypeException;

class FileItem extends BaseLinkItem
{
	use MigrationStatusCheckerTrait;

	protected static string $migrationOptionName = 'im_link_file_migration';

	protected string $subtype;

	/**
	 * @param int|array|EO_LinkFile|null $source
	 */
	public function __construct($source = null)
	{
		$this->initByDefault();

		if (!empty($source))
		{
			$this->load($source);
		}
	}

	public function save(): Result
	{
		if (!static::isMigrationFinished())
		{
			return new Result;
		}

		return parent::save();
	}

	protected function validateSubtype(): Result
	{
		if (\Bitrix\Im\V2\Entity\File\FileItem::isSubtypeValid($this->subtype))
		{
			return new Result;
		}

		return (new Result())->addError(new FileError(FileError::UNKNOWN_FILE_SUBTYPE));
	}

	public static function getEntityClassName(): string
	{
		return Entity\File\FileItem::class;
	}

	public static function getRestEntityName(): string
	{
		return 'link';
	}

	public function setSubtype(string $subtype): self
	{
		$this->subtype = $subtype;
		return $this;
	}

	public function getSubtype(): string
	{
		return $this->subtype;
	}

	public function fillFile(): self
	{
		$fileEntity = \Bitrix\Im\V2\Entity\File\FileItem::initByDiskFileId($this->getEntityId(), $this->getChatId());

		if ($fileEntity !== null)
		{
			$this->setEntity($fileEntity);
		}

		return $this;
	}

	public static function getDataClass(): string
	{
		return LinkFileTable::class;
	}

	public static function getByDiskFileId(int $diskFileId): ?self
	{
		$entity = LinkFileTable::query()
			->setSelect(['ID', 'MESSAGE_ID', 'CHAT_ID', 'SUBTYPE', 'DISK_FILE_ID', 'DATE_CREATE', 'AUTHOR_ID'])
			->where('DISK_FILE_ID', $diskFileId)
			->setLimit(1)
			->fetchObject()
		;

		if ($entity === null)
		{
			return null;
		}

		return (new static($entity))->fillFile();
	}

	public function setChatId(int $chatId): BaseLinkItem
	{
		if (isset($this->entity))
		{
			$this->getEntity()->setChatId($chatId);
		}

		return parent::setChatId($chatId);
	}

	/**
	 * @return Entity|\Bitrix\Im\V2\Entity\File\FileItem
	 */
	public function getEntity(): \Bitrix\Im\V2\Entity\File\FileItem
	{
		return $this->entity;
	}

	/**
	 * @param RestEntity $entity
	 * @return static
	 * @throws ArgumentTypeException
	 */
	public function setEntity(RestEntity $entity): self
	{
		if (!($entity instanceof \Bitrix\Im\V2\Entity\File\FileItem))
		{
			throw new ArgumentTypeException(get_class($entity));
		}
		$this->setSubtype($entity->getSubtype());

		return parent::setEntity($entity->setChatId($this->chatId ?? null));
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		return parent::getPopupData($excludedList)->add(new Entity\File\FilePopupItem($this->getEntity()));
	}

	protected static function getEntityIdFieldName(): string
	{
		return 'DISK_FILE_ID';
	}

	protected static function mirrorDataEntityFields(): array
	{
		$additionalFields = [
			'SUBTYPE' => [
				'field' => 'subtype',
				'set' => 'setSubtype', /** @see FileItem::setSubtype */
				'get' => 'getSubtype', /** @see FileItem::getSubtype */
				'beforeSave' => 'validateSubtype', /** @see FileItem::validateSubtype */
			]
		];

		return array_merge(parent::mirrorDataEntityFields(), $additionalFields);
	}

	public function toRestFormat(array $option = []): array
	{
		return [
			'id' => $this->getPrimaryId(),
			'messageId' => $this->getMessageId(),
			'chatId' => $this->getChatId(),
			'authorId' => $this->getAuthorId(),
			'dateCreate' => $this->getDateCreate()->format('c'),
			'fileId' => $this->getEntityId(),
			'subType' => mb_strtolower($this->subtype),
		];
	}
}