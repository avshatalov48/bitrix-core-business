<?php
namespace Bitrix\Socialservices;

use Bitrix\Main;

/**
 * Class ApTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime optional default 'CURRENT_TIMESTAMP'
 * <li> USER_ID int mandatory
 * <li> DOMAIN string(255) optional
 * <li> ENDPOINT string(255) optional
 * <li> LOGIN string(50) optional
 * <li> PASSWORD string(50) optional
 * <li> LAST_AUTHORIZE datetime optional
 * </ul>
 *
 * @package Bitrix\Socialservices
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Ap_Query query()
 * @method static EO_Ap_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Ap_Result getById($id)
 * @method static EO_Ap_Result getList(array $parameters = array())
 * @method static EO_Ap_Entity getEntity()
 * @method static \Bitrix\Socialservices\EO_Ap createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialservices\EO_Ap_Collection createCollection()
 * @method static \Bitrix\Socialservices\EO_Ap wakeUpObject($row)
 * @method static \Bitrix\Socialservices\EO_Ap_Collection wakeUpCollection($rows)
 */
class ApTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_socialservices_ap';
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
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'DOMAIN' => array(
				'data_type' => 'string',
			),
			'ENDPOINT' => array(
				'data_type' => 'string',
			),
			'LOGIN' => array(
				'data_type' => 'string',
			),
			'PASSWORD' => array(
				'data_type' => 'string',
			),
			'LAST_AUTHORIZE' => array(
				'data_type' => 'datetime',
			),
			'SETTINGS' => array(
				'data_type' => 'string',
				'serialized' => true,
			),
		);
	}

	public static function onBeforeUpdate(Main\Entity\Event $event)
	{
		$result = new Main\Entity\EventResult();

		$data = $event->getParameter("fields");

		// modify TIMESTAMP_X for every change other than single LAST_AUTHORIZE update
		if(count($data) > 1 || !array_key_exists('LAST_AUTHORIZE', $data))
		{
			$data['TIMESTAMP_X'] = new Main\Type\DateTime();
			$result->modifyFields($data);
		}

		return $result;
	}

	public static function getConnection()
	{
		$dbRes = static::getList(array(
			'order' => array('ID' => 'DESC'),
			'limit' => 1,
			'cache' => ['ttl' => 3600],
		));

		return $dbRes->fetch();
	}
}
