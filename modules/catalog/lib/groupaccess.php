<?php
namespace Bitrix\Catalog;

use Bitrix\Main\ORM,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class GroupAccessTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CATALOG_GROUP_ID int mandatory
 * <li> GROUP_ID int mandatory
 * <li> ACCESS bool mandatory
 * <li> CATALOG_GROUP reference to {@link \Bitrix\Catalog\CatalogGroupTable}
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_GroupAccess_Query query()
 * @method static EO_GroupAccess_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_GroupAccess_Result getById($id)
 * @method static EO_GroupAccess_Result getList(array $parameters = [])
 * @method static EO_GroupAccess_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_GroupAccess createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_GroupAccess_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_GroupAccess wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_GroupAccess_Collection wakeUpCollection($rows)
 */

class GroupAccessTable extends ORM\Data\DataManager
{
	const ACCESS_BUY = 'Y';
	const ACCESS_VIEW = 'N';
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_catalog_group2group';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new ORM\Fields\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('GROUP_ACCESS_ENTITY_ID_FIELD')
			)),
			'CATALOG_GROUP_ID' => new ORM\Fields\IntegerField('CATALOG_GROUP_ID', array(
				'required' => true,
				'title' => Loc::getMessage('GROUP_ACCESS_ENTITY_CATALOG_GROUP_ID_FIELD')
			)),
			'GROUP_ID' => new ORM\Fields\IntegerField('GROUP_ID', array(
				'required' => true,
				'title' => Loc::getMessage('GROUP_ACCESS_ENTITY_GROUP_ID_FIELD')
			)),
			'ACCESS' => new ORM\Fields\BooleanField('ACCESS', array(
				'column_name' => 'BUY',
				'values' => array(self::ACCESS_VIEW, self::ACCESS_BUY),
				'title' => Loc::getMessage('GROUP_ACCESS_ENTITY_ACCESS_FIELD')
			)),
			'CATALOG_GROUP' => new ORM\Fields\Relations\Reference(
				'CATALOG_GROUP',
				'\Bitrix\Catalog\Group',
				array('=this.CATALOG_GROUP_ID' => 'ref.ID')
			)
		);
	}
}