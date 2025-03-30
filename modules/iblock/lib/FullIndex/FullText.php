<?php

namespace Bitrix\Iblock\FullIndex;

use Bitrix\Main\DB\Connection;
use CIBlock;

final class FullText
{
	private const INDEX_NAME = 'IBLOCK_ELEMENT_FT_INDEX';

	private static array $existTables = [];

	public static function isTableExist(int $iblockId): bool
	{
		if (empty(self::$existTables[$iblockId]))
		{
			$connection = \Bitrix\Main\Application::getConnection();

			FullText::$existTables[$iblockId] = $connection->isTableExists(FullText::getTableName($iblockId));
		}

		return FullText::$existTables[$iblockId];
	}

	public static function isExist(int $iblockId, int $elementId): bool
	{
		$connection = \Bitrix\Main\Application::getConnection();

		$searchIndexTableName = FullText::getTableName($iblockId);

		if (!FullText::isTableExist($iblockId))
		{
			return false;
		}

		$sqlHelper = $connection->getSqlHelper();

		return !empty(
			$connection->query(
				"SELECT ELEMENT_ID FROM "
				. $sqlHelper->quote($searchIndexTableName)
				. " WHERE ELEMENT_ID = "
				. $elementId
			)->Fetch()
		);
	}

	public static function createTable(int $iblockId): void
	{
		$connection = \Bitrix\Main\Application::getConnection();

		$searchIndexTableName = FullText::getTableName($iblockId);

		if (FullText::isTableExist($iblockId))
		{
			return;
		}

		$fields = [
			'ELEMENT_ID' => (new \Bitrix\Main\ORM\Fields\IntegerField('ELEMENT_ID')),
			'SEARCH_CONTENT' => (new \Bitrix\Main\ORM\Fields\TextField('SEARCH_CONTENT'))->configureNullable(),
		];

		$connection->createTable($searchIndexTableName, $fields, ['ELEMENT_ID']);
		$connection->createIndex($searchIndexTableName, FullText::INDEX_NAME, ['SEARCH_CONTENT'], null, Connection::INDEX_FULLTEXT);

		FullText::$existTables[$iblockId] = true;
	}

	public static function drop(int $iblockId): void
	{
		$connection = \Bitrix\Main\Application::getConnection();

		if (!FullText::isTableExist($iblockId))
		{
			return;
		}

		$connection->dropTable(FullText::getTableName($iblockId));

		FullText::$existTables[$iblockId] = false;
	}

	public static function add(int $iblockId, array $searchIndexParams): void
	{
		$connection = \Bitrix\Main\Application::getConnection();

		$searchIndexTableName = FullText::getTableName($iblockId);

		if (!FullText::isTableExist($iblockId))
		{
			FullText::createTable($iblockId);
		}

		$connection->add($searchIndexTableName, $searchIndexParams, null);
	}

	public static function delete(int $iblockId, int $elementId): void
	{
		$connection = \Bitrix\Main\Application::getConnection();

		$searchIndexTableName = FullText::getTableName($iblockId);

		if (!FullText::isTableExist($iblockId))
		{
			return;
		}

		$sqlHelper = $connection->getSqlHelper();

		$connection->queryExecute("DELETE FROM " . $sqlHelper->quote($searchIndexTableName) . " WHERE ELEMENT_ID = " . $elementId);
	}

	public static function update(int $iblockId, int $elementId, array $searchIndexParams): void
	{
		$connection = \Bitrix\Main\Application::getConnection();

		$searchIndexTableName = FullText::getTableName($iblockId);

		if (!FullText::isTableExist($iblockId))
		{
			FullText::createTable($iblockId);
		}

		$helper = $connection->getSqlHelper();

		$queries = $helper->prepareMerge(
			$searchIndexTableName,
			['ELEMENT_ID'],
			array_merge(['ELEMENT_ID' => $elementId], $searchIndexParams),
			$searchIndexParams,
		);

		foreach($queries as $query)
		{
			$connection->queryExecute($query);
		}
	}

	public static function getTableName(int $iblockId): string
	{
		return 'b_iblock_' . $iblockId . '_element_search_index';
	}

	public static function doesIblockSupportById(int $iblockId): bool
	{
		return CIBlock::GetArrayByID($iblockId, "FULLTEXT_INDEX") === 'Y';
	}

	public static function doesIblockSupportByData(array $data): bool
	{
		return isset($data['FULLTEXT_INDEX']) && ($data['FULLTEXT_INDEX'] === 'Y');
	}

	public static function canUseFulltextSearch(int $iblockId): bool
	{
		return FullText::doesIblockSupportById($iblockId) && FullText::isTableExist($iblockId);
	}
}