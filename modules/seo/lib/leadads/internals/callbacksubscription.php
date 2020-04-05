<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage crm
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Seo\LeadAds\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * Class CallbackSubscriptionTable.
 * @package Bitrix\Seo\LeadAds\Internals
 */
class CallbackSubscriptionTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_seo_service_subscription';
	}

	/**
	 * Get map.
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
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'default_value' => new DateTime(),
			),
			'TYPE' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'GROUP_ID' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'CALLBACK_SERVER_ID' => array(
				'data_type' => 'string',
				'required' => false,
			),
			'HAS_AUTH' => array(
				'data_type' => 'boolean',
				'default_value' => 'N',
				'values' => array('N', 'Y'),
				'required' => true,
			),
		);
	}
}
