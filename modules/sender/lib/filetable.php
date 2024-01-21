<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender;

use Bitrix\Main\DB;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type as MainType;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Fileman\Block\Editor as BlockEditor;
use Bitrix\Fileman\Block\EditorMail as BlockEditorMail;
use Bitrix\Sender\Internals\SqlBatch;

Loc::loadMessages(__FILE__);

class FileTable extends ORM\Data\DataManager
{
	private static $fileList = [];
	public const TYPES = [
		'LETTER' => 0,
		'TEMPLATE' => 1,
	];
	/**
	 * Get table name
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_file';
	}

	/**
	 * Return the map
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'autocomplete' => true,
				'primary' => true,
			),
			'FILE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'ENTITY_TYPE' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'ENTITY_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => new MainType\DateTime(),
			),
		);
	}

	public static function syncFiles(int $entityId, int $entityType, string $template, bool $deleteFiles = true, bool $onDeleteEntity = false)
	{
		preg_match_all(
			'@src="([^"]+)"@',
			$template,
			$matches
		);
		$urls = array_pop($matches);
		// get file list from html
		$fileNameList = [];
		$fileNameSearchList = [];
		$files = [];
		foreach ($urls as $path)
		{
			preg_match("/[^\/|\\\]+$/", $path, $url);

			if (!$url)
			{
				continue;
			}

			if (!empty($url[0]))
			{
				$fileNameList[] = $url[0];
				if (array_key_exists($url[0], static::$fileList))
				{
					$files[] = static::$fileList[$url[0]];
					continue;
				}
				$fileNameSearchList[] = $url[0];
			}

		}

		if (!empty($fileNameSearchList))
		{
			// get files from main FileTable which exists in html
			$selectedFiles = \Bitrix\Main\FileTable::getList([
				'select' => ['ID', 'FILE_NAME'],
				'filter' => [
					'=MODULE_ID' => 'sender',
					'@FILE_NAME' => $fileNameSearchList,
				],
				'order' => [
					'ID' => 'ASC'
				]
			])->fetchAll();
			$files = array_merge($files, $selectedFiles);

			if (empty($selectedFiles))
			{
				foreach ($fileNameList as $fileName)
				{
					static::$fileList[$fileName] = null;
				}
			}
		}

		$batchData = [];
		// get files in current sender file table
		$currentFiles = array_column(self::getCurrentFiles($entityId, $entityType), 'FILE_ID');

		$preparedFiles = [];
		foreach ($currentFiles as $fileId)
		{
			$preparedFiles[$fileId] = $fileId;
		}

		$filesToDelete = [];
		foreach ($files as $file)
		{
			if (is_null($file))
			{
				continue;
			}
			if (!isset(static::$fileList[$file['FILE_NAME']]))
			{
				static::$fileList[$file['FILE_NAME']] = $file;
			}

			if ($onDeleteEntity && in_array($file['ID'], $preparedFiles))
			{
				$filesToDelete[] = $file['ID'];
				unset($preparedFiles[$file['ID']]);
				continue;
			}
			// do nothing if file in current template
			if (in_array($file['ID'], $preparedFiles))
			{
				unset($preparedFiles[$file['ID']]);
				continue;
			}

			if (isset($batchData[$file['FILE_NAME']]))
			{
				$filesToDelete[] = $file['ID'];
				continue;
			}

			foreach ($fileNameList as $fileName)
			{
				if ($fileName === $file['FILE_NAME'])
				{
					$batchData[$fileName] = [
						'ENTITY_TYPE' => $entityType,
						'ENTITY_ID' => $entityId,
						'FILE_ID' => $file['ID'],
						'DATE_INSERT' => new MainType\DateTime()
					];
				}
			}
		}

		foreach ($preparedFiles as $file)
		{
			self::deleteIfCan($file, $entityId, $entityType, $deleteFiles);
		}

		foreach ($filesToDelete as $file)
		{
			self::deleteIfCan($file, $entityId, $entityType, $deleteFiles);
		}
		SqlBatch::insert(self::getTableName(), $batchData);
	}

	private static function getCurrentFiles(int $entityId, int $entityType)
	{
		return self::getList([
			'select' => ['FILE_ID'],
			'filter' => [
				'=ENTITY_ID' => $entityId,
				'=ENTITY_TYPE' => $entityType,
			],
		])->fetchAll();
	}

	private static function deleteIfCan(int $fileId, int $entityId, int $entityType, bool $deleteFiles)
	{
		self::deleteList([
			'=FILE_ID' => $fileId,
			'=ENTITY_TYPE' => $entityType,
			'=ENTITY_ID' => $entityId,
		]);

		$hasFiles = self::getList([
				'select' => ['ID',],
				'filter' => [
					'=FILE_ID' => $fileId
				],
				'limit' => 1
			]
		)->fetch();

		if ($deleteFiles)
		{
			$deleteFiles = 1 === \COption::GetOptionInt(
				'sender',
				'sender_file_load_completed',
				0
				);
		}

		if (!$hasFiles && $deleteFiles)
		{
			\CFile::Delete($fileId);
		}
	}


	/**
	 * @param array $filter
	 * @return \Bitrix\Main\DB\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function deleteList(array $filter)
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();

		\CTimeZone::disable();
		$sql = sprintf(
			'DELETE FROM %s WHERE %s',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			Query::buildFilterSql($entity, $filter)
		);
		$res = $connection->query($sql);
		\CTimeZone::enable();

		return $res;
	}
}
