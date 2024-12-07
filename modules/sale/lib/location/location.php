<?php
/**
 * Bitrix Framework
 * @package Bitrix\Sale\Location
 * @subpackage sale
 * @copyright 2001-2014 Bitrix
 */
namespace Bitrix\Sale\Location;

use Bitrix\Main;
use Bitrix\Main\DB;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

use Bitrix\Sale\Location\Name;
use Bitrix\Sale\Location\Util\Assert;
use Bitrix\Sale\Location\Search;
use Bitrix\Sale\Location\DB\Helper;

Loc::loadMessages(__FILE__);

/**
 * Class LocationTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Location_Query query()
 * @method static EO_Location_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Location_Result getById($id)
 * @method static EO_Location_Result getList(array $parameters = [])
 * @method static EO_Location_Entity getEntity()
 * @method static \Bitrix\Sale\Location\EO_Location createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Location\EO_Location_Collection createCollection()
 * @method static \Bitrix\Sale\Location\EO_Location wakeUpObject($row)
 * @method static \Bitrix\Sale\Location\EO_Location_Collection wakeUpCollection($rows)
 */
final class LocationTable extends Tree
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_location';
	}

	/**
	* Returns location with the specified code.
	*
	* @param string $code location code to search for
	*
	* @throws Bitrix\Main\ArgumentNullException
	*
	* @return Bitrix\Main\DB\Result location
	*/
	public static function getByCode($code = '', $parameters = array())
	{
		$code = Assert::expectStringNotNull($code, '$code');

		if(!is_array($parameters))
			$parameters = array();

		$parameters['filter']['=CODE'] = $code;
		$parameters['limit'] = 1;

		return self::getList($parameters);
	}

	public static function checkFields(Entity\Result $result, $primary, array $data)
	{
		parent::checkFields($result, $primary, $data);

		foreach(static::getEntity()->getFields() as $field)
		{
			$error = false;

			if ($field->getName() === 'LATITUDE')
			{
				$latitude = (string)($data['LATITUDE'] ?? null);
				if ($latitude !== '')
				{
					// latitude is set in data and not empty, it must lay between -90 and 90
					if (!is_numeric($latitude))
					{
						$error = Loc::getMessage('SALE_LOCATION_LOCATION_ENTITY_LATITUDE_TYPE_ERROR');
					}
					else
					{
						$latitude = (float)$latitude;
						if ($latitude < -90 || $latitude > 90)
						{
							$error = Loc::getMessage('SALE_LOCATION_LOCATION_ENTITY_LATITUDE_RANGE_ERROR');
						}
					}
				}
			}

			if ($field->getName() === 'LONGITUDE')
			{
				$longitude = (string)($data['LONGITUDE'] ?? null);
				if ($longitude !== '')
				{
					// longitude is set in data and not empty, it must lay between -180 and 180
					if (!is_numeric($longitude))
					{
						$error = Loc::getMessage('SALE_LOCATION_LOCATION_ENTITY_LONGITUDE_TYPE_ERROR');
					}
					else
					{
						$longitude = (float)$longitude;
						if ($longitude < -180 || $longitude > 180)
						{
							$error = Loc::getMessage('SALE_LOCATION_LOCATION_ENTITY_LONGITUDE_RANGE_ERROR');
						}
					}
				}
			}

			if ($error !== false)
			{
				$result->addError(new Entity\FieldError(
					$field,
					$error,
					Entity\FieldError::INVALID_VALUE
				));
			}
		}
	}

	public static function add(array $data)
	{
		return self::addExtended($data);
	}

	/**
	* Adds a new location
	*
	* @param mixed[] $data to be added. Additional data keys could be passed:
	*
	*	<ul>
	*		<li>
	*			NAME string[] : add name string to a newly created location
	*		</li>
	*		<li>
	*			EXTERNAL string[] : add external data records to a newly created location
	*		</li>
	*	</ul>
	*
	* @param mixed[] $additional an additional behaviour flags:
	*
	*	<ul>
	*		<li>
	*			REBALANCE boolean (default: true) : do rebalance after add
	*		</li>
	*	</ul>
	*
	* @return \Bitrix\Main\Entity\AddResult the result of add operation
	*/
	public static function addExtended(array $data, array $additional = array())
	{
		$resetLegacy = !isset($additional['RESET_LEGACY']) || $additional['RESET_LEGACY'] !== false;

		if(isset($data['EXTERNAL']))
		{
			$external = $data['EXTERNAL'];
			unset($data['EXTERNAL']);
		}

		if(isset($data['NAME']))
		{
			$name = $data['NAME'];
			unset($data['NAME']);
		}

		// force code to lowercase
		if(isset($data['CODE']))
			$data['CODE'] = mb_strtolower($data['CODE']);

		// you are not allowed to modify tree data over LocationTable::add()
		self::applyRestrictions($data);

		// store tree data and basic
		$addResult = parent::addExtended($data, $additional);

		// add connected data
		if($addResult->isSuccess())
		{
			$primary = $addResult->getId();

			// external
			if(isset($external))
				ExternalTable::addMultipleForOwner($primary, $external);

			// names
			if(isset($name))
				Name\LocationTable::addMultipleForOwner($primary, $name);

			if(intval($data['TYPE_ID']) > 0 && $resetLegacy)
				self::resetLegacy(intval($data['TYPE_ID']));

			Search\Finder::setIndexInvalid();
			$GLOBALS['CACHE_MANAGER']->ClearByTag('sale-location-data');
		}

		return $addResult;
	}

	protected static function resetLegacy($typeId)
	{
		$type = TypeTable::getList(array('filter' => array('=ID' => $typeId), 'select' => array('CODE')))->fetch();
		if(mb_strlen($type['CODE']) && in_array($type['CODE'], array('COUNTRY', 'REGION', 'CITY')))
			static::resetLegacyPath();
	}

	public static function update($primary, array $data)
	{
		return self::updateExtended($primary, $data);
	}
	/**
	* Updates an existed location
	*
	* @param integer $primary location primary key of a element being updated
	* @param mixed[] $data new data to set. Additional data keys could be passed:
	*
	*	<ul>
	*		<li>
	*			NAME string[] : update name string for specified location
	*		</li>
	*		<li>
	*			EXTERNAL string[] : update external data records for specified location
	*		</li>
	*	</ul>
	*
	* @param mixed[] $additional an additional behaviour flags:
	*
	*	<ul>
	*		<li>
	*			REBALANCE boolean (default: true) : do rebalancing after add
	*		</li>
	*	</ul>
	*
	* @return \Bitrix\Main\Entity\UpdateResult the result of update operation
	*/
	public static function updateExtended($primary, array $data, array $additional = array())
	{
		$primary = Assert::expectIntegerPositive($primary, '$primary');
		$resetLegacy = !isset($additional['RESET_LEGACY']) || $additional['RESET_LEGACY'] !== false;

		// first update parent, and if it succeed, do updates of the connected data

		if(isset($data['EXTERNAL']))
		{
			$external = $data['EXTERNAL'];
			unset($data['EXTERNAL']);
		}

		if(isset($data['NAME']))
		{
			$name = $data['NAME'];
			unset($data['NAME']);
		}

		// force code to lowercase
		if(isset($data['CODE']))
			$data['CODE'] = mb_strtolower($data['CODE']);

		// you are not allowed to modify tree data over LocationTable::update()
		self::applyRestrictions($data);

		$updResult = parent::updateExtended($primary, $data, $additional);

		// update connected data
		if($updResult->isSuccess())
		{
			// external
			if(isset($external))
				ExternalTable::updateMultipleForOwner($primary, $external);

			// names
			if(isset($name))
				Name\LocationTable::updateMultipleForOwner($primary, $name);

			if($resetLegacy && (intval($data['TYPE_ID']) > 0 || isset($data['PARENT_ID'])))
				self::resetLegacy(intval($data['TYPE_ID']));

			$GLOBALS['CACHE_MANAGER']->ClearByTag('sale-location-data');

			if(isset($name) || isset($data['PARENT_ID']))
				Search\Finder::setIndexInvalid();
		}

		return $updResult;
	}

	public static function delete($primary)
	{
		return self::deleteExtended($primary);
	}
	/**
	 * Deletes location from the tree
	 *
	 * @param $primary
	 * @param array $additional
	 * @return Entity\DeleteResult
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 * @throws Tree\NodeNotFoundException
	 */
	public static function deleteExtended($primary, array $additional = array())
	{
		$primary = Assert::expectIntegerPositive($primary, '$primary');
		$resetLegacy = !isset($additional['RESET_LEGACY']) || $additional['RESET_LEGACY'] !== false;
		$deleteSubtree = !isset($additional['DELETE_SUBTREE']) || $additional['DELETE_SUBTREE'] !== false;

		// delete connected data of sub-nodes
		if($deleteSubtree)
		{
			$rangeSql = parent::getSubtreeRangeSqlForNode($primary);
			Name\LocationTable::deleteMultipleByParentRangeSql($rangeSql);
			ExternalTable::deleteMultipleByParentRangeSql($rangeSql);
		}

		if($resetLegacy)
			$data = static::getList(array('filter' => array('=ID' => $primary), 'select' => array('TYPE_ID')))->fetch();

		$delResult = parent::deleteExtended($primary, $additional);

		// delete connected data
		if($delResult->isSuccess())
		{
			Name\LocationTable::deleteMultipleForOwner($primary);
			ExternalTable::deleteMultipleForOwner($primary);

			if($resetLegacy && intval($data['TYPE_ID']))
			{
				$type = TypeTable::getList(array('filter' => array('=ID' => $data['TYPE_ID']), 'select' => array('CODE')))->fetch();
				if(mb_strlen($type['CODE']) && in_array($type['CODE'], array('COUNTRY', 'REGION', 'CITY')))
					static::resetLegacyPath();
			}

			$GLOBALS['CACHE_MANAGER']->ClearByTag('sale-location-data');
			Search\Finder::setIndexInvalid();
		}

		return $delResult;
	}

	/**
	*
	*
	*
	*/
	public static function getExternalData($primary, $parameters = array())
	{
		$primary = Assert::expectIntegerPositive($primary, '$primary');

		if(!is_array($parameters) || empty($parameters))
			$parameters = array();

		$parameters['filter']['LOCATION_ID'] = $primary;

		return ExternalTable::getList($parameters);
	}

	// todo: make getList with SITE_ID parameter to have an ability to filter by SITE_ID using orm (even slowly)

	/**
	 * Fetches a parent chain of a specified node, using its code
	 *
	 * Available keys in $behaviour
	 * SHOW_LEAF : if set to true, return node itself in the result
	 */
	public static function getPathToNodeByCode($code, $parameters, $behaviour = array('SHOW_LEAF' => true))
	{
		$code = Assert::expectStringNotNull($code, '$code');

		return self::getPathToNodeByCondition(array('=CODE' => $code), $parameters, $behaviour);
	}

	public static function checkNodeIsParentOfNode($primary, $childPrimary, $behaviour = array('ACCEPT_CODE' => false, 'CHECK_DIRECT' => false))
	{
		if(!$behaviour['ACCEPT_CODE'])
			return static::checkNodeIsParentOfNodeById($primary, $childPrimary, $behaviour);

		$primary = Assert::expectStringNotNull($primary, '$primary');
		$childPrimary = Assert::expectStringNotNull($childPrimary, '$childPrimary');

		return static::checkNodeIsParentOfNodeByFilters(array('=CODE' => $primary), array('=CODE' => $childPrimary), $behaviour);
	}

	public static function resetLegacyPath()
	{
		$dbConnection = Main\HttpApplication::getConnection();
		$locTable = static::getTableName();

		$types = array();
		$res = TypeTable::getList(array(
			'filter' => array('CODE' => array('COUNTRY', 'REGION', 'CITY')),
			'select' => array('ID', 'CODE')
		));
		while($item = $res->fetch())
			$types[$item['CODE']] = $item['ID'];

		if(!empty($types))
		{
			if(!$dbConnection->isTableExists('b_sale_loc_rebind'))
				$dbConnection->query("create table b_sale_loc_rebind (TARGET_ID ".Helper::getSqlForDataType('int').", LOCATION_ID ".Helper::getSqlForDataType('int').")");
			else
				$dbConnection->query("truncate table b_sale_loc_rebind");

			$sqlWhere = array();
			foreach($types as $code => $id)
				$sqlWhere[] = "'".intval($id)."'";

			$dbConnection->query("update ".$locTable." set COUNTRY_ID = NULL, REGION_ID = NULL, CITY_ID = NULL where TYPE_ID in (".implode(', ', $sqlWhere).")");

			if(intval($types['REGION']) && intval($types['COUNTRY']))
			{
				// countries for regions
				$dbConnection->query("insert into b_sale_loc_rebind (TARGET_ID, LOCATION_ID) select A.ID as ONE, B.ID as TWO from ".$locTable." A inner join ".$locTable." B on A.TYPE_ID = '".intval($types['REGION'])."' and B.TYPE_ID = '".intval($types['COUNTRY'])."' and B.LEFT_MARGIN <= A.LEFT_MARGIN and B.RIGHT_MARGIN >= A.RIGHT_MARGIN");
				Helper::mergeTables($locTable, 'b_sale_loc_rebind', array('COUNTRY_ID' => 'LOCATION_ID'), array('ID' => 'TARGET_ID'));
				$dbConnection->query("truncate table b_sale_loc_rebind");
			}

			if(intval($types['REGION']) && intval($types['CITY']))
			{
				// regions for cities
				$dbConnection->query("insert into b_sale_loc_rebind (TARGET_ID, LOCATION_ID) select A.ID as ONE, B.ID as TWO from ".$locTable." A inner join ".$locTable." B on A.TYPE_ID = '".intval($types['CITY'])."' and B.TYPE_ID = '".intval($types['REGION'])."' and B.LEFT_MARGIN <= A.LEFT_MARGIN and B.RIGHT_MARGIN >= A.RIGHT_MARGIN");
				Helper::mergeTables($locTable, 'b_sale_loc_rebind', array('REGION_ID' => 'LOCATION_ID'), array('ID' => 'TARGET_ID'));
				$dbConnection->query("truncate table b_sale_loc_rebind");
			}

			if(intval($types['COUNTRY']) && intval($types['CITY']))
			{
				// countries for cities
				$dbConnection->query("insert into b_sale_loc_rebind (TARGET_ID, LOCATION_ID) select A.ID as ONE, B.ID as TWO from ".$locTable." A inner join ".$locTable." B on A.TYPE_ID = '".intval($types['CITY'])."' and B.TYPE_ID = '".intval($types['COUNTRY'])."' and B.LEFT_MARGIN <= A.LEFT_MARGIN and B.RIGHT_MARGIN >= A.RIGHT_MARGIN");
				Helper::mergeTables($locTable, 'b_sale_loc_rebind', array('COUNTRY_ID' => 'LOCATION_ID'), array('ID' => 'TARGET_ID'));
			}

			Helper::dropTable('b_sale_loc_rebind');

			if(intval($types['COUNTRY']))
				$dbConnection->query("update ".$locTable." set COUNTRY_ID = ID where TYPE_ID = '".intval($types['COUNTRY'])."'");

			if(intval($types['REGION']))
				$dbConnection->query("update ".$locTable." set REGION_ID = ID where TYPE_ID = '".intval($types['REGION'])."'");

			if(intval($types['CITY']))
				$dbConnection->query("update ".$locTable." set CITY_ID = ID where TYPE_ID = '".intval($types['CITY'])."'");
		}
	}

	public static function getCodeValidators()
	{
		return array(
			new Entity\Validator\Unique(),
		);
	}

	public static function getMap()
	{
		return array(

			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => 'ID'
			),
			'CODE' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('SALE_LOCATION_LOCATION_ENTITY_CODE_FIELD'),
				'required' => true,
				'validation' => array(__CLASS__, 'getCodeValidators')
			),

			'LEFT_MARGIN' => array(
				'data_type' => 'integer',
			),
			'RIGHT_MARGIN' => array(
				'data_type' => 'integer',
			),
			'DEPTH_LEVEL' => array(
				'data_type' => 'integer',
			),
			'SORT' => array(
				'data_type' => 'integer',
				'default_value' => 100,
				'title' => Loc::getMessage('SALE_LOCATION_LOCATION_ENTITY_SORT_FIELD')
			),
			'PARENT_ID' => array(
				'data_type' => 'integer',
				'default_value' => 0,
				'title' => Loc::getMessage('SALE_LOCATION_LOCATION_ENTITY_PARENT_ID_FIELD')
			),
			'TYPE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('SALE_LOCATION_LOCATION_ENTITY_TYPE_ID_FIELD')
			),
			'LATITUDE' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('SALE_LOCATION_LOCATION_ENTITY_LATITUDE_FIELD')
			),
			'LONGITUDE' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('SALE_LOCATION_LOCATION_ENTITY_LONGITUDE_FIELD')
			),

			// virtual
			'TYPE' => array(
				'data_type' => 'Bitrix\Sale\Location\Type',
				'reference' => array(
					'=this.TYPE_ID' => 'ref.ID'
				),
				'join_type' => "inner"
			),
			'NAME' => array(
				'data_type' => 'Bitrix\Sale\Location\Name\Location',
				'reference' => array(
					'=this.ID' => 'ref.LOCATION_ID'
				),
				'join_type' => "inner"
			),
			'PARENT' => array(
				'data_type' => 'Bitrix\Sale\Location\Location',
				'reference' => array(
					'=this.PARENT_ID' => 'ref.ID'
				)
			),
			'PARENTS' => array(
				'data_type' => 'Bitrix\Sale\Location\Location',
				'reference' => array(
					'<=ref.LEFT_MARGIN' => 'this.LEFT_MARGIN',
					'>=ref.RIGHT_MARGIN' => 'this.RIGHT_MARGIN'
				)
			),
			'CHILDREN' => array(
				'data_type' => 'Bitrix\Sale\Location\Location',
				'reference' => array(
					'=this.ID' => 'ref.PARENT_ID'
				)
			),
			'EXTERNAL' => array(
				'data_type' => 'Bitrix\Sale\Location\External',
				'reference' => array(
					'=this.ID' => 'ref.LOCATION_ID'
				)
			),
			'DEFAULT_SITE' => array(
				'data_type' => 'Bitrix\Sale\Location\DefaultSite',
				'reference' => array(
					'=this.CODE' => 'ref.LOCATION_CODE'
				)
			),

			'CNT' => array(
				'data_type' => 'integer',
				'expression' => array(
					'count(*)'
				)
			),
			'CHILDREN_CNT' => array(
				'data_type' => 'integer',
				'expression' => array(
					'count(%s)',
					'CHILD.ID'
				)
			),
			'IS_PARENT' => array(
				'data_type' => 'boolean',
				'expression' => array(
					'case when count(%s) > 0 then 1 else 0 end',
					'CHILD.ID'
				)
			),

			// do not remove unless you want migrator to be dead
			'COUNTRY_ID' => array(
				'data_type' => 'integer',
			),
			'REGION_ID' => array(
				'data_type' => 'integer',
			),
			'CITY_ID' => array(
				'data_type' => 'integer',
			),
			'LOC_DEFAULT' => array(
				'data_type' => 'string',
			),

			// deprecated aliases
			'CHILD' => array(
				'data_type' => 'Bitrix\Sale\Location\Location',
				'reference' => array(
					'=this.ID' => 'ref.PARENT_ID'
				)
			),
			'CHILD_CNT' => array(
				'data_type' => 'integer',
				'expression' => array(
					'count(%s)',
					'CHILD.ID'
				)
			),
			'DEFAULT_SORT' => array(
				'data_type' => 'Bitrix\Sale\Location\DefaultSiteTable',
				'reference' => array(
					'=this.CODE' => 'ref.LOCATION_CODE'
				)
			),

		);
	}

	/**
	 * @deprecated
	 */
	public static function getListFast($parameters = array())
	{
		// here $parameters conversion required

		if(isset($parameters['filter']['NAME']))
		{
			$parameters['filter']['PHRASE'] = $parameters['filter']['NAME'];
			unset($parameters['filter']['NAME']);
		}

		if(isset($parameters['filter']['LANGUAGE_ID']))
		{
			$parameters['filter']['NAME.LANGUAGE_ID'] = $parameters['filter']['LANGUAGE_ID'];
			unset($parameters['filter']['LANGUAGE_ID']);
		}

		return \Bitrix\Sale\Location\Search\Finder::find($parameters, array('USE_INDEX' => false, 'USE_ORM' => false));
	}
}

