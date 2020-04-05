<?php
namespace Bitrix\Landing;

use \Bitrix\Landing\Internals\FileTable;

class File
{
	/**
	 * Entity type site.
	 */
	const ENTITY_TYPE_SITE  = 'S';

	/**
	 * Entity type landing.
	 */
	const ENTITY_TYPE_LANDING = 'L';

	/**
	 * Entity type block.
	 */
	const ENTITY_TYPE_BLOCK = 'B';

	/**
	 * Add new record.
	 * @param int $fileId File id.
	 * @param int $entityId Entity id.
	 * @param int $entityType Entity type.
	 * @return void
	 */
	protected static function add($fileId, $entityId, $entityType)
	{
		$res = FileTable::getList(array(
			'select' => array(
				'ID'
			),
			'filter' => array(
				'FILE_ID' => $fileId,
				'ENTITY_ID' => $entityId,
				'=ENTITY_TYPE' => $entityType
			)
		));
		if (!$res->fetch())
		{
			$res = FileTable::add(array(
				'FILE_ID' => $fileId,
				'ENTITY_ID' => $entityId,
				'ENTITY_TYPE' => $entityType
			));
			$res->isSuccess();
		}
	}

	/**
	 * Mark records for delete.
	 * @param int|array $fileId File id.
	 * @param int $entityId Entity id.
	 * @param int $entityType Entity type.
	 * @return void
	 */
	protected static function delete($fileId, $entityId, $entityType)
	{
		$filter = array(
			'ENTITY_ID' => $entityId,
			'=ENTITY_TYPE' => $entityType
		);
		if ($fileId)
		{
			$filter['FILE_ID'] = $fileId;
		}
		$res = FileTable::getList(array(
			'select' => array(
				'ID', 'FILE_ID'
			),
			'filter' => $filter
		));
		while ($row = $res->fetch())
		{
			$resUpdate = FileTable::update(
				$row['ID'],
				array(
					'FILE_ID' => -1 * abs($row['FILE_ID'])
				)
			);
			$resUpdate->isSuccess();
		}
	}

	/**
	 * Final delete all marked files.
	 * @param null $limit
	 * @return void
	 */
	public static function deleteFinal($limit = null)
	{
		$deletedFiles = array();

		$res = FileTable::getList(array(
		  	'select' => array(
		 		'ID', 'FILE_ID'
		  	),
	  		'filter' => array(
				'<FILE_ID' => 0
			),
			'limit' => $limit,
			'order' => array(
				'ID' => 'asc'
			)
		));
		while ($row = $res->fetch())
		{
			$row['FILE_ID'] *= -1;
			FileTable::delete($row['ID']);
			$deletedFiles[$row['FILE_ID']] = $row['FILE_ID'];
		}
		if (!empty($deletedFiles))
		{
			// don't delete still used
			$res = FileTable::getList(array(
				'select' => array(
					'FILE_ID'
				),
				'filter' => array(
					'FILE_ID' => $deletedFiles
				)
			));
			while ($row = $res->fetch())
			{
				unset($deletedFiles[$row['FILE_ID']]);
			}
			foreach ($deletedFiles as $fid)
			{
				$fileData = self::getFileArray($fid);
				if ($fileData)
				{
					//@tmp log
					\CEventLog::add(array(
						'SEVERITY' => 'NOTICE',
						'AUDIT_TYPE_ID' => 'LANDING_FILE_DELETE',
						'MODULE_ID' => 'landing',
						'ITEM_ID' => $fileData['SRC'],
						'DESCRIPTION' => print_r(array(
							'fileId' => $fid
						), true)
					));
					\CFile::delete($fid);
				}
			}
		}
	}

	/**
	 * Add new record for Site.
	 * @param int $id Site id.
	 * @param int $fileId File id.
	 * @return void
	 */
	public static function addToSite($id, $fileId)
	{
		if ($fileId > 0 && $id > 0)
		{
			self::add($fileId, $id, self::ENTITY_TYPE_SITE);
		}
	}

	/**
	 * Delete record from Site.
	 * @param int $id Site id.
	 * @param int|array $fileId File id (by default delete all files from landing).
	 * @return void
	 */
	public static function deleteFromSite($id, $fileId = array())
	{
		self::delete($fileId, $id, self::ENTITY_TYPE_SITE);
	}

	/**
	 * Add new record for Landing.
	 * @param int $lid Landing id.
	 * @param int $fileId File id.
	 * @return void
	 */
	public static function addToLanding($lid, $fileId)
	{
		if ($fileId > 0 && $lid > 0)
		{
			self::add($fileId, $lid, self::ENTITY_TYPE_LANDING);
		}
	}

	/**
	 * Delete record from Landing.
	 * @param int $lid Landing id.
	 * @param int|array $fileId File id (by default delete all files from landing).
	 * @return void
	 */
	public static function deleteFromLanding($lid, $fileId = array())
	{
		self::delete($fileId, $lid, self::ENTITY_TYPE_LANDING);
	}

	/**
	 * Add new record(s) for Block.
	 * @param int $blockId Block id.
	 * @param int|array $fileId File id (or file ids).
	 * @return void
	 */
	public static function addToBlock($blockId, $fileId)
	{
		if ($blockId > 0)
		{
			if (!is_array($fileId))
			{
				$fileId = array($fileId);
			}
			foreach ($fileId as $fid)
			{
				if ($fid > 0)
				{
					self::add($fid, $blockId, self::ENTITY_TYPE_BLOCK);
				}
			}
		}
	}

	/**
	 * Add new record(s) for Block (old records will be deleted).
	 * @param int $blockId Block id.
	 * @param int|array $fileId File id (or file ids).
	 * @return void
	 */
	public static function replaceInBlock($blockId, $fileId)
	{
		if ($blockId > 0)
		{
			if (!is_array($fileId))
			{
				$fileId = array($fileId);
			}
			$res = FileTable::getList(array(
				'select' => array(
					'FILE_ID'
				),
				'filter' => array(
					'ENTITY_ID' => $blockId,
					'=ENTITY_TYPE' => self::ENTITY_TYPE_BLOCK
				)
			));
			while ($row = $res->fetch())
			{
				if (!in_array($row['FILE_ID'], $fileId))
				{
					self::delete($row['FILE_ID'], $blockId, self::ENTITY_TYPE_BLOCK);
				}
			}
			self::addToBlock($blockId, $fileId);
		}
	}

	/**
	 * Delete record from Block.
	 * @param int $blockId Block id.
	 * @param int|array $fileId File id (by default delete all files from block).
	 * @return void
	 */
	public static function deleteFromBlock($blockId, $fileId = array())
	{
		self::delete($fileId, $blockId, self::ENTITY_TYPE_BLOCK);
	}

	/**
	 * Parse some content for search data-fileid for the block.
	 * @param int $blockId Block id for content.
	 * @param string $content Content.
	 * @return array
	 */
	public static function getFilesFromBlockContent($blockId, $content)
	{
		$fileIds = array();
		// parse from content
		if (preg_match_all('/data-fileid[2x]{0,2}="([\d]+)"/i', $content, $matches))
		{
			foreach ($matches[1] as $fid)
			{
				$fileIds[] = $fid;
			}
		}
		// check if files ids set in blockId
		if (!empty($fileIds))
		{
			$res = FileTable::getList(array(
				'select' => array(
					'FILE_ID'
				),
				'filter' => array(
					'FILE_ID' => $fileIds,
					'ENTITY_ID' => $blockId,
					'=ENTITY_TYPE' => self::ENTITY_TYPE_BLOCK
				)
			));
			$fileIds = array();
			while ($row = $res->fetch())
			{
				$fileIds[] = $row['FILE_ID'];
			}
		}
		return $fileIds;
	}

	/**
	 * Copy files from one entity to another.
	 * @param int $from Entity id.
	 * @param int $to Entity id.
	 * @param string $entityType Entity type.
	 * @return void
	 */
	protected static function copyEntityFiles($from, $to, $entityType)
	{
		$res = FileTable::getList(array(
			'select' => array(
				'FILE_ID'
			),
			'filter' => array(
				'ENTITY_ID' => $from,
				'=ENTITY_TYPE' => $entityType
			)
		));
		while ($row = $res->fetch())
		{
			FileTable::add(array(
				'FILE_ID' => $row['FILE_ID'],
				'ENTITY_ID' => $to,
				'ENTITY_TYPE' => $entityType
			));
		}
	}

	/**
	 * Copy files from one block to another.
	 * @param int $from Block id.
	 * @param int $to Block id.
	 * @return void
	 */
	public static function copyBlockFiles($from, $to)
	{
		self::copyEntityFiles($from, $to, self::ENTITY_TYPE_BLOCK);
	}

	/**
	 * Gets core file array.
	 * @param int $fileId File id.
	 * @return mixed
	 */
	public static function getFileArray($fileId)
	{
		$file = \CFile::getFileArray(
			$fileId
		);
		if (
			isset($file['MODULE_ID']) &&
			$file['MODULE_ID'] == 'landing'
		)
		{
			return $file;
		}
		return false;
	}

	/**
	 * Gets core file path.
	 * @param int $fileId File id.
	 * @return string
	 */
	public static function getFilePath($fileId)
	{
		$file = self::getFileArray($fileId);
		if (isset($file['SRC']))
		{
			return $file['SRC'];
		}
		return null;
	}
}