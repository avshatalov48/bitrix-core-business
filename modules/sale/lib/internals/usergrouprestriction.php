<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class UserGroupRestrictionTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ENTITY_ID int mandatory
 * <li> ENTITY_TYPE_ID int mandatory
 * <li> GROUP_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Sale\Internals
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserGroupRestriction_Query query()
 * @method static EO_UserGroupRestriction_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_UserGroupRestriction_Result getById($id)
 * @method static EO_UserGroupRestriction_Result getList(array $parameters = array())
 * @method static EO_UserGroupRestriction_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_UserGroupRestriction createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_UserGroupRestriction_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_UserGroupRestriction wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_UserGroupRestriction_Collection wakeUpCollection($rows)
 */

class UserGroupRestrictionTable extends Main\ORM\Data\DataManager
{
	const ENTITY_TYPE_SHIPMENT = 1;
	const ENTITY_TYPE_PAYMENT = 2;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_usergroup_restr';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new Main\ORM\Fields\IntegerField('ID',[
				'primary' => true,
				'autocomplete' => true
			]),

			new Main\ORM\Fields\IntegerField('ENTITY_ID', [
				'required' => true
			]),

			new Main\ORM\Fields\IntegerField('ENTITY_TYPE_ID', [
				'required' => true
			]),

			new Main\ORM\Fields\IntegerField('GROUP_ID', [
				'required' => true
			])
		];
	}

	public static function deleteByEntity($entityType, $entityId)
	{
		$conn = Main\Application::getConnection();
		$helper = $conn->getSqlHelper();
		$conn->queryExecute('DELETE FROM '.$helper->quote(self::getTableName()).' WHERE ENTITY_TYPE_ID='.(string)(int)$entityType.' AND ENTITY_ID='.(string)(int)$entityId);
	}
}