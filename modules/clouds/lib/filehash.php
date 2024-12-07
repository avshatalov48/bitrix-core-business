<?php
namespace Bitrix\Clouds;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Fields\ExpressionField;

Loc::loadMessages(__FILE__);

/**
 * <ul>
 * <li> ID integer mandatory
 * <li> BUCKET_ID integer mandatory
 * <li> FILE_PATH string(600) mandatory
 * <li> FILE_SIZE int optional
 * <li> FILE_MTIME datetime optional
 * <li> FILE_HASH string(50) optional
 * </ul>
 *
 * @package Bitrix\Clouds
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_FileHash_Query query()
 * @method static EO_FileHash_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_FileHash_Result getById($id)
 * @method static EO_FileHash_Result getList(array $parameters = array())
 * @method static EO_FileHash_Entity getEntity()
 * @method static \Bitrix\Clouds\EO_FileHash createObject($setDefaultValues = true)
 * @method static \Bitrix\Clouds\EO_FileHash_Collection createCollection()
 * @method static \Bitrix\Clouds\EO_FileHash wakeUpObject($row)
 * @method static \Bitrix\Clouds\EO_FileHash_Collection wakeUpCollection($rows)
 */

class FileHashTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_clouds_file_hash';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('FILE_HASH_ENTITY_ID_FIELD'),
				]
			),
			new IntegerField(
				'BUCKET_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('FILE_HASH_ENTITY_BUCKET_ID_FIELD'),
				]
			),
			new StringField(
				'FILE_PATH',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateFilePath'],
					'title' => Loc::getMessage('FILE_HASH_ENTITY_FILE_PATH_FIELD'),
				]
			),
			new IntegerField(
				'FILE_SIZE',
				[
					'title' => Loc::getMessage('FILE_HASH_ENTITY_FILE_SIZE_FIELD'),
				]
			),
			new DatetimeField(
				'FILE_MTIME',
				[
					'title' => Loc::getMessage('FILE_HASH_ENTITY_FILE_MTIME_FIELD'),
				]
			),
			new StringField(
				'FILE_HASH',
				[
					'validation' => [__CLASS__, 'validateFileHash'],
					'title' => Loc::getMessage('FILE_HASH_ENTITY_FILE_HASH_FIELD'),
				]
			),
		];
	}

	/**
	 * Returns validators for FILE_PATH field.
	 *
	 * @return array
	 */
	public static function validateFilePath()
	{
		return [
			new LengthValidator(null, 760),
		];
	}

	/**
	 * Returns validators for FILE_HASH field.
	 *
	 * @return array
	 */
	public static function validateFileHash()
	{
		return [
			new LengthValidator(null, 50),
		];
	}

	/**
	 * Stores file hashes to the database.
	 *
	 * @param int $bucketId Clouds storage bucket identifier.
	 * @param array $files File list as it returned by \CCloudStorageBucket::ListFiles.
	 *
	 * @return \Bitrix\Main\DB\Result
	 * @see \CCloudStorageBucket::ListFiles
	 */
	public static function addList($bucketId, array $files)
	{
		$bucketId = intval($bucketId);
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		$values = [];
		foreach ($files['file'] as $i => $file)
		{
			$fileSize = $files['file_size'][$i];
			$fileMtime = \CCloudUtil::gmtTimeToDateTime($files['file_mtime'][$i]);
			$fileHash = $files['file_hash'][$i];
			$values [] = '('
				. $bucketId
				. ",'" . $helper->forSql($file) . "'"
				. ',' . intval($fileSize)
				. ",'" . $fileMtime->format('Y-m-d h:i:s') . "'"
				. ",'" . $helper->forSql($fileHash) . "'"
				. ')'
			;
		}
		$sql = '
			INSERT INTO ' . static::getTableName() . '
			(BUCKET_ID, FILE_PATH, FILE_SIZE, FILE_MTIME, FILE_HASH)
			VALUES
			' . implode(",\n", $values) . '
		';
		return $connection->query($sql);
	}

	/**
	 * Sync file hashes to the database. Adds new keys and removes missing between $files[last_key] and $lastKey.
	 *
	 * @param int $bucketId Clouds storage bucket identifier.
	 * @param string $path File list relative path.
	 * @param array $files File list as it returned by CCloudStorageBucket::ListFiles.
	 * @param string $prevLastKey Last key returned by previous call to CCloudStorageBucket::ListFiles.
	 *
	 * @return null|\Bitrix\Main\DB\Result
	 * @see \CCloudStorageBucket::ListFiles
	 */
	public static function syncList($bucketId, $path, array $files, $prevLastKey)
	{
		$result = null;
		$bucketId = intval($bucketId);
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$index = [];
		foreach ($files['file'] as $i => $filePath)
		{
			$index[$path . $filePath] = $i;
		}

		$filter = [
			'=BUCKET_ID' => $bucketId,
		];
		if ($prevLastKey)
		{
			$filter['>FILE_PATH'] = $path . $prevLastKey;
		}
		if ($files['last_key'])
		{
			$filter['<=FILE_PATH'] = $path . $files['last_key'];
		}
		$fileList = static::getList([
			'select' => ['ID', 'FILE_PATH', 'FILE_SIZE', 'FILE_HASH'],
			'filter' => $filter,
		]);
		while ($fileInfo = $fileList->fetch())
		{
			if (
				array_key_exists($fileInfo['FILE_PATH'], $index)
				&& ($files['file_size'][$index[$fileInfo['FILE_PATH']]] == $fileInfo['FILE_SIZE'])
				&& ($files['file_hash'][$index[$fileInfo['FILE_PATH']]] == $fileInfo['FILE_HASH'])
			)
			{
				unset($files['file'][$index[$fileInfo['FILE_PATH']]]);
			}
			else
			{
				$deleteResult = static::delete($fileInfo['ID']);
			}
		}

		$values = [];
		foreach ($files['file'] as $i => $file)
		{
			$fileSize = $files['file_size'][$i];
			$fileMtime = \CCloudUtil::gmtTimeToDateTime($files['file_mtime'][$i]);
			$fileHash = $files['file_hash'][$i];
			$values [] = '('
				. $bucketId
				. ",'" . $helper->forSql($path . $file) . "'"
				. ',' . intval($fileSize)
				. ",'" . $fileMtime->format('Y-m-d h:i:s') . "'"
				. ",'" . $helper->forSql($fileHash) . "'"
				. ')'
			;
		}

		$insertSize = 1000;
		while ($values)
		{
			$sql = '
				INSERT INTO ' . static::getTableName() . '
				(BUCKET_ID, FILE_PATH, FILE_SIZE, FILE_MTIME, FILE_HASH)
				VALUES
				' . implode(",\n", array_splice($values, 0, $insertSize)) . '
			';
			$result = $connection->query($sql);
		}

		return $result;
	}

	/**
	 * Sync file hashes to the database. Removes all keys beyond the $prevLastKey.
	 *
	 * @param int $bucketId Clouds storage bucket identifier.
	 * @param string $path File list relative path.
	 * @param string $prevLastKey Last key returned by last call to CCloudStorageBucket::ListFiles.
	 *
	 * @return null|\Bitrix\Main\DB\Result
	 * @see \Bitrix\Clouds\FileHashTable::syncList
	 */
	public static function syncEnd($bucketId, $path, $prevLastKey)
	{
		$bucketId = intval($bucketId);
		$connection = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$delete = '
			DELETE from ' . static::getTableName() . '
			WHERE BUCKET_ID = ' . $bucketId . '
			AND FILE_PATH like \'' . $sqlHelper->forSql($path) . '%\'
			AND FILE_PATH > \'' . $sqlHelper->forSql($path . $prevLastKey) . '\'
		';
		$result = $connection->query($delete);
		return $result;
	}

	/**
	 * Add a file hash to the database.
	 *
	 * @param int $bucketId Clouds storage bucket identifier.
	 * @param string $path Path to the file.
	 * @param array $fileInfo File info as it returned by CCloudStorageBucket::GetFileInfo.
	 *
	 * @return null|\Bitrix\Main\DB\Result
	 * @see \CCloudStorageBucket::GetFileInfo
	 */
	public static function addFile($bucketId, $path, array $fileInfo)
	{
		return static::add([
			'BUCKET_ID' => $bucketId,
			'FILE_PATH' => $path,
			'FILE_SIZE' => $fileInfo['size'],
			'FILE_MTIME' => \CCloudUtil::gmtTimeToDateTime($fileInfo['mtime']),
			'FILE_HASH' => $fileInfo['hash'],
		]);
	}

	/**
	 * Returns last stored key for given bucket.
	 *
	 * @param int $bucketId Clouds storage bucket identifier.
	 *
	 * @return string
	 */
	public static function getLastKey($bucketId)
	{
		$bucketId = intval($bucketId);
		$connection = \Bitrix\Main\Application::getConnection();
		$sql = 'SELECT max(FILE_PATH) LAST_KEY from ' . static::getTableName() . ' WHERE BUCKET_ID=' . $bucketId;
		$last = $connection->query($sql)->fetch();
		return $last && $last['LAST_KEY'] ? $last['LAST_KEY'] : '';
	}

	/**
	 * Clears all stored file hashes for the bucket.
	 *
	 * @param int $bucketId Clouds storage bucket identifier.
	 *
	 * @return \Bitrix\Main\DB\Result
	 */
	public static function deleteAll($bucketId)
	{
		$bucketId = intval($bucketId);
		$connection = \Bitrix\Main\Application::getConnection();
		$delete = 'DELETE from ' . static::getTableName() . ' WHERE BUCKET_ID=' . $bucketId;
		$result = $connection->query($delete);
		return $result;
	}

	/**
	 * Clears a stored file hashe for the filePath for the bucket.
	 *
	 * @param int $bucketId Clouds storage bucket identifier.
	 * @param string $filePath File path.
	 *
	 * @return \Bitrix\Main\DB\Result
	 */
	public static function deleteByFilePath($bucketId, $filePath)
	{
		$bucketId = intval($bucketId);
		$connection = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$delete = '
			DELETE from ' . static::getTableName() . '
			WHERE BUCKET_ID = ' . $bucketId . "
			AND FILE_PATH = '" . $sqlHelper->forSql($filePath) . "'
		";
		$result = $connection->query($delete);
		return $result;
	}

	/**
	 * Returns file listing with "folders", sizes and modification times.
	 *
	 * @param int $bucketId Clouds storage bucket identifier.
	 * @param string $path Directory path.
	 * @param array $order How to sort.
	 * @param array $filter Additional filter.
	 *
	 * @return \Bitrix\Main\DB\Result
	 */
	public static function dirList($bucketId, $path, $order, $filter)
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$query = \Bitrix\Clouds\FileHashTable::query();
		$query->setSelect([
			new ExpressionField(
				'FILE_TYPE',
				'case when position(\'/\' in substring(%s, length(\'' . $sqlHelper->forSql($path) . '\')+1)) > 0 then \'D\' else \'F\' end',
				['FILE_PATH']
			),
			new ExpressionField(
				'NAME',
				'substring_index(substring(%s, length(\'' . $sqlHelper->forSql($path) . '\')+1), \'/\', 1)',
				['FILE_PATH']
			),
			new ExpressionField(
				'SUM_FILE_SIZE',
				'SUM(%s)',
				['FILE_SIZE']
			),
			new ExpressionField(
				'MAX_FILE_MTIME',
				'MAX(%s)',
				['FILE_MTIME']
			),
			new ExpressionField(
				'FILE_COUNT',
				'COUNT(1)'
			),
		]);

		$filter['=BUCKET_ID'] = $bucketId;
		$filter['%=FILE_PATH'] = $path . '%';
		$query->setFilter($filter);
		$query->setGroup(['FILE_TYPE', 'NAME']);
		$query->setOrder($order);

		$sql = $query->getQuery();

		return $connection->query($sql);
	}

	/**
	 * Returns duplicate files by bucket, hash, and size.
	 *
	 * @param int $bucketId Clouds storage bucket identifier.
	 * @param array $filter Additional filter to pass to FileHashTable.
	 * @param array $order Sort order.
	 * @param int $limit Records count.
	 * @return \Bitrix\Main\DB\Result
	 * @see \Bitrix\Main\File\Internal\FileHashTable
	 */
	public static function duplicateList($bucketId, $filter, $order, $limit = 0)
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		$query = \Bitrix\Main\File\Internal\FileHashTable::query();
		$query->registerRuntimeField('FILE_PATH', [
			'expression' => [
				$helper->getConcatFunction('%s', "'/'", '%s'), 'FILE.SUBDIR', 'FILE.FILE_NAME'
			]
		]);
		$query->setSelect([
			'FILE_HASH',
			'FILE_SIZE',
			new ExpressionField(
				'FILE_COUNT',
				'COUNT(distinct %s)',
				['FILE_PATH']
			),
			new ExpressionField(
				'FILE_ID_MIN',
				'MIN(%s)',
				['FILE_ID']
			),
			new ExpressionField(
				'FILE_ID_MAX',
				'MAX(%s)',
				['FILE_ID']
			),
		]);

		$filter['=FILE.HANDLER_ID'] = $bucketId;
		$filter['>FILE_COUNT'] = 1;
		$query->setFilter($filter);
		$query->setGroup(['FILE_HASH', 'FILE_SIZE']);
		$query->setOrder($order);
		if ($limit > 0)
		{
			$query->setLimit($limit);
		}

		$sql = $query->getQuery();

		return $connection->query($sql);
	}

	/**
	 * Returns duplicate files list by bucket, hash, and size.
	 *
	 * @param int $bucketId Clouds storage bucket identifier.
	 * @param array $fileHash File hash.
	 * @param array $fileSize File size.
	 * @return array
	 * @see \Bitrix\Main\File\Internal\FileHashTable
	 */
	public static function getFileDuplicates($bucketId, $fileHash, $fileSize)
	{
		$query = \Bitrix\Main\File\Internal\FileHashTable::getList([
			'select' => [
				'FILE_ID',
			],
			'filter' => [
				'=FILE.HANDLER_ID' => $bucketId,
				'=FILE_HASH' => $fileHash,
				'=FILE_SIZE' => $fileSize,
			],
			'order' => [
				'FILE_ID' => 'ASC',
			],
		]);

		$result = [];
		while ($fileDuplicate = $query->fetch())
		{
			$result[] = $fileDuplicate['FILE_ID'];
		}
		return $result;
	}

	/**
	 * Returns duplicates summary statistics.
	 *
	 * @param int $bucketId Clouds storage bucket identifier.
	 * @return array
	 */
	public static function getDuplicatesStat($bucketId)
	{
		$bucketId = intval($bucketId);
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		$sql = '
			select sum(DUP_COUNT) DUP_COUNT, sum(DUP_SIZE) DUP_SIZE
			from (
				select
					b_file_hash.FILE_SIZE
					,b_file_hash.FILE_HASH
					,count(distinct ' . $helper->getConcatFunction('b_file.SUBDIR', "'/'", 'b_file.FILE_NAME') . ')-1 DUP_COUNT
					,(count(distinct ' . $helper->getConcatFunction('b_file.SUBDIR', "'/'", 'b_file.FILE_NAME') . ")-1) * b_file_hash.FILE_SIZE DUP_SIZE
				from
					b_file_hash
					inner join b_file on
						b_file.ID = b_file_hash.FILE_ID
				where
					b_file.HANDLER_ID = '" . $bucketId . "'
				group by
					b_file_hash.FILE_SIZE, b_file_hash.FILE_HASH
				having
					count(distinct " . $helper->getConcatFunction('b_file.SUBDIR', "'/'", 'b_file.FILE_NAME') . ') > 1
			) t
		';

		return $connection->query($sql)->fetch();
	}

	/**
	 * Copies file hash information from clouds module table to the main module.
	 * Returns an array with copy statistics information.
	 *
	 * @param int $lastKey Where to start.
	 * @param int $pageSize Batch size.
	 * @return array
	 */
	public static function copyToFileHash($lastKey, $pageSize)
	{
		global $DB;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$lastKey = (int)$lastKey;
		$pageSize = (int)$pageSize;
		$sql = '
			SELECT
				b_file.ID as FILE_ID
				,b_clouds_file_hash.FILE_SIZE as FILE_SIZE
				,b_clouds_file_hash.FILE_HASH as FILE_HASH
			FROM
				b_file
				INNER JOIN b_clouds_file_hash ON
					b_clouds_file_hash.BUCKET_ID = ' . $DB->ToNumber('b_file.HANDLER_ID') . '
					AND b_clouds_file_hash.FILE_PATH = ' . $helper->getConcatFunction("'/'", 'b_file.SUBDIR', "'/'", 'b_file.FILE_NAME') . '
				LEFT JOIN b_file_duplicate ON
					b_file_duplicate.DUPLICATE_ID = b_file.ID
			WHERE
				b_file.ID > ' . $lastKey . '
				AND b_file_duplicate.DUPLICATE_ID is null
			ORDER BY b_file.ID
			LIMIT ' . $pageSize . '
		';

		$fileIds = $connection->query('
			SELECT
				min(FILE_ID) as FILE_ID_MIN
				,max(FILE_ID) as FILE_ID_MAX
				,count(FILE_ID) FILE_ID_CNT
			FROM (' . $sql . ') t
		')->fetch();

		if ($fileIds['FILE_ID_CNT'] > 0)
		{
			$sql = $helper->getInsertIgnore(
				'b_file_hash',
				'(FILE_ID, FILE_SIZE, FILE_HASH)',
				$sql
			);

			$connection->query($sql);
		}

		return $fileIds;
	}

	/**
	 * Checks candidates from file duplicates delete. Returns future file original identifier.
	 *
	 * @param int $bucketId Clouds storage bucket identifier.
	 * @param array &$fileIds Array of file identifiers candidates for duplicate delete.
	 * @return false|int
	 */
	public static function prepareDuplicates($bucketId, &$fileIds)
	{
		$originalId = false;
		if ($fileIds)
		{
			//Exclude any id that is already a duplicate
			$duplicates = \Bitrix\Main\File\Internal\FileDuplicateTable::getList([
				'select' => ['DUPLICATE_ID'],
				'filter' => [
					'=DUPLICATE_ID' => $fileIds,
				],
			]);
			while ($duplicate = $duplicates->fetch())
			{
				//Others will be excluded from the process
				$p = array_search($duplicate['DUPLICATE_ID'], $fileIds);
				if ($p !== false)
				{
					unset($fileIds[$p]);
				}
			}
		}

		if ($fileIds)
		{
			//Search throught file id for any existing originals
			$originals = \Bitrix\Main\File\Internal\FileDuplicateTable::getList([
				'select' => ['ORIGINAL_ID'],
				'filter' => [
					'=ORIGINAL_ID' => $fileIds,
				],
				'order' => ['ORIGINAL_ID' => 'ASC'],
			]);
			while ($original = $originals->fetch())
			{
				//First will be used for future deduplication
				if ($originalId === false)
				{
					$originalId = $original['ORIGINAL_ID'];
				}

				//Others will be excluded from the process
				$p = array_search($original['ORIGINAL_ID'], $fileIds);
				if ($p !== false)
				{
					unset($fileIds[$p]);
				}
			}
			//None originals exists yet
			if ($originalId === false)
			{
				$originalId = array_shift($fileIds);
			}
		}

		return $originalId;
	}
}
