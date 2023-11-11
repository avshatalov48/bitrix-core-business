<?php
namespace Bitrix\Iblock;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class IblockSiteTable
 *
 * Fields:
 * <ul>
 * <li> IBLOCK_ID int mandatory
 * <li> SITE_ID char(2) mandatory
 * <li> IBLOCK reference to {@link \Bitrix\Iblock\IblockTable}
 * <li> SITE reference to {@link \Bitrix\Main\SiteTable}
 * </ul>
 *
 * @package Bitrix\Iblock
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_IblockSite_Query query()
 * @method static EO_IblockSite_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_IblockSite_Result getById($id)
 * @method static EO_IblockSite_Result getList(array $parameters = [])
 * @method static EO_IblockSite_Entity getEntity()
 * @method static \Bitrix\Iblock\EO_IblockSite createObject($setDefaultValues = true)
 * @method static \Bitrix\Iblock\EO_IblockSite_Collection createCollection()
 * @method static \Bitrix\Iblock\EO_IblockSite wakeUpObject($row)
 * @method static \Bitrix\Iblock\EO_IblockSite_Collection wakeUpCollection($rows)
 */
class IblockSiteTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iblock_site';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'IBLOCK_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('IBLOCK_SITE_ENTITY_IBLOCK_ID_FIELD'),
			),
			'SITE_ID' => array(
				'data_type' => 'string',
				'primary' => true,
				'validation' => array(__CLASS__, 'validateSiteId'),
				'title' => Loc::getMessage('IBLOCK_SITE_ENTITY_SITE_ID_FIELD'),
			),
			'IBLOCK' => array(
				'data_type' => 'Bitrix\Iblock\Iblock',
				'reference' => array('=this.IBLOCK_ID' => 'ref.ID')
			),
			'SITE' => array(
				'data_type' => 'Bitrix\Main\Site',
				'reference' => array('=this.SITE_ID' => 'ref.LID'),
			),
		);
	}

	/**
	 * Returns validators for SITE_ID field.
	 *
	 * @return array
	 */
	public static function validateSiteId()
	{
		return array(
			new Entity\Validator\Length(null, 2),
		);
	}
}
