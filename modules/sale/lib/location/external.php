<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2014 Bitrix
 */
namespace Bitrix\Sale\Location;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

use Bitrix\Sale\Location\Util\Assert;

Loc::loadMessages(__FILE__);

/**
 * Class ExternalTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_External_Query query()
 * @method static EO_External_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_External_Result getById($id)
 * @method static EO_External_Result getList(array $parameters = [])
 * @method static EO_External_Entity getEntity()
 * @method static \Bitrix\Sale\Location\EO_External createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Location\EO_External_Collection createCollection()
 * @method static \Bitrix\Sale\Location\EO_External wakeUpObject($row)
 * @method static \Bitrix\Sale\Location\EO_External_Collection wakeUpCollection($rows)
 */
class ExternalTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_loc_ext';
	}

	public static function addMultipleForOwner($primaryOwner, $external = array())
	{
		$primaryOwner = Assert::expectIntegerPositive($primaryOwner, '$primaryOwner');

		// nothing to connect to, simply exit
		if(!is_array($external) || empty($external))
			return false;

		foreach($external as $data)
		{
			$serivceId = intval($data['SERVICE_ID']);

			if($serivceId && mb_strlen($data['XML_ID']))
			{
				$res = self::add(array(
					'SERVICE_ID' => $serivceId,
					'XML_ID' => $data['XML_ID'],
					'LOCATION_ID' => $primaryOwner
				));
				if(!$res->isSuccess())
					throw new Main\SystemException(Loc::getMessage('SALE_LOCATION_EXTERNAL_ENTITY_CANNOT_ADD_DATA_EXCEPTION')); // .': '.implode(', ', $res->getErrorMessages())
			}
		}

        return true;
	}

	public static function updateMultipleForOwner($primaryOwner, $external)
	{
		$primaryOwner = Assert::expectIntegerPositive($primaryOwner, '$primaryOwner');

		$res = self::getList(array(
			'filter' => array('LOCATION_ID' => $primaryOwner)
		));

		$existed = array();
		while($item = $res->fetch())
			$existed[$item['ID']][$item['SERVICE_ID']] = $item['XML_ID'];

		foreach($external as $id => $data)
		{
			$data['REMOVE'] ??= false;
			$data['SERVICE_ID'] ??= 0;
			$data['XML_ID'] = (string)($data['XML_ID'] ?? '');

			$serivceId = (int)$data['SERVICE_ID'];
			$id = (int)$id;

			if (isset($existed[$id]))
			{
				if ($data['XML_ID'] === '' || !$serivceId || $data['REMOVE'])
				{
					// field either empty or prepared to remove
					self::delete($id);
				}
				else
				{
					$res = self::update(
						$id,
						[
							'SERVICE_ID' => $serivceId,
							'XML_ID' => $data['XML_ID'],
						]
					);
					if (!$res->isSuccess())
					{
						throw new Main\SystemException(Loc::getMessage('SALE_LOCATION_EXTERNAL_ENTITY_CANNOT_UPDATE_DATA_EXCEPTION'));
					}
				}
			}
			else
			{
				if ($serivceId && $data['XML_ID'] !== '')
				{
					$res = self::add([
						'SERVICE_ID' => $serivceId,
						'XML_ID' => $data['XML_ID'],
						'LOCATION_ID' => $primaryOwner,
					]);
					if (!$res->isSuccess())
					{
						throw new Main\SystemException(Loc::getMessage('SALE_LOCATION_EXTERNAL_ENTITY_CANNOT_ADD_DATA_EXCEPTION'));
					}
				}
			}
		}
	}

	public static function deleteMultipleForOwner($primaryOwner)
	{
		$primaryOwner = Assert::expectIntegerPositive($primaryOwner, '$primaryOwner');

		$listRes = self::getList(array(
			'filter' => array('LOCATION_ID' => $primaryOwner),
			'select' => array('ID')
		));
		while($item = $listRes->fetch())
		{
			$res = self::delete($item['ID']);
			if(!$res->isSuccess())
				throw new Main\SystemException(Loc::getMessage('SALE_LOCATION_EXTERNAL_ENTITY_CANNOT_DELETE_DATA_EXCEPTION'));
		}
	}

	/**
	 * This method is for internal use only. It may be changed without any notification further, or even mystically disappear.
	 *
	 * @access private
	 */
	public static function deleteMultipleByParentRangeSql($sql)
	{
		if($sql == '')
			throw new Main\SystemException('Range sql is empty');

		$dbConnection = Main\HttpApplication::getConnection();

		$dbConnection->query('delete from '.static::getTableName().' where LOCATION_ID in ('.$sql.')');
	}

	public static function getMap()
	{
		return array(

			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'SERVICE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('SALE_LOCATION_EXTERNAL_ENTITY_SERVICE_ID_FIELD')
			),
			'XML_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SALE_LOCATION_EXTERNAL_ENTITY_XML_ID_FIELD')
			),
			'LOCATION_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),

			// virtual
			'SERVICE' => array(
				'data_type' => '\Bitrix\Sale\Location\ExternalService',
				'reference' => array(
					'=this.SERVICE_ID' => 'ref.ID'
				)
			),
			'LOCATION' => array(
				'data_type' => '\Bitrix\Sale\Location\Location',
				'reference' => array(
					'=this.LOCATION_ID' => 'ref.ID'
				)
			)
		);
	}
}
