<?php
namespace Bitrix\Sale\TradingPlatform\Vk;

use Bitrix\Main\Entity;
use Bitrix\Main\Type;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);


/**
 * Class LogTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Log_Query query()
 * @method static EO_Log_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Log_Result getById($id)
 * @method static EO_Log_Result getList(array $parameters = [])
 * @method static EO_Log_Entity getEntity()
 * @method static \Bitrix\Sale\TradingPlatform\Vk\EO_Log createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\TradingPlatform\Vk\EO_Log_Collection createCollection()
 * @method static \Bitrix\Sale\TradingPlatform\Vk\EO_Log wakeUpObject($row)
 * @method static \Bitrix\Sale\TradingPlatform\Vk\EO_Log_Collection wakeUpCollection($rows)
 */
class LogTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_tp_vk_log';
	}

	public static function getMap()
	{
		return array(
			new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('TP_VK_LOG_ID_FIELD'),
			)),

			new Entity\IntegerField('EXPORT_ID', array(
				'required' => true,
				'title' => Loc::getMessage('TP_VK_LOG_EXPORT_ID_FIELD'),
			)),

			new Entity\StringField('ERROR_CODE', array(
				'required' => true,
				'title' => Loc::getMessage('TP_VK_LOG_ERROR_CODE_FIELD'),
			)),

			new Entity\StringField('ITEM_ID', array(
				'title' => Loc::getMessage('TP_VK_LOG_ITEM_ID_FIELD'),
			)),

			new Entity\DatetimeField('TIME', array(
				'title' => Loc::getMessage('TP_VK_LOG_TIME_FIELD'),
				'default_value' => new Type\DateTime(),
			)),
			
			new Entity\TextField('ERROR_PARAMS', array(
				'title' => Loc::getMessage('TP_VK_LOG_ERROR_PARAMS'),
				'serialized' => true,
			)),
		);
	}
}