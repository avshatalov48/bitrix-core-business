<?php
namespace Bitrix\Seo\Adv;

use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Seo\Engine;

/**
 * Class OrderTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ENGINE_ID int mandatory
 * <li> TIMESTAMP_X datetime mandatory default 'CURRENT_TIMESTAMP'
 * <li> REQUEST_URI string(100) mandatory
 * <li> REQUEST_DATA string optional
 * <li> RESPONSE_TIME double mandatory
 * <li> RESPONSE_STATUS int optional
 * <li> RESPONSE_DATA string optional
 * </ul>
 *
 * @package Bitrix\Seo
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Order_Query query()
 * @method static EO_Order_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Order_Result getById($id)
 * @method static EO_Order_Result getList(array $parameters = array())
 * @method static EO_Order_Entity getEntity()
 * @method static \Bitrix\Seo\Adv\EO_Order createObject($setDefaultValues = true)
 * @method static \Bitrix\Seo\Adv\EO_Order_Collection createCollection()
 * @method static \Bitrix\Seo\Adv\EO_Order wakeUpObject($row)
 * @method static \Bitrix\Seo\Adv\EO_Order_Collection wakeUpCollection($rows)
 */

class OrderTable extends Entity\DataManager
{
	const PROCESSED = 'Y';
	const NOT_PROCESSED = 'N';

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_seo_adv_order';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'ENGINE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
				'required' => true,
			),
			'CAMPAIGN_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'BANNER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'ORDER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'SUM' => array(
				'data_type' => 'float',
			),
			'PROCESSED' => array(
				'data_type' => 'boolean',
				'values' => array(static::NOT_PROCESSED, static::PROCESSED),
			),
			'CAMPAIGN' => array(
				'data_type' => 'Bitrix\Seo\Adv\YandexCampaignTable',
				'reference' => array('=this.CAMPAIGN_ID' => 'ref.ID'),
			),
			'BANNER' => array(
				'data_type' => 'Bitrix\Seo\Adv\YandexBannerTable',
				'reference' => array('=this.BANNER_ID' => 'ref.ID'),
			),
			'ORDER' => array(
				'data_type' => 'Bitrix\Sale\OrderTable',
				'reference' => array('=this.ORDER_ID' => 'ref.ID'),
			)
		);
	}

	public static function onBeforeAdd(Entity\Event $event)
	{
		$result = new Entity\EventResult();
		$result->modifyFields(array("TIMESTAMP_X" => new DateTime()));
		return $result;
	}
}
