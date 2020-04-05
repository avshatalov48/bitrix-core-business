<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

class DemosTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_demo';
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
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ACTIVE'),
				'default_value' => 'Y'
			)),
			'TYPE' => new Entity\StringField('TYPE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DEMOS_TYPE')
			)),
			'TPL_TYPE' => new Entity\StringField('TPL_TYPE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DEMOS_TPL_TYPE')
			)),
			'SHOW_IN_LIST' => new Entity\StringField('SHOW_IN_LIST', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SHOW_IN_LIST'),
				'default_value' => 'N'
			)),
			'TITLE' => new Entity\StringField('TITLE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_TITLE'),
				'required' => true
			)),
			'DESCRIPTION' => new Entity\StringField('DESCRIPTION', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DESCRIPTION')
			)),
			'PREVIEW_URL' => new Entity\StringField('PREVIEW_URL', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_PREVIEW_URL')
			)),
			'PREVIEW' => new Entity\StringField('PREVIEW', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_PREVIEW')
			)),
			'PREVIEW2X' => new Entity\StringField('PREVIEW2X', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_PREVIEWX2')
			)),
			'PREVIEW3X' => new Entity\StringField('PREVIEW3X', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_PREVIEWX3')
			)),
			'MANIFEST' => new Entity\StringField('MANIFEST', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_MANIFEST')
			)),
			'LANG' => new Entity\StringField('LANG', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_MANIFEST')
			)),
			'SITE_TEMPLATE_ID' => new Entity\StringField('SITE_TEMPLATE_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SITE_TEMPLATE_ID')
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