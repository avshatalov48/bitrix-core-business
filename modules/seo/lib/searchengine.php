<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage seo
 * @copyright 2001-2013 Bitrix
 */
namespace Bitrix\Seo;

use Bitrix\Main\Entity;

/**
 * Class SearchEngineTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_SearchEngine_Query query()
 * @method static EO_SearchEngine_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_SearchEngine_Result getById($id)
 * @method static EO_SearchEngine_Result getList(array $parameters = array())
 * @method static EO_SearchEngine_Entity getEntity()
 * @method static \Bitrix\Seo\EO_SearchEngine createObject($setDefaultValues = true)
 * @method static \Bitrix\Seo\EO_SearchEngine_Collection createCollection()
 * @method static \Bitrix\Seo\EO_SearchEngine wakeUpObject($row)
 * @method static \Bitrix\Seo\EO_SearchEngine_Collection wakeUpCollection($rows)
 */
class SearchEngineTable extends Entity\DataManager
{
	const INACTIVE = 'N';
	const ACTIVE = 'Y';

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_seo_search_engine';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'CODE' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array(self::INACTIVE, self::ACTIVE)
			),
			'SORT' => array(
				'data_type' => 'integer',
			),
			'NAME' => array(
				'data_type' => 'string',
			),
			'CLIENT_ID' => array(
				'data_type' => 'string',
			),
			'CLIENT_SECRET' => array(
				'data_type' => 'string',
			),
			'REDIRECT_URI' => array(
				'data_type' => 'string',
			),
			'SETTINGS' => array(
				'data_type' => 'text',
			),
		);

		return $fieldsMap;
	}

	public static function getByCode($code)
	{
		return SearchEngineTable::getList([
			'filter' => ['=CODE' => $code],
		]);
	}
}
