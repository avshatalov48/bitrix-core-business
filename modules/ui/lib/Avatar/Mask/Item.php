<?php
namespace Bitrix\UI\Avatar\Mask;

use Bitrix\Main;
use Bitrix\Main\FileTable;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Bitrix\UI\Avatar;

class ItemTable extends OrmDataManager
{
	public static function getTableName(): string
	{
		return 'b_ui_avatar_mask_item';
	}

	public static function getMap(): array
	{
		return array(
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),
			(new IntegerField('FILE_ID'))->configureRequired(),

			(new StringField('OWNER_TYPE'))->configureRequired()->configureSize(100),
			(new StringField('OWNER_ID', []))->configureRequired()->configureSize(20),

			new StringField('GROUP_ID'),

			new StringField('TITLE'),
			new TextField('DESCRIPTION'),
			(new IntegerField('SORT'))->configureDefaultValue(100),

			(new DatetimeField('TIMESTAMP_X'))
				->configureRequired()
				->configureDefaultValue(function() {
					return new DateTime();
				}),

			(new Reference(
				'FILE',
				FileTable::class,
				Join::on('this.FILE_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_INNER),

			(new Reference(
				'SHARED_FOR',
				Avatar\Mask\AccessTable::class,
				Join::on('this.ID', 'ref.ITEM_ID')
			))->configureJoinType(Join::TYPE_INNER),

			(new Reference(
				'RECENTLY_USED_BY',
				Avatar\Mask\RecentlyUsedTable::class,
				Join::on('this.ID', 'ref.ITEM_ID')
			))->configureJoinType(Join::TYPE_INNER)
		);
	}
}

class Item
{
	public const MAX_SIDE_SIZE = 1024; // in Pixels
	public const MAX_FILE_SIZE = 600000; // in Bites
	public const FILE_TYPES = ['png', 'gif'];
	public const ORM_OPERATION_LIMIT_COUNT = 2;
	private static array $buffer = [];
	protected int $id;
	protected array $data;
	protected Owner\DefaultOwner $owner;

	public function __construct(int $id)
	{
		if ($id > 0 && ($this->data = ItemTable::getById($id)->fetch()))
		{
			$this->id = $id;
			if (is_subclass_of($this->data['OWNER_TYPE'], Owner\DefaultOwner::class))
			{
				$this->owner = new $this->data['OWNER_TYPE']($this->data['OWNER_ID']);
			}
			else
			{
				throw new Main\ArgumentTypeException("Mask owner ({$this->data['OWNER_TYPE']}) is unreachable.");
			}
		}
		else
		{
			throw new Main\ObjectNotFoundException("Mask id ($id) is not found.");
		}
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function isEditableBy(Avatar\Mask\Consumer $consumer): bool
	{
		if ($consumer->isAdmin())
		{
			return true;
		}
		if ($this->getOwner() instanceof Owner\User && $this->getOwner()->getId() === $this->getId())
		{
			return true;
		}
		return false;
	}

	public function isReadableBy(Avatar\Mask\Consumer $consumer): bool
	{
		if ($consumer->isAdmin())
		{
			return true;
		}
		if ($this->getOwner() instanceof Owner\User && $this->getOwner()->getId() === $consumer->getId())
		{
			return true;
		}
		if (Avatar\Mask\AccessTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=ITEM_ID' => $this->getId(),
				'=ACCESS_CODE' => $consumer->getAccessCodes()
			]
		])->fetch())
		{
			return true;
		}
		return false;
	}

	public function update(array $data): Main\Result
	{
		$dataToSave = array_intersect_key($data, ['TITLE' => null, 'DESCRIPTION' => null]);
		if (array_key_exists('FILE', $data)
			&& !empty($data['FILE'])
		)
		{
			$file = $data['FILE'] + [
				'MODULE_ID' => 'ui',
				'del' => 'Y',
				'old_file' => $this->data['FILE_ID'],
			];

			if ($fileId = \CFile::SaveFile($file, 'ui/mask'))
			{
				$dataToSave['FILE_ID'] = $fileId;
			}
		}
		if (!empty($dataToSave))
		{
			ItemTable::update($this->getId(), $dataToSave);
		}

		if (array_key_exists('ACCESS_CODE', $data))
		{
			$this->setAccessCode($data['ACCESS_CODE']);
		}

		return new Main\Result();
	}

	public function delete(): Main\Result
	{
		return static::deleteByFilter(['ID' => $this->getId()]);
	}

	public function getOwner(): Owner\DefaultOwner
	{
		return $this->owner;
	}

	public function getAccessCode(): array
	{
		return array_column(AccessTable::getList([
			'select' => ['ACCESS_CODE'],
			'filter' => ['ITEM_ID' => $this->getId()]
		])->fetchAll(), 'ACCESS_CODE');
	}

	protected function setAccessCode(array $accessCodes)
	{
		AccessTable::deleteByFilter(['ITEM_ID' => $this->getId()]);
		if (!empty($accessCodes))
		{
			$itemId = $this->getId();
			AccessTable::addMulti(array_map(function($accessCode) use ($itemId) {
				return ['ITEM_ID' => $itemId, 'ACCESS_CODE' => $accessCode];
			}, $accessCodes), true);
		}
	}

	public function applyToFileBy(int $originalFileId, int $fileId, Avatar\Mask\Consumer $consumer)
	{

		return ItemToFileTable::add([
			'ITEM_ID' => $this->id,
			'ORIGINAL_FILE_ID' => $originalFileId,
			'FILE_ID' => $fileId,
			'USER_ID' => $consumer->getUserId()
		]);
	}

	public static function create(Owner\DefaultOwner $owner, array $file, ?array $descriptionParams = []): Main\Entity\AddResult
	{
		$result = new Main\Entity\AddResult();
		$fileCheckResult = \CFile::CheckImageFile($file,
			static::MAX_FILE_SIZE,
			static::MAX_SIDE_SIZE,
			static::MAX_SIDE_SIZE,
			['png', 'svg']
		);
		if ($fileCheckResult === null)
		{
			$file['MODULE_ID'] = 'ui';

			if ($fileId = \CFile::SaveFile($file, 'ui/mask'))
			{
				$result = ItemTable::add([
					'OWNER_TYPE' => get_class($owner),
					'OWNER_ID' => $owner->getId(),
					'GROUP_ID' => $descriptionParams['GROUP_ID'] ?? null,
					'TITLE' => $descriptionParams['TITLE'] ?? null,
					'DESCRIPTION' => $descriptionParams['DESCRIPTION'] ?? null,
					'SORT' => $descriptionParams['SORT'] ?? 0,
					'FILE_ID' => $fileId
				]);

				if ($result->isSuccess() && ($item = static::getInstance($result->getId())))
				{
					$item->setAccessCode($descriptionParams['ACCESS_CODE'] ?: $owner->getDefaultAccess());
					$result->setData([$item]);
					return $result;
				}
				\CFile::Delete($fileId);
			}
		}
		$result->addError(new Main\Error($fileCheckResult, 'image check'));
		return $result;
	}

	public static function getInstance($id): ?Item
	{
		try
		{
			return new static($id);
		}
		catch (Main\ObjectNotFoundException $exception)
		{
			return null;
		}
	}

	public static function deleteByFilter(array $filter): Main\Entity\DeleteResult
	{
		$result = new Main\Entity\DeleteResult();

		$connection = Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$sqlItemTableName = ItemTable::getTableName();
		$where = Main\ORM\Query\Query::buildFilterSql(ItemTable::getEntity(), $filter);
		if (empty($where))
		{
			return $result;
		}

		$sql = <<<SQL
INSERT IGNORE INTO b_ui_avatar_mask_file_deleted (ENTITY, ORIGINAL_FILE_ID, FILE_ID, ITEM_ID)
	SELECT 'ITEM_TEMP', FILE_ID, FILE_ID, ID
	FROM {$sqlHelper->quote($sqlItemTableName)}
	WHERE {$where}
;
DELETE ACCESS1
	FROM {$sqlHelper->quote(AccessTable::getTableName())} AS ACCESS1,
		b_ui_avatar_mask_file_deleted AS FDTABLE
	WHERE ACCESS1.ITEM_ID = FDTABLE.ITEM_ID AND FDTABLE.ENTITY = 'ITEM_TEMP'
;
DELETE RECENTLYUSED1
	FROM {$sqlHelper->quote(RecentlyUsedTable::getTableName())} AS RECENTLYUSED1,
		b_ui_avatar_mask_file_deleted AS FDTABLE
	WHERE RECENTLYUSED1.ITEM_ID = FDTABLE.ITEM_ID AND FDTABLE.ENTITY = 'ITEM_TEMP'
;
INSERT IGNORE INTO b_ui_avatar_mask_file_deleted (ENTITY, ORIGINAL_FILE_ID, FILE_ID, ITEM_ID)
	SELECT 'LINK', LINK1.ORIGINAL_FILE_ID, LINK1.FILE_ID, LINK1.ITEM_ID
	FROM {$sqlHelper->quote(ItemToFileTable::getTableName())} AS LINK1,
		b_ui_avatar_mask_file_deleted AS FDTABLE
	WHERE LINK1.ITEM_ID = FDTABLE.ITEM_ID AND FDTABLE.ENTITY = 'ITEM_TEMP'
;
DELETE LINK1
	FROM {$sqlHelper->quote(ItemToFileTable::getTableName())} AS LINK1,
		b_ui_avatar_mask_file_deleted AS FDTABLE
	WHERE LINK1.ITEM_ID = FDTABLE.ITEM_ID AND FDTABLE.ENTITY = 'ITEM_TEMP'
;
UPDATE b_ui_avatar_mask_file_deleted SET ENTITY = 'ITEM' WHERE ENTITY = 'ITEM_TEMP';
SQL;
		$connection->executeSqlBatch($sql);

		$cleaningString = static::clearFileTable();
		if ($cleaningString !== '')
		{
			\CAgent::AddAgent($cleaningString, 'ui','Y',0, '',
				'Y', '', 100, false, false
			);
		}
		return $result;
	}

	public static function clearFileTable(): string
	{
		$connection = Main\Application::getConnection();
		$limit = self::ORM_OPERATION_LIMIT_COUNT;
		$dbRes = $connection->query("
			SELECT * 
			FROM b_ui_avatar_mask_file_deleted 
			WHERE ENTITY IN ('ITEM', 'LINK') 
			ORDER BY ID ASC 
			LIMIT {$limit}
			"
		);
		$items = [];
		if ($res = $dbRes->fetch())
		{
			do
			{
				self::$buffer[] = $res['FILE_ID'];
				$items[] = $res['ITEM_ID'];
				\CFile::Delete($res['FILE_ID']);
			} while ($res = $dbRes->fetch());
			$itemsSql = implode(',', $items);
			$connection->queryExecute("DELETE FROM b_ui_avatar_mask_file_deleted WHERE ITEM_ID IN ({$itemsSql})");
		}

		if (count($items) < self::ORM_OPERATION_LIMIT_COUNT)
		{
			return '';
		}
		return '\\' . __CLASS__ . "::" . __FUNCTION__ . "();";
	}

	public static function onFileDelete($file)
	{
		if (in_array($file['ID'], self::$buffer))
		{
			return;
		}
		if ($file['MODULE_ID'] === 'ui')
		{
			ItemToFileTable::deleteByFilter(['=FILE_ID' => $file['ID']]);
		}
		else if ($file['MODULE_ID'] === 'main' && ItemToFileTable::getList([
				'select' => ['FILE_ID'],
				'filter' => ['=ORIGINAL_FILE_ID' => $file['ID']],
			])->fetch())
		{
			$connection = Main\Application::getConnection();
			$sqlHelper = $connection->getSqlHelper();
			$fileId = (int) $file['ID'];
			$sql = <<<SQL
INSERT IGNORE INTO b_ui_avatar_mask_file_deleted (ENTITY, ORIGINAL_FILE_ID, FILE_ID, ITEM_ID)
	SELECT 'LINK', LINK1.ORIGINAL_FILE_ID, LINK1.FILE_ID, LINK1.ITEM_ID
	FROM {$sqlHelper->quote(ItemToFileTable::getTableName())} AS LINK1
	WHERE LINK1.ORIGINAL_FILE_ID = {$fileId} 
;
DELETE LINK1
	FROM {$sqlHelper->quote(ItemToFileTable::getTableName())} AS LINK1
	WHERE LINK1.ORIGINAL_FILE_ID = {$fileId} 
;
SQL;
			$connection->executeSqlBatch($sql);

			$cleaningString = static::clearFileTable();
			if ($cleaningString !== '')
			{
				\CAgent::AddAgent($cleaningString, 'ui','Y',0, '',
					'Y', '', 100, false, false
				);
			}
		}
	}
}
