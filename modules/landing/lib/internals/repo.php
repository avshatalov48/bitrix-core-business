<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class RepoTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Repo_Query query()
 * @method static EO_Repo_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Repo_Result getById($id)
 * @method static EO_Repo_Result getList(array $parameters = array())
 * @method static EO_Repo_Entity getEntity()
 * @method static \Bitrix\Landing\Internals\EO_Repo createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Internals\EO_Repo_Collection createCollection()
 * @method static \Bitrix\Landing\Internals\EO_Repo wakeUpObject($row)
 * @method static \Bitrix\Landing\Internals\EO_Repo_Collection wakeUpCollection($rows)
 */
class RepoTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_repo';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => 'ID'
			)),
			'XML_ID' => new Entity\StringField('XML_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_XML_ID'),
				'required' => true
			)),
			'APP_CODE' => new Entity\StringField('APP_CODE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_APP_CODE')
			)),
			'ACTIVE' => new Entity\StringField('ACTIVE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ACTIVE')
			)),
			'NAME' => new Entity\StringField('NAME', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_NAME'),
				'required' => true
			)),
			'DESCRIPTION' => new Entity\StringField('DESCRIPTION', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DESCRIPTION')
			)),
			'SECTIONS' => new Entity\StringField('SECTIONS', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SECTIONS')
			)),
			'SITE_TEMPLATE_ID' => new Entity\StringField('SITE_TEMPLATE_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SITE_TEMPLATE_ID')
			)),
			'PREVIEW' => new Entity\StringField('PREVIEW', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_PREVIEW')
			)),
			'MANIFEST' => new Entity\StringField('MANIFEST', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_MANIFEST')
			)),
			'CONTENT' => new Entity\StringField('CONTENT', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CONTENT'),
				'required' => true
			)),
			'CREATED_BY_ID' => new Entity\IntegerField('CREATED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CREATED_BY_ID'),
				'required' => true
			)),
			'MODIFIED_BY_ID' => new Entity\IntegerField('MODIFIED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_MODIFIED_BY_ID'),
				'required' => true
			)),
			'DATE_CREATE' => new Entity\DatetimeField('DATE_CREATE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_CREATE'),
				'required' => true
			)),
			'DATE_MODIFY' => new Entity\DatetimeField('DATE_MODIFY', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_MODIFY'),
				'required' => true
			))
		);
	}
}