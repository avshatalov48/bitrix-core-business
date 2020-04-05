<?php
namespace Bitrix\Rest\APAuth;

use Bitrix\Main;

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
 **/
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
}