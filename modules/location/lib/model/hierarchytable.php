<?php
namespace Bitrix\Location\Model;

use Bitrix\Main,
	Bitrix\Main\ORM\Fields;
use Bitrix\Main\Application;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;


/**
 * Class HierarchyTable
 *
 * Fields:
 * <ul>
 * <li> ANCESTOR_ID int mandatory
 * <li> DESCENDANT_ID int mandatory
 * <li> LEVEL int mandatory
 * </ul>
 *
 * @package Bitrix\Location
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Hierarchy_Query query()
 * @method static EO_Hierarchy_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Hierarchy_Result getById($id)
 * @method static EO_Hierarchy_Result getList(array $parameters = array())
 * @method static EO_Hierarchy_Entity getEntity()
 * @method static \Bitrix\Location\Model\EO_Hierarchy createObject($setDefaultValues = true)
 * @method static \Bitrix\Location\Model\EO_Hierarchy_Collection createCollection()
 * @method static \Bitrix\Location\Model\EO_Hierarchy wakeUpObject($row)
 * @method static \Bitrix\Location\Model\EO_Hierarchy_Collection wakeUpCollection($rows)
 */

class HierarchyTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_location_hierarchy';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(

			(new Fields\IntegerField('ANCESTOR_ID'))
				->configurePrimary(true),

			(new Fields\IntegerField('DESCENDANT_ID'))
				->configurePrimary(true),

			(new Fields\IntegerField('LEVEL'))
				->configureRequired(true),

			// References

			(new Reference('ANCESTOR', LocationTable::class,
				Join::on('this.ANCESTOR_ID', 'ref.ID')))
				->configureJoinType('inner'),

			(new Reference('DESCENDANT', LocationTable::class,
				Join::on('this.DESCENDANT_ID', 'ref.ID')))
				->configureJoinType('inner')
		);
	}

	/**
	 * @param array $data
	 * @return Main\DB\Result
	 * @throws Main\Db\SqlQueryException
	 */
	public static function insertBatch(array $data)
	{
		$values = [];

		foreach ($data as $row)
		{
			if((int)$row['ANCESTOR_ID'] <= 0 || (int)$row['DESCENDANT_ID'] <= 0)
			{
				continue;
			}

			$values[] = (int)$row['ANCESTOR_ID'].', '.(int)$row['DESCENDANT_ID'].', '.(int)$row['LEVEL'];
		}

		if(!empty($values))
		{
			$values = '(' . implode('), (', $values) . ')';
			$sql = "INSERT IGNORE INTO " . static::getTableName() . " (ANCESTOR_ID, DESCENDANT_ID, LEVEL) VALUES " . $values;
			Application::getConnection()->queryExecute($sql);
		}
	}

	/**
	 * @param int $locationId
	 * @throws Main\Db\SqlQueryException
	 */
	public static function deleteByLocationId(int $locationId)
	{
		$locationId = (int)$locationId;

		Application::getConnection()->queryExecute("
			DELETE 
				FROM ".self::getTableName()." 
			WHERE ANCESTOR_ID=".$locationId."
				OR DESCENDANT_ID = ".$locationId
		);
	}
}