<?php

namespace Bitrix\Im\V2\Link\File;

use Bitrix\Disk\TypeFile;
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

	public const MEDIA_SUBTYPE = 'MEDIA';
	public const AUDIO_SUBTYPE = 'AUDIO';
	public const BRIEF_SUBTYPE = 'BRIEF';
	public const OTHER_SUBTYPE = 'OTHER';
	public const DOCUMENT_SUBTYPE = 'DOCUMENT';
	public const BRIEF_CODE = 'resume';
	public const MEDIA_ORIGINAL_CODE = 'media_original';

	protected static string $migrationOptionName = 'im_link_file_migration';

	protected Subtype $subtype;

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

	protected function resolveSubtype(): Result
	{
		if (!isset($this->subtype))
		{
			$this->getSubtype();
		}

		return new Result();
	}

	public static function getEntityClassName(): string
	{
		return Entity\File\FileItem::class;
	}

	public static function getRestEntityName(): string
	{
		return 'link';
	}

	protected function saveSubtypeValueFilter(Subtype $subtype): string
	{
		return $subtype->value;
	}

	protected function loadSubtypeValueFilter(string $subtype): Subtype
	{
		return Subtype::tryFromOrDefault($subtype);
	}

	public function setSubtype(Subtype|string $subtype): self
	{
		if (is_string($subtype))
		{
			$this->subtype = Subtype::tryFromOrDefault($subtype);
		}
		else
		{
			$this->subtype = $subtype;
		}

		return $this;
	}

	public function getSubtype(): Subtype
	{
		$this->subtype ??= $this->calculateSubtype();

		return $this->subtype;
	}

	protected function calculateSubtype(): Subtype
	{
		$this->fillFile();

		if (!isset($this->entity))
		{
			return Subtype::Other;
		}

		$diskFile = $this->getEntity()->getDiskFile();
		$realFile = $diskFile?->getRealObject() ?? $diskFile;

		if ($realFile?->getCode() === static::BRIEF_CODE)
		{
			return Subtype::Brief;
		}

		if ($realFile?->getCode() === static::MEDIA_ORIGINAL_CODE)
		{
			return Subtype::Other;
		}

		return Subtype::tryFromDiskFileType($diskFile?->getTypeFile());
	}

	public function getSubtypeGroup(): ?SubtypeGroup
	{
		return SubtypeGroup::tryFromSubtype($this->getSubtype());
	}

	public function fillFile(): self
	{
		if (isset($this->entity))
		{
			return $this;
		}

		$fileEntity = Entity\File\FileItem::initByDiskFileId($this->getEntityId(), $this->getChatId());

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

	/**
	 * @return null|string|string[]
	 */
	public static function normalizeFilterFromJsonFormat(null|string|array $filter): null|string|array
	{
		if (is_string($filter))
		{
			return mb_strtoupper($filter);
		}

		if (is_array($filter))
		{
			return array_map('mb_strtoupper', $filter);
		}

		return null;
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
	 * @return Entity|Entity\File\FileItem
	 */
	public function getEntity(): Entity\File\FileItem
	{
		$this->fillFile();

		return $this->entity;
	}

	/**
	 * @param RestEntity $entity
	 * @return static
	 * @throws ArgumentTypeException
	 */
	public function setEntity(RestEntity $entity): self
	{
		if (!($entity instanceof Entity\File\FileItem))
		{
			throw new ArgumentTypeException(get_class($entity));
		}

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
				'beforeSave' => 'resolveSubtype', /** @see FileItem::resolveSubtype */
				'saveFilter' => 'saveSubtypeValueFilter', /** @see FileItem::saveSubtypeValueFilter */
				'loadFilter' => 'loadSubtypeValueFilter' /** @see  FileItem::loadSubtypeValueFilter */
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
			'subType' => $this->getSubtype(),
			'group' => $this->getSubtypeGroup(),
		];
	}
}