<?php

namespace Bitrix\Rest\Configuration;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Entity\ReferenceField;

Loc::loadMessages(__FILE__);

/**
 * Class OwnerEntityTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> OWNER_TYPE string(1) mandatory
 * <li> OWNER string(11) mandatory
 * <li> ENTITY_TYPE string(32) mandatory
 * <li> ENTITY string(32) mandatory
 * </ul>
 *
 * @package Bitrix\Rest\Configuration
 **/

class OwnerEntityTable extends DataManager
{
	const ENTITY_TYPE_APPLICATION = 'A';
	const ENTITY_TYPE_EXTERNAL = 'E';

	const ENTITY_EMPTY = 0;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_rest_owner_entity';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true
				]
			),
			new StringField(
				'OWNER_TYPE',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateOwnerType']
				]
			),
			new StringField(
				'OWNER',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateOwner']
				]
			),
			new StringField(
				'ENTITY_TYPE',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateEntityType']
				]
			),
			new StringField(
				'ENTITY',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateEntity']
				]
			),
			new ReferenceField(
				'DATA_APP',
				'\Bitrix\Rest\AppTable',
				array(
					'=this.OWNER' => 'ref.ID',
				)
			)
		];
	}

	/**
	 * Returns validators for OWNER_TYPE field.
	 *
	 * @return array
	 */
	public static function validateOwnerType()
	{
		return [
			new LengthValidator(null, 1),
		];
	}

	/**
	 * Returns validators for OWNER field.
	 *
	 * @return array
	 */
	public static function validateOwner()
	{
		return [
			new LengthValidator(null, 11),
		];
	}

	/**
	 * Returns validators for ENTITY_TYPE field.
	 *
	 * @return array
	 */
	public static function validateEntityType()
	{
		return [
			new LengthValidator(null, 32),
		];
	}

	/**
	 * Returns validators for ENTITY field.
	 *
	 * @return array
	 */
	public static function validateEntity()
	{
		return [
			new LengthValidator(null, 32),
		];
	}

	/**
	 * @param $owner string(11)
	 * @param $ownerType string(1)
	 * @param $itemList array
	 */
	public static function saveMulti($owner, $ownerType, $itemList)
	{
		if(is_array($itemList))
		{
			if(!empty($itemList['ENTITY_TYPE']) && !empty($itemList['ENTITY']))
			{
				try
				{
					static::add(
						[
							'ENTITY_TYPE' => $itemList['ENTITY_TYPE'],
							'ENTITY' => $itemList['ENTITY'],
							'OWNER_TYPE' => $ownerType,
							'OWNER' => $owner,
						]
					);
				}
				catch (\Exception $e)
				{
				}
			}
			else
			{
				foreach ($itemList as $entity)
				{
					if(!empty($entity['ENTITY_TYPE']) && !empty($entity['ENTITY']))
					{
						try
						{
							static::add(
								[
									'ENTITY_TYPE' => $entity['ENTITY_TYPE'],
									'ENTITY' => $entity['ENTITY'],
									'OWNER_TYPE' => $ownerType,
									'OWNER' => $owner,
								]
							);
						}
						catch (\Exception $e)
						{
						}
					}
				}
			}
		}
	}

	/**
	 * @param $itemList array
	 */
	public static function deleteMulti($itemList)
	{

		if (is_array($itemList))
		{
			if (!empty($itemList['ENTITY_TYPE']) && !empty($itemList['ENTITY']))
			{
				$res = static::getList(
					[
						'filter' => [
							'=ENTITY_TYPE' => $itemList['ENTITY_TYPE'],
							'=ENTITY' => $itemList['ENTITY']
						]
					]
				);
				if ($item = $res->fetch())
				{
						static::delete($item['ID']);
				}
			}
			else
			{
				$entityList = [];
				foreach ($itemList as $entity)
				{
					if (!empty($entity['ENTITY_TYPE']) && !empty($entity['ENTITY']))
					{
						$entityList[$entity['ENTITY_TYPE']][] = $entity['ENTITY'];
					}
				}
				$res = static::getList(
					[
						'filter' => [
							'=ENTITY_TYPE' => array_keys($entityList)
						]
					]
				);
				while ($item = $res->fetch())
				{
					if(
						!empty($entityList[$item['ENTITY_TYPE']])
						&& in_array($item['ENTITY'], $entityList[$item['ENTITY_TYPE']])
					)
					{
						static::delete($item['ID']);
					}
				}
			}
		}
	}

	public static function checkApp($entityType, $entityId)
	{
		$res = static::getList(
			[
				'filter' => [
					'=ENTITY_TYPE' => $entityType,
					'=ENTITY' => $entityId,
					'=OWNER_TYPE' => static::ENTITY_TYPE_APPLICATION,
					'>OWNER' => 0
				],
				'select' => [
					'OWNER',
					'APP_CODE' => 'DATA_APP.CODE'
				]
			]
		);

		if ($item = $res->fetch())
		{
			if($item['OWNER'] > 0)
			{
				$url = \Bitrix\Rest\Marketplace\Url::getApplicationDetailUrl($item['APP_CODE']);
				$appStatus = \Bitrix\Rest\AppTable::getAppStatusInfo($item['OWNER'], $url);
				if($appStatus['PAYMENT_NOTIFY'] == 'Y')
				{
					return $appStatus;
				}
			}
		}

		return null;
	}
}