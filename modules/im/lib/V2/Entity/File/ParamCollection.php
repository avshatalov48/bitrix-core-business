<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Entity\File;

use Bitrix\Im\Model\FileParamTable;
use Bitrix\Im\V2\Entity\File\Param\BaseParam;
use Bitrix\Im\V2\Entity\File\Param\Param;
use Bitrix\Im\V2\Entity\File\Param\ParamName;
use Bitrix\Im\V2\Registry;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int,Param>
 */
final class ParamCollection extends Registry
{
	private int $fileId;

	private static array $cache = [];

	private function __construct(int $fileId)
	{
		parent::__construct();
		$this->fileId = $fileId;
	}

	public static function getInstance(int $fileId): self
	{
		if (!isset(self::$cache[$fileId]))
		{
			self::load($fileId);
		}

		return self::$cache[$fileId];
	}

	private static function load(int $fileId): void
	{
		$collection = new self($fileId);

		$result = FileParamTable::query()
			->setSelect(['ID', 'DISK_FILE_ID', 'PARAM_NAME', 'PARAM_VALUE'])
			->where('DISK_FILE_ID', $fileId)
			->fetchAll()
		;

		self::$cache[$fileId] = $collection->initByArray($result);
	}

	public static function loadByFileIds(array $fileIds): void
	{
		foreach ($fileIds as $key => $fileId)
		{
			if (isset(self::$cache[$fileId]))
			{
				unset($fileIds[$key]);
			}
		}

		if (empty($fileIds))
		{
			return;
		}

		$result = FileParamTable::query()
			->setSelect(['ID', 'DISK_FILE_ID', 'PARAM_NAME', 'PARAM_VALUE'])
			->whereIn('DISK_FILE_ID', $fileIds)
			->exec()
		;

		$params = [];
		while ($row = $result->fetch())
		{
			$params[$row['DISK_FILE_ID']][] = $row;
		}

		foreach ($fileIds as $fileId)
		{
			$collection = new self((int)$fileId);
			self::$cache[$fileId] = $collection->initByArray($params[$fileId] ?? []);
		}
	}

	private function initByArray(array $items): self
	{
		foreach ($items as $item)
		{
			$paramName = $item['PARAM_NAME'] ?? '';

			if (
				$this->offsetExists($paramName)
				|| ParamName::tryFrom($paramName) === null
			)
			{
				continue;
			}

			$this[$paramName] = BaseParam::getInstance(
				(int)$item['DISK_FILE_ID'] ,
				ParamName::tryFrom($paramName),
				$item['PARAM_VALUE']
			);
		}

		return $this;
	}

	public static function addParams(array $params): void
	{
		$validParams = [];
		foreach ($params as $param)
		{
			if (
				ParamName::tryFrom((String)$param['PARAM_NAME']) === null
				|| empty($param['PARAM_VALUE'])
				|| empty($param['DISK_FILE_ID'])
			)
			{
				continue;
			}

			$validParams[] = [
				'DISK_FILE_ID' => $param['DISK_FILE_ID'],
				'PARAM_NAME' => $param['PARAM_NAME'],
				'PARAM_VALUE' => $param['PARAM_VALUE'],
			];
		}

		foreach ($validParams as $validParam)
		{
			if (isset(self::$cache[$item['DISK_FILE_ID']]))
			{
				self::$cache[$validParam['DISK_FILE_ID']]->initByArray([$validParam]);
			}
		}

		self::insertParams($validParams);
	}

	private static function insertParams(array $fields): void
	{
		if (empty($fields))
		{
			return;
		}

		FileParamTable::multiplyInsertWithoutDuplicate(
			$fields,
			['UNIQUE_FIELDS' => ['DISK_FILE_ID', 'PARAM_NAME']]
		);
	}

	public function getParam(ParamName $param): ?Param
	{
		return $this[$param->value] ?? null;
	}

	public function getFileId(): int
	{
		return $this->fileId;
	}

	public static function copyParams(array $fileMap): void
	{
		$oldFiles = array_keys($fileMap);
		self::loadByFileIds($oldFiles);
		$insertParams = [];

		foreach ($fileMap as $oldFileId => $newFileId)
		{
			$oldCollection = self::getInstance($oldFileId);
			foreach ($oldCollection as $item)
			{
				$newParam = $item->toArray();
				$newParam['DISK_FILE_ID'] = $newFileId;
				$insertParams[] = $newParam;
			}
		}

		self::insertParams($insertParams);
	}
}
