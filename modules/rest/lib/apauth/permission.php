<?php
namespace Bitrix\Rest\APAuth;

use Bitrix\Main;
use Bitrix\Rest\Preset\EventController;

/**
 * Class PermissionTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PASSWORD_ID int mandatory
 * <li> PERM string(255) mandatory
 * </ul>
 *
 * @package Bitrix\Rest
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Permission_Query query()
 * @method static EO_Permission_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Permission_Result getById($id)
 * @method static EO_Permission_Result getList(array $parameters = array())
 * @method static EO_Permission_Entity getEntity()
 * @method static \Bitrix\Rest\APAuth\EO_Permission createObject($setDefaultValues = true)
 * @method static \Bitrix\Rest\APAuth\EO_Permission_Collection createCollection()
 * @method static \Bitrix\Rest\APAuth\EO_Permission wakeUpObject($row)
 * @method static \Bitrix\Rest\APAuth\EO_Permission_Collection wakeUpCollection($rows)
 */
class PermissionTable extends Main\Entity\DataManager
{
	protected static $deniedPermission = array(
		'rating', 'entity', 'placement', 'landing_cloud', \CRestUtil::GLOBAL_SCOPE
	);

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_rest_ap_permission';
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
			'PASSWORD_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'PERM' => array(
				'data_type' => 'string',
				'required' => true,
			),
		);
	}

	public static function onPasswordDelete(Main\Entity\Event $event)
	{
		$data = $event->getParameter("id");
		static::deleteByPasswordId($data['ID']);
	}

	public static function deleteByPasswordId($passwordId)
	{
		$dbRes = static::getList(
			array(
				'filter' => array
				(
					'=PASSWORD_ID' => $passwordId,
				),
				'select' => array('ID')
			)
		);
		while($perm = $dbRes->fetch())
		{
			static::delete($perm['ID']);
		}
	}

	public static function cleanPermissionList(array $permissionList)
	{
		foreach($permissionList as $key => $perm)
		{
			if(in_array($perm, static::$deniedPermission))
			{
				unset($permissionList[$key]);
			}
		}

		return array_values($permissionList);
	}

	public static function onAfterAdd(Main\Entity\Event $event)
	{
		EventController::onAfterAddApPermission($event);
	}
}