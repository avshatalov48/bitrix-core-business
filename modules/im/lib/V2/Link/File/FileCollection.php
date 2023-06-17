<?php

namespace Bitrix\Im\V2\Link\File;

use Bitrix\Im\V2\Entity\File\FileError;
use Bitrix\Im\V2\Entity\File\FilePopupItem;
use Bitrix\Im\V2\Entity\User\UserPopupItem;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Im\Model\LinkFileTable;
use Bitrix\Im\V2\Common\MigrationStatusCheckerTrait;
use Bitrix\Im\V2\Common\SidebarFilterProcessorTrait;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Service\Locator;
use Bitrix\Im\V2\Link\BaseLinkCollection;

/**
 * @method FileItem next()
 * @method FileItem current()
 * @method FileItem offsetGet($offset)
 */
class FileCollection extends BaseLinkCollection
{
	use SidebarFilterProcessorTrait;
	use MigrationStatusCheckerTrait;

	protected static string $migrationOptionName = 'im_link_file_migration';

	public static function getCollectionElementClass(): string
	{
		return FileItem::class;
	}

	public static function find(
		array $filter = [],
		array $order = ['ID' => 'DESC'],
		?int $limit = null,
		?Context $context = null
	): self
	{
		$context = $context ?? Locator::getContext();

		$fileOrder = ['ID' => 'DESC'];

		if (isset($order['ID']))
		{
			$fileOrder['ID'] = $order['ID'];
		}

		$query = LinkFileTable::query();
		static::addRightsCheckToQuery($query, $context->getUserId(), ['FILE.ID', 'FILE.CREATED_BY']);
		$query
			->setSelect(['ID', 'DISK_FILE_ID', 'SUBTYPE', 'AUTHOR_ID', 'MESSAGE_ID', 'CHAT_ID', 'DATE_CREATE'])
			->setOrder($fileOrder)
		;
		if (isset($limit))
		{
			$query->setLimit($limit);
		}
		static::processFilters($query, $filter, $fileOrder);
		$collection = new static($query->fetchCollection());
		$collection->fillFiles();

		return $collection;
	}

	public function fillFiles(): FileCollection
	{
		$diskFilesIds = $this->getEntityIds();

		$entities = \Bitrix\Im\V2\Entity\File\FileCollection::initByDiskFilesIds($diskFilesIds);

		foreach ($this as $file)
		{
			if ($entities->getById($file->getEntityId()) !== null)
			{
				$file->setEntity($entities->getById($file->getEntityId()));
			}
		}

		return $this;
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		$data = new PopupData([new UserPopupItem(), new FilePopupItem()], $excludedList);

		return parent::getPopupData($excludedList)->merge($data);
	}

	public function save(bool $isGroupSave = false): Result
	{
		if (!static::isMigrationFinished())
		{
			return (new Result())->addError(new FileError(FileError::SAVE_BEFORE_MIGRATION_ERROR));
		}

		return parent::save($isGroupSave);
	}

	protected static function processFilters(Query $query, array $filter, array $order): void
	{
		static::processSidebarFilters($query, $filter, $order);

		if (isset($filter['SEARCH_FILE_NAME']))
		{
			$query->whereLike('FILE.NAME', "{$filter['SEARCH_FILE_NAME']}%");
		}

		if (isset($filter['SUBTYPE']))
		{
			if (is_array($filter['SUBTYPE']))
			{
				$subtypes = array_filter($filter['SUBTYPE'], static fn (string $subtype) => \Bitrix\Im\V2\Entity\File\FileItem::isSubtypeValid($subtype));
				$query->whereIn('SUBTYPE', $subtypes);
			}
			elseif (\Bitrix\Im\V2\Entity\File\FileItem::isSubtypeValid($filter['SUBTYPE']))
			{
				$query->where('SUBTYPE', $filter['SUBTYPE']);
			}
		}
	}

	protected static function addRightsCheckToQuery(Query $query, int $userId, array $specificColumns): Query
	{
		$securityContext = new \Bitrix\Disk\Security\DiskSecurityContext($userId);
		$parameters = [];
		$parameters = \Bitrix\Disk\Driver::getInstance()
			->getRightsManager()
			->addRightsCheck($securityContext, $parameters, $specificColumns)
		;

		return $query->where($parameters['runtime'][0], 'expr', true);
	}
}