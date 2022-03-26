<?php

namespace Bitrix\Main\UrlPreview;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\ScalarField;

/**
 * Class UrlMetadataTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UrlMetadata_Query query()
 * @method static EO_UrlMetadata_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UrlMetadata_Result getById($id)
 * @method static EO_UrlMetadata_Result getList(array $parameters = [])
 * @method static EO_UrlMetadata_Entity getEntity()
 * @method static \Bitrix\Main\UrlPreview\EO_UrlMetadata createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\UrlPreview\EO_UrlMetadata_Collection createCollection()
 * @method static \Bitrix\Main\UrlPreview\EO_UrlMetadata wakeUpObject($row)
 * @method static \Bitrix\Main\UrlPreview\EO_UrlMetadata_Collection wakeUpCollection($rows)
 */
class UrlMetadataTable extends Entity\DataManager
{
	const TYPE_STATIC = 'S';
	const TYPE_DYNAMIC = 'D';
	const TYPE_TEMPORARY = 'T';
	const TYPE_FILE = 'F';

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_urlpreview_metadata';
	}

	/**
	 * Returns entity map definition
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
			)),
			'URL' => new Entity\StringField('URL', array(
				'required' => true,
			)),
			'TYPE' => new Entity\StringField('TYPE', array(
				'required' => true,
			)),
			'TITLE' => new Entity\StringField('TITLE'),
			'DESCRIPTION' => new Entity\TextField('DESCRIPTION'),
			'IMAGE_ID' => new Entity\IntegerField('IMAGE_ID'),
			'IMAGE' => new Entity\StringField('IMAGE'),
			'EMBED' => new Entity\TextField('EMBED'),
			'EXTRA' => new Entity\TextField('EXTRA', array(
				'serialized' => true,
			)),
			'DATE_INSERT' => new Entity\DatetimeField('DATE_INSERT', array(
				'default_value' => new Main\Type\DateTime(),
			)),
			'DATE_EXPIRE' => new Entity\DatetimeField('DATE_EXPIRE')
		);
	}

	/**
	 * Returns last record filtered by $url value
	 *
	 * @param string $url Url of the page with metadata.
	 * @return array|false
	 * @throws Main\ArgumentException
	 */
	public static function getByUrl($url)
	{
		$parameters = [
			'select' => ['*'],
			'filter' => [
				'=URL' => $url,
			],
			'order' => [
				'ID' => 'desc'
			],
			'limit' => 1
		];

		return static::getList($parameters)->fetch();
	}
}