<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage seo
 * @copyright 2001-2013 Bitrix
 */
namespace Bitrix\Seo\Adv;

use Bitrix\Main\Localization\Loc;
use Bitrix\Seo\AdvEntity;

Loc::loadMessages(__FILE__);

/**
 * Class YandexGroupTable
 *
 * Local mirror for Yandex.Direct banner groups
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ENGINE_ID int mandatory
 * <li> XML_ID string(255) mandatory
 * <li> LAST_UPDATE datetime optional
 * <li> SETTINGS string optional
 * <li> CAMPAIGN_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Seo
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_YandexGroup_Query query()
 * @method static EO_YandexGroup_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_YandexGroup_Result getById($id)
 * @method static EO_YandexGroup_Result getList(array $parameters = array())
 * @method static EO_YandexGroup_Entity getEntity()
 * @method static \Bitrix\Seo\Adv\EO_YandexGroup createObject($setDefaultValues = true)
 * @method static \Bitrix\Seo\Adv\EO_YandexGroup_Collection createCollection()
 * @method static \Bitrix\Seo\Adv\EO_YandexGroup wakeUpObject($row)
 * @method static \Bitrix\Seo\Adv\EO_YandexGroup_Collection wakeUpCollection($rows)
 */

class YandexGroupTable extends AdvEntity
{
	const ENGINE = 'yandex_direct';

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_seo_adv_group';
	}

	public static function getMap()
	{
		return array_merge(
			parent::getMap(),
			array(
				'CAMPAIGN_ID' => array(
					'data_type' => 'integer',
					'title' => Loc::getMessage('ADV_CAMPAIGN_ENTITY_CAMPAIGN_ID_FIELD'),
				),
				'CAMPAIGN' => array(
					'data_type' => 'Bitrix\Seo\Adv\YandexCampaignTable',
					'reference' => array('=this.CAMPAIGN_ID' => 'ref.ID'),
				),
			)
		);
	}
}
