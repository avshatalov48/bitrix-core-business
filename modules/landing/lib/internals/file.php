<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class FileTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_File_Query query()
 * @method static EO_File_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_File_Result getById($id)
 * @method static EO_File_Result getList(array $parameters = array())
 * @method static EO_File_Entity getEntity()
 * @method static \Bitrix\Landing\Internals\EO_File createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Internals\EO_File_Collection createCollection()
 * @method static \Bitrix\Landing\Internals\EO_File wakeUpObject($row)
 * @method static \Bitrix\Landing\Internals\EO_File_Collection wakeUpCollection($rows)
 */
class FileTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_landing_file';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap(): array
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => 'ID'
			)),
			'ENTITY_ID' => new Entity\IntegerField('ENTITY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ENTITY_ID'),
				'required' => true
			)),
			'ENTITY_TYPE' => new Entity\StringField('ENTITY_TYPE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ENTITY_TYPE'),
				'required' => true
			)),
			'FILE_ID' => new Entity\IntegerField('FILE_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_FILE_ID'),
				'required' => true
			)),
			'TEMP' => new Entity\StringField('TEMP', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_FILE_TEMP'),
				'required' => true,
				'default_value' => 'N'
			)),
			'FILE' => new Entity\ReferenceField(
				'FILE',
				'\Bitrix\Main\FileTable',
				array('=this.FILE_ID' => 'ref.ID')
			)
		);
	}
}