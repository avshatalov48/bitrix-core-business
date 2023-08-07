<?php

namespace Bitrix\Seo\Sitemap\Internals;

use Bitrix\Main\Entity;

class RuntimeTable extends Entity\DataManager
{
	const ACTIVE = 'Y';
	const INACTIVE = 'N';
	
	const ITEM_TYPE_DIR = 'D';
	const ITEM_TYPE_FILE = 'F';
	const ITEM_TYPE_IBLOCK = 'I';
	const ITEM_TYPE_SECTION = 'S';
	const ITEM_TYPE_ELEMENT = 'E';
	const ITEM_TYPE_FORUM = 'G';
	const ITEM_TYPE_TOPIC = 'T';
	
	const PROCESSED = 'Y';
	const UNPROCESSED = 'N';
	
	public static function getFilePath(): string
	{
		return __FILE__;
	}
	
	public static function getTableName()
	{
		return 'b_seo_sitemap_runtime';
	}
	
	public static function getMap()
	{
		$fieldsMap = [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'PID' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'PROCESSED' => [
				'data_type' => 'boolean',
				'values' => [self::UNPROCESSED, self::PROCESSED],
			],
			'ITEM_PATH' => [
				'data_type' => 'string',
			],
			'ITEM_ID' => [
				'data_type' => 'integer',
			],
			'ITEM_TYPE' => [
				'data_type' => 'enum',
				'values' => [
					self::ITEM_TYPE_DIR,
					self::ITEM_TYPE_FILE,
					self::ITEM_TYPE_IBLOCK,
					self::ITEM_TYPE_SECTION,
					self::ITEM_TYPE_ELEMENT,
					self::ITEM_TYPE_FORUM,
					self::ITEM_TYPE_TOPIC,
				],
			],
			'ACTIVE' => [
				'data_type' => 'boolean',
				'values' => [self::INACTIVE, self::ACTIVE],
			],
			'ACTIVE_ELEMENT' => [
				'data_type' => 'boolean',
				'values' => [self::INACTIVE, self::ACTIVE],
			],
		];
		
		return $fieldsMap;
	}

	public static function clearByPid($PID)
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$query = $connection->query("
DELETE
FROM " . self::getTableName() . "
WHERE PID='" . intval($PID) . "'
");
	}
}
