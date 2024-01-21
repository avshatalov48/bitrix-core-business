<?php

namespace Bitrix\Translate\Index\Internals;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Translate;
use Bitrix\Translate\Index;


abstract class PhraseFts extends DataManager
{
	use Index\Internals\BulkOperation;

	/** @var array<string, ORM\Entity>  */
	protected static array $ftsEntities = [];

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
			],
			'FILE_ID' => [
				'data_type' => 'integer',
			],
			'PATH_ID' => [
				'data_type' => 'integer',
			],
			'CODE' => [
				'data_type' => 'string',
			],
			'PHRASE' => [
				'data_type' => 'string',
			],
			'FILE' => [
				'data_type' => Index\Internals\FileIndexTable::class,
				'reference' => [
					'=this.FILE_ID' => 'ref.ID',
				],
				'join_type' => 'INNER',
			],
			'PATH' => [
				'data_type' => Index\Internals\PathIndexTable::class,
				'reference' => [
					'=this.PATH_ID' => 'ref.ID',
				],
				'join_type' => 'INNER',
			],
		];
	}

	/**
	 * Checks and restore FTS tables.
	 * @return void
	 */
	public static function checkTables(): void
	{
		$tables = [];
		$tablesRes = Application::getConnection()->query("SHOW TABLES LIKE 'b_translate_phrase_fts_%'");
		while ($row = $tablesRes->fetch())
		{
			$tableName = array_shift($row);
			$langId = substr($tableName, -2);
			$tables[$langId] = $tableName;
		}
		foreach (Translate\Config::getEnabledLanguages() as $langId)
		{
			if (!preg_match("/[a-z0-9]{2}/i", $langId))
			{
				continue;
			}
			if (!isset($tables[$langId]))
			{
				self::createTable($langId);
			}
			else
			{
				unset($tables[$langId]);
			}
		}
		foreach ($tables as $langId => $table)
		{
			self::dropTable($langId);
		}
	}

	/**
	 * @param string $langId
	 * @return string
	 * @throws ArgumentException
	 */
	public static function getPartitionTableName(string $langId): string
	{
		if (!in_array($langId, Translate\Config::getLanguages(), true))
		{
			throw new ArgumentException('Parameter langId has wrong value');
		}

		return "b_translate_phrase_fts_{$langId}";
	}

	/**
	 * @param string $langId
	 * @return void
	 * @throws ArgumentException
	 * @throws SqlQueryException
	 */
	public static function createTable(string $langId): void
	{
		$partitionTable = self::getPartitionTableName($langId);

		$suffix = mb_strtoupper($langId);

		Application::getConnection()->queryExecute("
			CREATE TABLE IF NOT EXISTS `{$partitionTable}` (
				`ID` int not null,
				`FILE_ID` int not null,
				`PATH_ID` int not null,
				`CODE` varbinary(255) not null,
				`PHRASE` text,
				PRIMARY KEY (`ID`),
				UNIQUE KEY `IXU_TRNSL_FTS_PT_{$suffix}` (`PATH_ID`, `CODE`),
				UNIQUE KEY `IXU_TRNSL_FTS_FL_{$suffix}` (`FILE_ID`, `CODE`),
				FULLTEXT INDEX `IXF_TRNSL_FTS_PH_{$suffix}` (`PHRASE`)
			) DELAY_KEY_WRITE=1
		");
	}

	/**
	 * @param string $langId
	 * @return void
	 * @throws ArgumentException
	 * @throws SqlQueryException
	 */
	public static function dropTable(string $langId): void
	{
		$partitionTable = self::getPartitionTableName($langId);

		Application::getConnection()->queryExecute("DROP TABLE IF EXISTS `{$partitionTable}`");
	}

	/**
	 * @param string $langId
	 * @return DataManager|PhraseFts|string
	 * @throws ArgumentException
	 */
	public static function getFtsEntityClass(string $langId): string
	{
		static $cache = [];
		if (!isset($cache[$langId]))
		{
			$entity = self::getFtsEntity($langId);
			$cache[$langId] = $entity->getDataClass();
		}

		return $cache[$langId];
	}

	/**
	 * @param string $langId
	 * @return ORM\Entity
	 * @throws ArgumentException
	 */
	public static function getFtsEntity(string $langId): ORM\Entity
	{
		if (!in_array($langId, Translate\Config::getEnabledLanguages(), true))
		{
			throw new ArgumentException('Parameter langId has wrong value');
		}
		if (!isset(self::$ftsEntities[$langId]))
		{
			self::$ftsEntities[$langId] = ORM\Entity::compileEntity(
				'PhraseIndexTfsEntity'. mb_strtoupper($langId),
				[],
				[
					'table_name' => self::getPartitionTableName($langId),
					'namespace' => __NAMESPACE__,
					'parent' => Index\Internals\PhraseFts::class
				]
			);
		}

		return self::$ftsEntities[$langId];
	}

	/**
	 * Drop index.
	 *
	 * @param Translate\Filter|null $filter Params to filter file list.
	 *
	 * @return void
	 */
	public static function purge(?Translate\Filter $filter = null): void
	{
		$filterOut = static::processFilter($filter);
		static::bulkDelete($filterOut);
	}

	/**
	 * Processes filter params to convert them into orm type.
	 *
	 * @param Translate\Filter|null $filter Params to filter file list.
	 *
	 * @return array
	 */
	public static function processFilter(?Translate\Filter $filter = null): array
	{
		$filterOut = [];

		if ($filter !== null)
		{
			foreach ($filter as $key => $value)
			{
				if (empty($value) && $value !== '0')
				{
					continue;
				}

				if ($key === 'path')
				{
					$filterOut['=%PATH.PATH'] = $value.'%';
				}
				elseif ($key === 'fileId')
				{
					$filterOut['=FILE_ID'] = $value;
				}
				elseif ($key === 'pathId')
				{
					$filterOut['=PATH_ID'] = $value;
				}
				else
				{
					if (static::getEntity()->hasField(trim($key, '<>!=@~%*')))
					{
						$filterOut[$key] = $value;
					}
				}
			}
		}

		return $filterOut;
	}
}
