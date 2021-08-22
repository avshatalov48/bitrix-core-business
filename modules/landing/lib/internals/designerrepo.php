<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class DesignerRepoTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_DesignerRepo_Query query()
 * @method static EO_DesignerRepo_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_DesignerRepo_Result getById($id)
 * @method static EO_DesignerRepo_Result getList(array $parameters = array())
 * @method static EO_DesignerRepo_Entity getEntity()
 * @method static \Bitrix\Landing\Internals\EO_DesignerRepo createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Internals\EO_DesignerRepo_Collection createCollection()
 * @method static \Bitrix\Landing\Internals\EO_DesignerRepo wakeUpObject($row)
 * @method static \Bitrix\Landing\Internals\EO_DesignerRepo_Collection wakeUpCollection($rows)
 */
class DesignerRepoTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_designer_repo';
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
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DB_XML_ID'),
				'required' => true
			)),
			'TITLE' => new Entity\StringField('TITLE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DB_TITLE')
			)),
			'SORT' => new Entity\IntegerField('SORT', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DB_SORT')
			)),
			'HTML' => new Entity\StringField('HTML', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DB_HTML'),
				'required' => true
			)),
			'MANIFEST' => (new \Bitrix\Main\ORM\Fields\ArrayField('MANIFEST', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DB_NODE_MANIFEST')
			)))->configureSerializationPhp(),
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