<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Sale\Internals;

use Bitrix\Main;
use Bitrix\Sale;

/**
 * Class OrderArchiveTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_OrderArchive_Query query()
 * @method static EO_OrderArchive_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_OrderArchive_Result getById($id)
 * @method static EO_OrderArchive_Result getList(array $parameters = [])
 * @method static EO_OrderArchive_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_OrderArchive createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_OrderArchive_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_OrderArchive wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_OrderArchive_Collection wakeUpCollection($rows)
 */
class OrderArchiveTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_order_archive';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			new Main\Entity\IntegerField(
				'ID',
				array(
					'autocomplete' => true,
					'primary' => true,
				)
			),
			new Main\Entity\StringField(
				'LID',
				array(
					'required' => true,
				)
			),
			new Main\Entity\IntegerField(
				'ORDER_ID',
				array(
					'required' => true,
				)
			),
			new Main\Entity\StringField(
				'ACCOUNT_NUMBER',
				array(
					'size' => 100,
					'required' => true,
				)
			),			
			new Main\Entity\StringField(
				'USER_ID',
				array(
					'required' => true,
				)
			),
			new Main\Entity\ReferenceField(
				'USER',
				'\Bitrix\Main\User',
				array('=this.USER_ID' => 'ref.ID'),
				array('join_type' => 'INNER')
			),
			new Main\Entity\StringField(
				'PERSON_TYPE_ID',
				array(
					'required' => true,
				)
			),

			new Main\Entity\StringField('STATUS_ID'),

			new Main\Entity\ReferenceField(
				'STATUS',
				'Bitrix\Sale\Internals\StatusLang',
				array(
					'=this.STATUS_ID' => 'ref.STATUS_ID',
					'=ref.LID' => array('?', LANGUAGE_ID)
				)
			),

			new Main\Entity\BooleanField(
				'PAYED',
				array(
					'values' => array('N', 'Y')
				)
			),

			new Main\Entity\BooleanField(
				'DEDUCTED',
				array(
					'values' => array('N','Y')
				)
			),

			new Main\Entity\BooleanField(
				'CANCELED',
				array(
					'values' => array('N', 'Y')
				)
			),

			new Main\Entity\FloatField(
				'PRICE',
				array(
					'default_value' => '0.0000'
				)
			),

			new Main\Entity\FloatField(
				'SUM_PAID',
				array(
					'default_value' => '0.0000'
				)
			),

			new Main\Entity\StringField(
				'CURRENCY',
				array(
					'required' => true,
					'size' => 3
				)
			),

			new Main\Entity\IntegerField(
				'VERSION',
				array(
					'required' => true,
				)
			),

			new Main\Entity\IntegerField('XML_ID'),

			new Main\Entity\IntegerField('ID_1C'),

			new Main\Entity\DatetimeField('DATE_ARCHIVED'),
			
			new Main\Entity\DatetimeField('DATE_INSERT'),

			new Main\Entity\IntegerField('RESPONSIBLE_ID'),
			
			new Main\Entity\IntegerField('COMPANY_ID'),
			
			new Main\Entity\StringField('ORDER_DATA'),

			new Main\Entity\ReferenceField(
				'BASKET_ARCHIVE',
				'Bitrix\Sale\Internals\BasketArchive',
				array(
					'=ref.ARCHIVE_ID' => 'this.ID'
				)
			),

			new Main\Entity\ReferenceField(
				'ORDER_PACKED',
				'Bitrix\Sale\Internals\OrderArchivePacked',
				array('=this.ID' => 'ref.ORDER_ARCHIVE_ID'),
				array('join_type' => 'INNER')
			)
		);
	}

	/**
	 * Adds row to entity table
	 *
	 * @param array $data An array with fields like
	 * 	array(
	 * 		"fields" => array(
	 * 			"FIELD1" => "value1",
	 * 			"FIELD2" => "value2",
	 * 		),
	 * 		"auth_context" => \Bitrix\Main\Authentication\Context object
	 *	)
	 *	or just a plain array of fields.
	 *
	 * @return Main\Entity\AddResult Contains ID of inserted row
	 *
	 * @throws \Exception
	 */
	public static function add(array $data)
	{
		$orderData = $data['ORDER_DATA'];
		unset($data['ORDER_DATA']);

		$result = parent::add($data);

		if ($result->isSuccess())
		{
			OrderArchivePackedTable::add(array(
				"ORDER_ARCHIVE_ID" => $result->getId(),
				"ORDER_DATA" => $orderData
			));
		}

		return $result;
	}

	/**
	 * Deletes row in entity table by primary key
	 *
	 * @param mixed $primary
	 *
	 * @return Main\Entity\DeleteResult
	 *
	 * @throws \Exception
	 */
	public static function delete($primary)
	{
		$result = parent::delete($primary);

		if ($result->isSuccess())
		{
			$checkOrderData = OrderArchivePackedTable::getById($primary);
			if ($checkOrderData->fetch())
			{
				OrderArchivePackedTable::delete($primary);
			}
		}

		return $result;
	}

	/**
	 * Renew table records from serialized data.
	 *
	 * @param array $filter.		Filter for selection updating entries.
	 *
	 * @return Main\Result
	 */
	public static function renew(array $filter = array())
	{
		$result = new Main\Result();
		$parameters = array("select" => array('ID'));

		if (!empty($filter))
			$parameters['filter'] = $filter;

		$idList = array();
		$archivedOrderData = self::getList($parameters);
		while ($archiveRow = $archivedOrderData->fetch())
		{
			$idList[] = $archiveRow['ID'];
		}

		if (empty($idList))
			return $result;

		$idListChunk = array_chunk($idList, 1000);
		foreach ($idListChunk as $chunk)
		{
			$packedData = OrderArchivePackedTable::getList(array(
				"filter" => array("ORDER_ARCHIVE_ID" => $chunk)
			));

			while ($packed = $packedData->fetch())
			{
				$orderData = unserialize($packed['ORDER_DATA'], ['allowed_classes' => false]);
				if (is_array($orderData['ORDER']))
				{
					$preparedOrderData = array_intersect_key($orderData['ORDER'], array_flip(Sale\Archive\Manager::getOrderFieldNames()));
					$result = self::update($packed['ORDER_ARCHIVE_ID'], $preparedOrderData);
					if (!$result->isSuccess())
						return $result;
				}
			}
		}

		return $result;
	}
}