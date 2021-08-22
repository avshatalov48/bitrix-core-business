<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class ChatBindingTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ChatBinding_Query query()
 * @method static EO_ChatBinding_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_ChatBinding_Result getById($id)
 * @method static EO_ChatBinding_Result getList(array $parameters = array())
 * @method static EO_ChatBinding_Entity getEntity()
 * @method static \Bitrix\Landing\Internals\EO_ChatBinding createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Internals\EO_ChatBinding_Collection createCollection()
 * @method static \Bitrix\Landing\Internals\EO_ChatBinding wakeUpObject($row)
 * @method static \Bitrix\Landing\Internals\EO_ChatBinding_Collection wakeUpCollection($rows)
 */
class ChatBindingTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_chat_binding';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'title' => 'ID',
				'primary' => true
			)),
			'INTERNAL_CHAT_ID' => new Entity\IntegerField('INTERNAL_CHAT_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_INTERNAL_CHAT_ID'),
				'required' => true
			)),
			'ENTITY_ID' => new Entity\IntegerField('ENTITY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ENTITY_ID'),
				'required' => true
			)),
			'ENTITY_TYPE' => new Entity\StringField('ENTITY_TYPE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_ENTITY_TYPE'),
				'required' => true
			))
		);
	}
}