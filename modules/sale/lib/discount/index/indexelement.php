<?php
namespace Bitrix\Sale\Discount\Index;
use Bitrix\Main\Application;
use Bitrix\Main\DB\MssqlConnection;
use Bitrix\Main\DB\MysqlCommonConnection;
use Bitrix\Main\DB\OracleConnection;
use Bitrix\Main\Entity\DataManager;


/**
 * Class IndexElementTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_IndexElement_Query query()
 * @method static EO_IndexElement_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_IndexElement_Result getById($id)
 * @method static EO_IndexElement_Result getList(array $parameters = array())
 * @method static EO_IndexElement_Entity getEntity()
 * @method static \Bitrix\Sale\Discount\Index\EO_IndexElement createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Discount\Index\EO_IndexElement_Collection createCollection()
 * @method static \Bitrix\Sale\Discount\Index\EO_IndexElement wakeUpObject($row)
 * @method static \Bitrix\Sale\Discount\Index\EO_IndexElement_Collection wakeUpCollection($rows)
 */
final class IndexElementTable extends DataManager
{
	const MAX_LENGTH_BATCH_MYSQL_QUERY = 2048;
	
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_d_ix_element';
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
			'DISCOUNT_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'DISCOUNT' => array(
				'data_type' => '\Bitrix\Sale\Internals\DiscountTable',
				'reference' => array(
					'=this.DISCOUNT_ID' => 'ref.ID'
				),
				'join_type' => 'INNER',
			),
			'DISCOUNT_GROUP' => array(
				'data_type' => '\Bitrix\Sale\Internals\DiscountGroupTable',
				'reference' => array(
					'=this.DISCOUNT_ID' => 'ref.DISCOUNT_ID'
				),
				'join_type' => 'INNER',
			),
			'ELEMENT_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
		);
	}

	/**
	 * Deletes rows by discount id.
	 *
	 * @param int $discountId Id of discount.
	 * @return void
	 */
	public static function deleteByDiscount($discountId)
	{
		$discountId = (int)$discountId;
		if($discountId <= 0)
		{
			return;
		}
		
		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();
		$connection->queryExecute(
			'delete from ' . $helper->quote(self::getTableName()) . 
			' where ' . $helper->quote('DISCOUNT_ID') . ' = ' . $discountId
		);
	}
	
	public static function fillByDiscount($discountId, array $indexData)
	{
		$items = array();
		foreach($indexData as $elementId)
		{
			$items[] = array(
				'DISCOUNT_ID' => $discountId,
				'ELEMENT_ID' => $elementId,
			);
		}

		static::insertBatch($items);
	}

	/**
	 * Adds rows to table.
	 * @param array $items Items.
	 * @internal
	 */
	private static function insertBatch(array $items)
	{
		$tableName = static::getTableName();
		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$query = $prefix = '';
		if($connection instanceof MysqlCommonConnection)
		{
			foreach($items as $item)
			{
				list($prefix, $values) = $sqlHelper->prepareInsert($tableName, $item);

				$query .= ($query ? ', ' : ' ') . '(' . $values . ')';
				if(mb_strlen($query) > self::MAX_LENGTH_BATCH_MYSQL_QUERY)
				{
					$connection->queryExecute("INSERT INTO {$tableName} ({$prefix}) VALUES {$query}");
					$query = '';
				}
			}
			unset($item);

			if($query && $prefix)
			{
				$connection->queryExecute("INSERT INTO {$tableName} ({$prefix}) VALUES {$query}");
			}
		}
		elseif($connection instanceof MssqlConnection)
		{
			$valueData = array();
			foreach($items as $item)
			{
				list($prefix, $values) = $sqlHelper->prepareInsert($tableName, $item);
				$valueData[] = "SELECT {$values}";
			}
			unset($item);

			$valuesSql = implode(' UNION ALL ', $valueData);
			if($valuesSql && $prefix)
			{
				$connection->queryExecute("INSERT INTO {$tableName} ({$prefix}) $valuesSql");
			}
		}
		elseif($connection instanceof OracleConnection)
		{
			$valueData = array();
			foreach($items as $item)
			{
				list($prefix, $values) = $sqlHelper->prepareInsert($tableName, $item);
				$valueData[] = "SELECT {$values} FROM dual";
			}
			unset($item);

			$valuesSql = implode(' UNION ALL ', $valueData);
			if($valuesSql && $prefix)
			{
				$connection->queryExecute("INSERT INTO {$tableName} ({$prefix}) $valuesSql");
			}
		}
	}	
}