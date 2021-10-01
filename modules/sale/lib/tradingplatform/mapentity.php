<?php
namespace Bitrix\Sale\TradingPlatform;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class MapEntityTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TRADING_PLATFORM_ID int mandatory
 * <li> CODE string(255) mandatory
 * </ul>
 *
 * @package Bitrix\Sale\TradingPlatform
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MapEntity_Query query()
 * @method static EO_MapEntity_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_MapEntity_Result getById($id)
 * @method static EO_MapEntity_Result getList(array $parameters = array())
 * @method static EO_MapEntity_Entity getEntity()
 * @method static \Bitrix\Sale\TradingPlatform\EO_MapEntity createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\TradingPlatform\EO_MapEntity_Collection createCollection()
 * @method static \Bitrix\Sale\TradingPlatform\EO_MapEntity wakeUpObject($row)
 * @method static \Bitrix\Sale\TradingPlatform\EO_MapEntity_Collection wakeUpCollection($rows)
 */

class MapEntityTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_tp_map_entity';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('TRADING_PLATFORM_MAP_ENTITY_ENTITY_ID_FIELD'),
			),
			'TRADING_PLATFORM_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('TRADING_PLATFORM_MAP_ENTITY_ENTITY_TRADING_PLATFORM_ID_FIELD'),
			),
			'CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateCode'),
				'title' => Loc::getMessage('TRADING_PLATFORM_MAP_ENTITY_ENTITY_CODE_FIELD'),
			),
		);
	}

	public static function validateCode()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
}