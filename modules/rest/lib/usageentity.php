<?php
namespace Bitrix\Rest;

use Bitrix\Main;
use \Bitrix\Main\DB\SqlQueryException;

/**
 * Class UsageEntityTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ENTITY_TYPE string(1) mandatory
 * <li> ENTITY_ID int mandatory
 * <li> ENTITY_CODE string(255) mandatory
 * <li> SUB_ENTITY_TYPE string(1) optional
 * <li> SUB_ENTITY_NAME string(255) optional
 * </ul>
 *
 * @package Bitrix\Rest
 **/

class UsageEntityTable extends Main\Entity\DataManager
{

	const ENTITY_TYPE_APPLICATION = 'A';
	const ENTITY_TYPE_WEBHOOK = 'W';

	const SUB_ENTITY_TYPE_METHOD = 'M';
	const SUB_ENTITY_TYPE_EVENT = 'E';
	const SUB_ENTITY_TYPE_PLACEMENT = 'P';
	const SUB_ENTITY_TYPE_ROBOT = 'R';
	const SUB_ENTITY_TYPE_ACTIVITY = 'A';

	protected static $info = array();

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_rest_usage_entity';
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
				'autocomplete' => true
			),
			'ENTITY_TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'values' => array(
					self::ENTITY_TYPE_APPLICATION,
					self::ENTITY_TYPE_WEBHOOK
				),
				'validation' => array(__CLASS__, 'validateEntityType')
			),
			'ENTITY_ID' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'ENTITY_CODE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateEntityCode')
			),
			'SUB_ENTITY_TYPE' => array(
				'data_type' => 'string',
				'values' => array(
					self::SUB_ENTITY_TYPE_METHOD,
					self::SUB_ENTITY_TYPE_EVENT,
					self::SUB_ENTITY_TYPE_PLACEMENT,
					self::SUB_ENTITY_TYPE_ROBOT,
					self::SUB_ENTITY_TYPE_ACTIVITY,
				),
				'validation' => array(__CLASS__, 'validateSubEntityType')
			),
			'SUB_ENTITY_NAME' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateSubEntityName')
			),
		);
	}

	protected static function getEntityInfo($entityType, $entityId)
	{
		$key = $entityType.'|'.$entityId;
		if(!isset(static::$info[$key]))
		{
			if($entityType == UsageEntityTable::ENTITY_TYPE_APPLICATION)
			{
				$appInfo = AppTable::getByClientId($entityId);
				static::$info[$key] = [
					'ENTITY_ID' => $appInfo['ID'],
					'ENTITY_CODE' => $appInfo['CODE'],
				];
			}
			else
			{
				static::$info[$key] = [
					'ENTITY_ID' => $entityId,
					'ENTITY_CODE' => '',
				];
			}
		}
		return static::$info[$key];
	}

	public static function register($entityType, $entityId, $subEntityType, $subEntityName)
	{
		$result = false;
		$entity = static::getEntityInfo($entityType, $entityId);

		while(true)
		{
			$res = static::getList(
				[
					'filter' => [
						'ENTITY_TYPE' => $entityType,
						'ENTITY_ID' => $entity['ENTITY_ID'],
						'SUB_ENTITY_TYPE' => $subEntityType,
						'SUB_ENTITY_NAME' => $subEntityName,
					],
					'select' => [
						'ID'
					],
					'limit' => 1
				]
			);
			if($element = $res->fetch())
			{
				$result = $element['ID'];
				break;
			}
			else
			{
				try
				{
					$res = static::add(
						[
							'ENTITY_TYPE' => $entityType,
							'ENTITY_ID' => $entity['ENTITY_ID'],
							'ENTITY_CODE' => $entity['ENTITY_CODE'],
							'SUB_ENTITY_TYPE' => $subEntityType,
							'SUB_ENTITY_NAME' => $subEntityName
						]
					);
					if($res->isSuccess())
					{
						$result = $res->getId();
					}
					break;
				}
				catch(SqlQueryException $e)
				{
					if(strpos($e->getMessage(),'Duplicate entry') === false)
					{
						break;
					}
				}
				catch(\Exception $e)
				{
					break;
				}
			}
		}
		return $result;
	}

	/**
	 * Returns validators for ENTITY_TYPE field.
	 *
	 * @return array
	 */
	public static function validateEntityType()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}

	/**
	 * Returns validators for ENTITY_CODE field.
	 *
	 * @return array
	 */
	public static function validateEntityCode()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for SUB_ENTITY_TYPE field.
	 *
	 * @return array
	 */
	public static function validateSubEntityType()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}

	/**
	 * Returns validators for SUB_ENTITY_NAME field.
	 *
	 * @return array
	 */
	public static function validateSubEntityName()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
}